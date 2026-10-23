<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Feedback extends BackEndController
{
    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->data['title'] = 'Обратная связь сайта';
        $this->load->model('site_feedback_model');
    }

    public function index()
    {
        $page = $this->input->get('page', true);
        $page = ctype_digit((string) $page) && (int) $page > 0 ? (int) $page : 1;

        $status = $this->input->get('status', true);
        $type = $this->input->get('type', true);
        $feedback = $this->site_feedback_model->get_feedback($page, 30, $status, $type);

        $this->data['feedback'] = $feedback['rows'];
        $this->data['pagination'] = $feedback['pagination'];
        $this->data['selected_status'] = in_array($status, array('new', 'in_progress', 'done', 'rejected'), true) ? $status : '';
        $this->data['selected_type'] = in_array($type, array('idea', 'bug', 'question'), true) ? $type : '';
        $this->data['inner_view'] = $this->folder . 'index';

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function view($id = 0)
    {
        $feedback = $this->site_feedback_model->get_feedback_item((int) $id);
        if (!$feedback) {
            show_404();
        }

        $this->data['feedback_item'] = $feedback;
        $this->data['inner_view'] = $this->folder . 'view';
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function image($id = 0)
    {
        $attachment = $this->site_feedback_model->get_attachment((int) $id);
        if (!$attachment) {
            show_404();
        }

        $uploadRoot = realpath(rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'site-feedback');
        $relativePath = ltrim(str_replace('\\', '/', (string) $attachment['file_path']), '/');
        $absolutePath = realpath(rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        if (!$uploadRoot || !$absolutePath || strpos($absolutePath, $uploadRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($absolutePath)) {
            show_404();
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($absolutePath) : '';
        $allowedMimeTypes = array('image/jpeg', 'image/png', 'image/webp', 'image/gif');
        if (!in_array($mime, $allowedMimeTypes, true)) {
            show_404();
        }

        $this->output
            ->set_content_type($mime)
            ->set_header('Content-Length: ' . (string) filesize($absolutePath))
            ->set_output(file_get_contents($absolutePath));
    }

    public function update_status($id = 0)
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('POST required', 405);
        }

        $status = $this->input->post('status', true);
        $this->site_feedback_model->update_status((int) $id, $status);

        redirect($this->feedbackListUrl());
    }

    private function feedbackListUrl()
    {
        $query = array();
        $status = $this->input->post('return_status', true);
        $type = $this->input->post('return_type', true);
        $page = $this->input->post('return_page', true);

        if (in_array($status, array('new', 'in_progress', 'done', 'rejected'), true)) {
            $query['status'] = $status;
        }
        if (in_array($type, array('idea', 'bug', 'question'), true)) {
            $query['type'] = $type;
        }
        if (ctype_digit((string) $page) && (int) $page > 1) {
            $query['page'] = (int) $page;
        }

        $url = '/' . ADM_CONTROLLER . '/feedback';
        return empty($query) ? $url : $url . '?' . http_build_query($query);
    }
}
