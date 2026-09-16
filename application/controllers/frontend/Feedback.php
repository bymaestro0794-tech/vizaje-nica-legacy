<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Feedback extends CI_Controller
{
    private $allowedTypes = array('idea', 'bug');
    private $maxImages = 3;
    private $maxImageBytes = 5242880;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('site_feedback_model');
    }

    public function submit()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            return $this->respond(array('error' => 'POST required'), 405);
        }

        if (isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > 16777216) {
            return $this->respond(array('error' => 'Payload too large'), 413);
        }

        $payload = $this->readPayload();

        // Invisible honeypot field for simple bots. Real users never see it.
        if (!empty($payload['website'])) {
            return $this->respond(array('accepted' => true), 201);
        }

        $type = isset($payload['feedback_type']) ? trim((string) $payload['feedback_type']) : '';
        $message = isset($payload['message']) ? trim((string) $payload['message']) : '';
        $pagePath = $this->nullablePath($payload, 'page_path');
        $locale = $this->nullableLocale($payload);

        if (!in_array($type, $this->allowedTypes, true)) {
            return $this->respond(array('error' => 'Invalid feedback type'), 422);
        }

        if ($message === '' || strlen($message) < 3 || strlen($message) > 5000) {
            return $this->respond(array('error' => 'Message must contain 3 to 5000 characters'), 422);
        }

        if ($pagePath === false || $locale === false) {
            return $this->respond(array('error' => 'Invalid page context'), 422);
        }

        $images = $this->prepareImages();
        if ($images === false) {
            return $this->respond(array('error' => $this->imageError), 422);
        }

        $saved = $this->site_feedback_model->insert_feedback(array(
            'feedback_type' => $type,
            'message' => $message,
            'page_path' => $pagePath,
            'locale' => $locale,
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s'),
        ));

        if (!$saved) {
            log_message('error', 'Site feedback insert failed: ' . json_encode($this->db->error()));
            return $this->respond(array('error' => 'Feedback was not saved'), 500);
        }

        $feedbackId = (int) $this->db->insert_id();
        foreach ($images as $image) {
            $upload = $this->storeImage($image, $feedbackId);
            if ($upload === false || !$this->site_feedback_model->insert_attachment($feedbackId, $upload)) {
                log_message('error', 'Site feedback image insert failed for feedback #' . $feedbackId);
                return $this->respond(array('error' => 'Image could not be saved'), 500);
            }
        }

        return $this->respond(array('accepted' => true), 201);
    }

    private $imageError = 'Invalid image upload';

    private function prepareImages()
    {
        if (!isset($_FILES['images'])) {
            return array();
        }

        $files = $_FILES['images'];
        $names = is_array($files['name']) ? $files['name'] : array($files['name']);
        $tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : array($files['tmp_name']);
        $errors = is_array($files['error']) ? $files['error'] : array($files['error']);
        $sizes = is_array($files['size']) ? $files['size'] : array($files['size']);

        if (count($names) > $this->maxImages) {
            $this->imageError = 'Можно загрузить не более 3 изображений';
            return false;
        }

        $allowedMimeTypes = array(
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        );
        $prepared = array();
        $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;

        foreach ($names as $index => $name) {
            if ($name === '' && (int) $errors[$index] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ((int) $errors[$index] !== UPLOAD_ERR_OK || (int) $sizes[$index] > $this->maxImageBytes) {
                $this->imageError = 'Каждое изображение должно быть не больше 5 МБ';
                if ($finfo) {
                    finfo_close($finfo);
                }
                return false;
            }

            $mime = $finfo ? finfo_file($finfo, $tmpNames[$index]) : '';
            if (!$mime && function_exists('getimagesize')) {
                $imageInfo = @getimagesize($tmpNames[$index]);
                $mime = is_array($imageInfo) && isset($imageInfo['mime']) ? $imageInfo['mime'] : '';
            }
            if (!isset($allowedMimeTypes[$mime]) || !@getimagesize($tmpNames[$index])) {
                $this->imageError = 'Поддерживаются только JPG, PNG, WEBP и GIF';
                if ($finfo) {
                    finfo_close($finfo);
                }
                return false;
            }

            $prepared[] = array(
                'tmp_name' => $tmpNames[$index],
                'extension' => $allowedMimeTypes[$mime],
                'original_name' => $this->cleanFileName($name),
            );
        }

        if ($finfo) {
            finfo_close($finfo);
        }
        return $prepared;
    }

    private function storeImage(array $image, $feedbackId)
    {
        $directory = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'site-feedback';
        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            return false;
        }

        try {
            $randomName = bin2hex(random_bytes(16));
        } catch (Exception $exception) {
            $randomName = sha1(uniqid((string) $feedbackId, true));
        }
        $fileName = $randomName . '.' . $image['extension'];
        $absolutePath = $directory . DIRECTORY_SEPARATOR . $fileName;
        if (!move_uploaded_file($image['tmp_name'], $absolutePath)) {
            return false;
        }

        return array(
            'file_path' => 'uploads/site-feedback/' . $fileName,
            'original_name' => $image['original_name'],
        );
    }

    private function cleanFileName($name)
    {
        $name = basename((string) $name);
        $name = preg_replace('/[\\x00-\\x1F\\x7F]+/', '', $name);
        return substr($name ?: 'image', 0, 255);
    }

    private function readPayload()
    {
        $raw = trim((string) $this->input->raw_input_stream);
        if ($raw !== '') {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                return $json;
            }
        }

        $post = $this->input->post(NULL, true);
        return is_array($post) ? $post : array();
    }

    private function nullablePath(array $payload, $field)
    {
        if (!isset($payload[$field]) || $payload[$field] === '') {
            return null;
        }

        $path = trim((string) $payload[$field]);
        $path = (string) parse_url($path, PHP_URL_PATH);

        if ($path === '' || strlen($path) > 255 || strpos($path, '/') !== 0) {
            return false;
        }

        return $path;
    }

    private function nullableLocale(array $payload)
    {
        if (!isset($payload['locale']) || $payload['locale'] === '') {
            return null;
        }

        $locale = strtolower(trim((string) $payload['locale']));
        return preg_match('/^[a-z]{2}(?:-[a-z]{2})?$/', $locale) ? $locale : false;
    }

    private function respond(array $body, $status)
    {
        return $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'UTF-8')
            ->set_output(json_encode($body));
    }
}
