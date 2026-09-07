<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends BackEndController
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

        $this->index_title = lang('Сategories');
        $this->add = lang('Add');
        $this->success_update_order = lang('You have successfully updated display order!');
        $this->success_add = lang('You have successfully added an object');
        $this->success_edit = lang('You have successfully updated the object');
        $this->success_delete = lang('You have successfully deleted the object');

        $this->data['title'] = $this->index_title;
        $this->data['add'] = $this->add;
        $this->load->model('categories_model');
        $this->load->model('shop_type_model');
    }

    public function index()
    {
        init_load_img($this->main_page);

        $objects = $this->categories_model->find();
        $this->data['categories'] = $this->categories_model->get_category_parents();

        $this->data['types'] = $this->shop_type_model->find();

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

            foreach (language(true) as $lang) {
                $post['uri' . strtoupper($lang)] = (!empty($post['title' . strtoupper($lang)])) ? transliteration($post['title' . strtoupper($lang)]) : '';
            }

            if (!empty($post['parent_id'])) {
                $parent_step = $this->db->select('step')->where('id', $post['parent_id'])->get('categories')->row()->step;
                $post['step'] = !empty($parent_step) ? $parent_step + 1 : 1;
            } else $post['step'] = 1;

            foreach (language(true) as $lang) {
                $lang = strtoupper($lang);
                $post['uri' . $lang] = (!empty($post['title' . $lang])) ? transliteration($post['title' . $lang]) : '';
                $category = $this->categories_model->get_category_by_uri($lang, $post['uri' . $lang]);
                if (!empty($category)) {
                    $post['uri' . $lang] = $post['uri' . $lang] . '_1';
                    $category = $this->categories_model->get_category_by_uri($lang, $post['uri' . $lang]);
                    if (!empty($category)) {
                        $post['uri' . $lang] = $post['uri' . $lang] . '_2';
                    }
                }
            }

            if (!empty($_FILES['imgNav']['name'])) {
                $this->upload->do_upload('imgNav');
                $file_data = $this->upload->data();
                $file = $file_data['file_name'];
                if (verify_img_extension($file_data['file_ext'])) $post['imgNav'] = $file;
            }
            if (!empty($_FILES['imgNavHover']['name'])) {
                $this->upload->do_upload('imgNavHover');
                $file_data = $this->upload->data();
                $file = $file_data['file_name'];
                if (verify_img_extension($file_data['file_ext'])) $post['imgNavHover'] = $file;
            }

            if (!$this->categories_model->put($post)) {
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

        $this->load->model('categories_model');

        try {
            $post = $this->input->post('so');

            if (empty($post) || !is_array($post)) {
                throw new Exception(lang('Error in received data!'));
            }

            if (!$this->categories_model->update_sorder($post)) {
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
        $item = $this->categories_model->find_first($id);
        if (empty($item)) throw_on_404();

        $banner = (object)array();
        $banner->titleRO = '';
        $banner->titleRU = '';
        $banner->descRO = '';
        $banner->descRU = '';
        $banner->uriRO = '';
        $banner->uriRU = '';
        $banner->uriimg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                foreach ($_POST as $index => $post_data) {
                    $post[$index] = $this->input->post($index);
                }

                $item->banner = $this->db->where('category_id', $id)->order_by("id ASC")->get('category_banner')->row();

                if (!empty($item->banner)) {
                    if (!empty($post['banner'])) {
                        $post['banner']['category_id'] = $id;

                        if (!empty($_FILES['banner']['name'])) {
                            unlink_files($this->main_page, $item->img);
                            $this->upload->do_upload('banner');
                            $file_data = $this->upload->data();
                            $file = $file_data['file_name'];
                            if (verify_img_extension($file_data['file_ext']))
                                $post['banner']['img'] = $file;
                        }

                        $this->db->where('id', $item->banner->id)->update('category_banner', $post['banner']);

                        unset($post['banner']);
                    }
                } else {
                    if (!empty($post['banner'])) {
                        $post['banner']['category_id'] = $id;

                        if (!empty($_FILES['banner']['name'])) {
                            unlink_files($this->main_page, $item->img);
                            $this->upload->do_upload('banner');
                            $file_data = $this->upload->data();
                            $file = $file_data['file_name'];
                            if (verify_img_extension($file_data['file_ext']))
                                $post['banner']['img'] = $file;
                        }

                        $this->db->insert('category_banner', $post['banner']);

                        unset($post['banner']);
                    }
                }


                $item->banner2 = $this->db->where('category_id', $id)->where('id !=', $item->banner->id)->order_by("id DESC")->get('category_banner')->row();
                if (!empty($item->banner2)) {
                    if (!empty($post['banner2'])) {
                        $post['banner2']['category_id'] = $id;

                        if (!empty($_FILES['banner2']['name'])) {
                            unlink_files($this->main_page, $item->img);
                            $this->upload->do_upload('banner2');
                            $file_data = $this->upload->data();
                            $file = $file_data['file_name'];
                            if (verify_img_extension($file_data['file_ext']))
                                $post['banner2']['img'] = $file;
                        }

                        $this->db->where('id', $item->banner2->id)->update('category_banner', $post['banner2']);
                        unset($post['banner2']);
                    }
                } else {
                    if (!empty($post['banner2'])) {
                        $post['banner2']['category_id'] = $id;

                        if (!empty($_FILES['banner2']['name'])) {
                            unlink_files($this->main_page, $item->img);
                            $this->upload->do_upload('banner2');
                            $file_data = $this->upload->data();
                            $file = $file_data['file_name'];
                            if (verify_img_extension($file_data['file_ext']))
                                $post['banner2']['img'] = $file;
                        }

                        $this->db->insert('category_banner', $post['banner2']);
                        unset($post['banner2']);
                    }
                }

                $post['updated_at'] = date("Y-m-d H:i:s");


                if (!empty($_FILES['img']['name'])) {
                    unlink_files($this->main_page, $item->img);
                    $this->upload->do_upload('img');
                    $file_data = $this->upload->data();
                    $file = $file_data['file_name'];
                    if (verify_img_extension($file_data['file_ext'])) $post['img'] = $file;
                }

                if (!empty($_FILES['imgNav']['name'])) {
                    unlink_files($this->main_page, $item->imgNav);
                    $this->upload->do_upload('imgNav');
                    $file_data = $this->upload->data();
                    $file = $file_data['file_name'];
                    if (verify_img_extension($file_data['file_ext'])) $post['imgNav'] = $file;
                }
                if (!empty($_FILES['imgNavHover']['name'])) {
                    unlink_files($this->main_page, $item->imgNavHover);
                    $this->upload->do_upload('imgNavHover');
                    $file_data = $this->upload->data();
                    $file = $file_data['file_name'];
                    if (verify_img_extension($file_data['file_ext'])) $post['imgNavHover'] = $file;
                }

                if (!$this->categories_model->update($post, $id)) {
                    throw new Exception(lang('Error writing data to table') . $this->main_page);
                }

                $_SESSION['success'] = $this->success_edit;
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $errors[] = lang('Exception thrown') . $e->getMessage();
                $_SESSION['error'] = $errors;
            }

            $item = $this->categories_model->find_first($id);
        }

        $banners = $this->db
            ->where('category_id', $id)
            ->order_by('id', 'ASC')
            ->get('category_banner')
            ->result();

        $item->banner  = $banners[0] ?? null;
        $item->banner2 = !empty($banners) ? $banners[count($banners) - 1] : null;


        $this->data['categories'] = $this->categories_model->get_category_parents();
        $this->data['types'] = $this->shop_type_model->find();

        $this->data['inner_view'] = $this->item_view;
        $this->data['title'] = lang('Editing') . ' ' . $item->titleRO;
        $this->data['parent_url'] = $this->path;
        $this->data['parent_title'] = $this->index_title;
        $this->data['item'] = $item;

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function delete($id = false)
    {
        $id = (int)$id;

        $item = $this->categories_model->find_first($id);
        if (empty($item)) throw_on_404();

        unlink_files($this->main_page, $item->img);

        try {
            if (!$this->categories_model->delete($id)) {
                throw new Exception(lang('Error deleting data from table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_delete;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }
}
