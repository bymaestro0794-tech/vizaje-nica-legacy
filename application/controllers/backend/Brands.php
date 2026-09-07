<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Brands extends BackEndController
{
    protected $add;
    protected $index_title;
    protected $title;
    protected $success_add;
    protected $success_update_order;
    protected $success_edit;
    protected $success_delete;

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->index_title = lang('Brands');
        $this->add = lang('Add');
        $this->success_update_order = lang('You have successfully updated display order!');
        $this->success_add = lang('You have successfully added an object');
        $this->success_edit = lang('You have successfully updated the object');
        $this->success_delete = lang('You have successfully deleted the object');

        $this->data['title'] = $this->index_title;
        $this->data['add'] = $this->add;
        $this->load->model('brands_model');
        $this->load->model('brand_certificates_model');
    }

    public function index()
    {
        init_load_img($this->main_page);

        $objects = $this->brands_model->find();

        $this->data['inner_view'] = $this->index_view;
        $this->data['objects'] = $objects;

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function put()
    {
        check_if_POST();

        $post = array();

        init_load_img($this->main_page);

        try {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index);
            }

            $post['uri'] = (!empty($post['title'])) ? transliteration($post['title']) : '';

            if (!empty($_FILES['img']['name'])) {
                $this->upload->do_upload('img');
                $file_data = $this->upload->data();
                $file = $file_data['file_name'];
                if (verify_img_extension($file_data['file_ext'])) $post['img'] = $file;
            }

            if (!$this->brands_model->put($post)) {
                throw new Exception(lang('Error writing data to table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_add;
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function update_order()
    {
        check_if_POST();

        $this->load->model('brands_model');

        try {
            $post = $this->input->post('so');

            if (empty($post) || !is_array($post)) {
                throw new Exception(lang('Error in received data!'));
            }

            if (!$this->brands_model->update_sorder($post)) {
                throw new Exception(lang('Error writing data to table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_update_order;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function item($id = 0)
    {
        $id = (int)$id;

        init_load_img($this->main_page);
        $item = $this->brands_model->find_first($id);
        if (empty($item)) throw_on_404();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $post = [];

                foreach ($_POST as $index => $postData) {
                    $post[$index] = $this->input->post($index);
                }

                $formType = !empty($post['form_type'])
                    ? $post['form_type']
                    : null;

                unset($post['form_type']);

                $post['updated_at'] = date('Y-m-d H:i:s');

                if (
                    $formType === 'general' &&
                    array_key_exists('title', $post)
                ) {
                    $post['uri'] = !empty($post['title'])
                        ? transliteration($post['title'])
                        : $item->uri;
                }

                if (
                    $formType === 'general' &&
                    !empty($_FILES['img']['name'])
                ) {
                    if (!$this->upload->do_upload('img')) {
                        throw new Exception(
                            strip_tags($this->upload->display_errors())
                        );
                    }

                    $fileData = $this->upload->data();

                    if (!verify_img_extension($fileData['file_ext'])) {
                        throw new Exception(
                            'Недопустимый формат изображения бренда'
                        );
                    }

                    if (!empty($item->img)) {
                        unlink_files($this->main_page, $item->img);
                    }

                    $post['img'] = $fileData['file_name'];
                }

                if (!$this->brands_model->update($post, $id)) {
                    throw new Exception(
                        lang('Error writing data to table') . $this->main_page
                    );
                }

                $_SESSION['success'] = $this->success_edit;
            } catch (Exception $e) {
                log_message('error', $e->getMessage());

                $_SESSION['error'] = [
                    lang('Exception thrown') . $e->getMessage()
                ];
            }

            redirect($this->path . 'item/' . $id . '/');
            return;
        }

        $this->data['inner_view'] = $this->item_view;
        $this->data['title'] = 'Редактирование ' . $item->title;
        $this->data['parent_url'] = $this->path;
        $this->data['parent_title'] = $this->index_title;
        $this->data['item'] = $item;

        $this->data['certificates'] =
            $this->brand_certificates_model->get_by_brand($id);

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function delete($id = false)
    {
        $id = (int)$id;

        $item = $this->brands_model->find_first($id);
        if (empty($item)) throw_on_404();

        unlink_files($this->main_page, $item->img);

        try {
            if (!$this->brands_model->delete($id)) {
                throw new Exception(lang('Error deleting data from table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_delete;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    // CERTIFICATE

public function certificate_put($brandId = 0)
{
    check_if_POST();

    $brandId = (int) $brandId;

    $brand = $this->brands_model->find_first($brandId);

    if (empty($brand)) {
        throw_on_404();
    }

    try {
        if (empty($_FILES['image']['name'])) {
            throw new Exception('Выберите изображение сертификата');
        }

        $uploadPath = FCPATH . 'public/brand_certificates/';

        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0775, true)) {
                throw new Exception(
                    'Не удалось создать папку для сертификатов'
                );
            }
        }

        $config = [
            'upload_path' => $uploadPath,
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size' => 15360,
            'encrypt_name' => true,
            'remove_spaces' => true,
        ];

        $this->load->library('upload');
        $this->upload->initialize($config, true);

        if (!$this->upload->do_upload('image')) {
            throw new Exception(
                strip_tags(
                    $this->upload->display_errors('', '')
                )
            );
        }

        $fileData = $this->upload->data();

        if (
            empty($fileData['file_name']) ||
            !verify_img_extension($fileData['file_ext'])
        ) {
            if (
                !empty($fileData['full_path']) &&
                file_exists($fileData['full_path'])
            ) {
                unlink($fileData['full_path']);
            }

            throw new Exception(
                'Недопустимый формат сертификата'
            );
        }

        $data = [
            'brand_id' => $brandId,

            'titleRU' => trim(
                (string) $this->input->post('titleRU', true)
            ),

            'titleRO' => trim(
                (string) $this->input->post('titleRO', true)
            ),

            'image' => $fileData['file_name'],

            'sorder' => (int) $this->input->post('sorder'),

            'isShown' => $this->input->post('isShown')
                ? 1
                : 0,

            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!$this->brand_certificates_model->put($data)) {
            if (file_exists($fileData['full_path'])) {
                unlink($fileData['full_path']);
            }

            throw new Exception(
                'Не удалось сохранить сертификат'
            );
        }

        $_SESSION['success'] =
            'Сертификат успешно добавлен';
    } catch (Exception $e) {
        log_message('error', $e->getMessage());

        $_SESSION['error'] = [
            $e->getMessage()
        ];
    }

    redirect(
        $this->path
        . 'item/'
        . $brandId
        . '/#tab_1_3'
    );
}
public function certificates_update_order($brandId = 0)
{
    check_if_POST();

    $brandId = (int) $brandId;

    $brand = $this->brands_model->find_first($brandId);

    if (empty($brand)) {
        throw_on_404();
    }

    try {
        $orders = $this->input->post('so');

        if (empty($orders) || !is_array($orders)) {
            throw new Exception('Не получены данные для обновления порядка');
        }

        foreach ($orders as $certificateId => $sorder) {
            $certificateId = (int) $certificateId;

            $certificate =
                $this->brand_certificates_model
                    ->find_by_brand_and_id(
                        $brandId,
                        $certificateId
                    );

            if (empty($certificate)) {
                continue;
            }

            $this->brand_certificates_model
                ->update_certificate(
                    $certificateId,
                    $brandId,
                    [
                        'sorder' => max(0, (int) $sorder),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]
                );
        }

        $_SESSION['success'] = 'Порядок сертификатов обновлён';
    } catch (Exception $e) {
        log_message('error', $e->getMessage());

        $_SESSION['error'] = [
            $e->getMessage()
        ];
    }

    redirect(
        $this->path . 'item/' . $brandId . '/#tab_1_3'
    );
}
public function certificate_delete(
    $brandId = 0,
    $certificateId = 0
) {
    $brandId = (int) $brandId;
    $certificateId = (int) $certificateId;

    $brand = $this->brands_model->find_first($brandId);

    if (empty($brand)) {
        throw_on_404();
    }

    $certificate =
        $this->brand_certificates_model
            ->find_by_brand_and_id(
                $brandId,
                $certificateId
            );

    if (empty($certificate)) {
        throw_on_404();
    }

    try {
        if (!empty($certificate->image)) {
            unlink_files(
                'brand_certificates',
                $certificate->image
            );
        }

        $deleted = $this->brand_certificates_model
        ->delete_certificate(
            $certificateId,
            $brandId
        );

        if (!$deleted) {
            throw new Exception(
                'Не удалось удалить сертификат'
            );
        }

        $_SESSION['success'] = 'Сертификат удалён';
    } catch (Exception $e) {
        log_message('error', $e->getMessage());

        $_SESSION['error'] = [
            $e->getMessage()
        ];
    }

    redirect(
        $this->path . 'item/' . $brandId . '/#tab_1_3'
    );
}
public function certificate_update($brandId = 0, $certificateId = 0)
{
    $brandId = (int) $brandId;
    $certificateId = (int) $certificateId;

    $brand = $this->brands_model->find_first($brandId);

    if (empty($brand)) {
        throw_on_404();
    }

    $certificate = $this->brand_certificates_model
        ->find_by_brand_and_id($brandId, $certificateId);

    if (empty($certificate)) {
        throw_on_404();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $data = [
                'titleRU' => trim(
                    (string) $this->input->post('titleRU', true)
                ),
                'titleRO' => trim(
                    (string) $this->input->post('titleRO', true)
                ),
                'sorder' => (int) $this->input->post('sorder'),
                'isShown' => $this->input->post('isShown') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($_FILES['image']['name'])) {
                $uploadPath = FCPATH . 'public/brand_certificates/';

                if (!is_dir($uploadPath)) {
                    if (!mkdir($uploadPath, 0775, true)) {
                        throw new Exception(
                            'Не удалось создать папку сертификатов'
                        );
                    }
                }

                $config = [
                    'upload_path' => $uploadPath,
                    'allowed_types' => 'jpg|jpeg|png|webp',
                    'max_size' => 15360,
                    'encrypt_name' => true,
                    'remove_spaces' => true,
                ];

                $this->load->library('upload');
                $this->upload->initialize($config, true);

                if (!$this->upload->do_upload('image')) {
                    throw new Exception(
                        strip_tags(
                            $this->upload->display_errors('', '')
                        )
                    );
                }

                $fileData = $this->upload->data();

                if (
                    empty($fileData['file_name']) ||
                    !verify_img_extension($fileData['file_ext'])
                ) {
                    if (
                        !empty($fileData['full_path']) &&
                        file_exists($fileData['full_path'])
                    ) {
                        unlink($fileData['full_path']);
                    }

                    throw new Exception(
                        'Недопустимый формат изображения'
                    );
                }

                /*
                 * Удаляем старый оригинал после успешной загрузки нового.
                 */
                if (!empty($certificate->image)) {
                    $oldFile = FCPATH
                        . 'public/brand_certificates/'
                        . $certificate->image;

                    if (is_file($oldFile)) {
                        unlink($oldFile);
                    }

                    /*
                     * Удаляем старые миниатюры этого файла.
                     */
                    $thumbsPath = FCPATH
                        . 'public/brand_certificates/thumbs/';

                    $this->deleteCertificateThumbs(
                        $thumbsPath,
                        $certificate->image
                    );
                }

                $data['image'] = $fileData['file_name'];
            }

            $updated = $this->brand_certificates_model
                ->update_certificate(
                    $certificateId,
                    $brandId,
                    $data
                );

            if (!$updated) {
                throw new Exception(
                    'Не удалось обновить сертификат'
                );
            }

            $_SESSION['success'] =
                'Сертификат успешно обновлён';

            redirect(
                $this->path
                . 'item/'
                . $brandId
                . '/#tab_1_3'
            );

            return;
        } catch (Exception $e) {
            log_message('error', $e->getMessage());

            $_SESSION['error'] = [
                $e->getMessage()
            ];
        }

        redirect(
            $this->path
            . 'certificate_update/'
            . $brandId
            . '/'
            . $certificateId
            . '/'
        );

        return;
    }

    $this->data['inner_view'] =
        'dashboard/brands/certificate_update';

    $this->data['title'] =
        'Редактирование сертификата';

    $this->data['parent_url'] =
        $this->path . 'item/' . $brandId . '/#tab_1_3';

    $this->data['parent_title'] =
        'Сертификаты ' . $brand->title;

    $this->data['brand'] = $brand;
    $this->data['certificate'] = $certificate;

    $this->load->vars($this->data);
    $this->load->view($this->main_layout);
}
private function deleteCertificateThumbs(
    $directory,
    $filename
) {
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $directory,
            RecursiveDirectoryIterator::SKIP_DOTS
        )
    );

    foreach ($iterator as $file) {
        if (
            $file->isFile() &&
            $file->getFilename() === $filename
        ) {
            @unlink($file->getPathname());
        }
    }
}
}