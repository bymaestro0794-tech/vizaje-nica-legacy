<?php
defined('BASEPATH') or exit('No direct script access allowed');
use JasonGrimes\Paginator;

class Account extends FrontEndController
{
    private $page_id;

    public function __construct()
    {
        parent::__construct();
        $this->page_id = 15;
        $this->per_page = 25;
        $this->load->model('products_model');
        $this->load->model('orders_model');
    }

    private function _init_seo_data($page)
    {
        $this->data['page_title'] = (!empty($page->seo_title)) ? $page->seo_title : "";
        $this->data['page_name'] = $page->title;
        $this->data['text_for_layout'] = $page->text;
        $this->data['keywords_for_layout'] = (!empty($page->seo_keywords)) ? $page->seo_keywords : "";
        $this->data['description_for_layout'] = (!empty($page->seo_desc)) ? $page->seo_desc : "";
        $this->data['otitle'] = $page->seo_title;
        $this->data['breadcrumbs'] = $this->breadcrumbs;
    }

    private function loadOGImgData($page, $dir = 'menu')
    {
        if (!empty($page->img)) {
            $this->data['og_img'] = newthumbs($page->img, $dir, 500, 300, 'og500x300x1', 1);
            $this->data['og_img_width'] = 500;
            $this->data['og_img_height'] = 300;
        } else {
            $this->data['og_img'] = '/public/i/no_image_214_51_og214x51x1.jpg';
            $this->data['og_img_width'] = 214;
            $this->data['og_img_height'] = 51;
        }
    }

    public function index()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        if (empty($this->data['client_info'])) {
            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][9]->uri);
        }

        $orders = $this->orders_model->get_client_orders($this->data['client_info']->id, $this->clang);
        $this->data['orders'] = $orders;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index);
            }

            if (!empty($post['password'])) {
                $user = $this->clients_model->login($this->data['client_info']->phone, sha1($post["password"]));

                if (!empty($user)) {

                    $data = array(
                        'password' => sha1($post['password_new']),
                    );

                    $this->clients_model->update($data, $this->data['client_info']->id);

                    $this->data['client_info'] = $this->clients_model->get_client($this->data['client_info']->id);

                    $_SESSION['user_id'] = $this->data['client_info']->id;
                    $_SESSION['user_login'] = $this->data['client_info']->email;
                    $_SESSION['usr_key'] = hash('sha512', $this->data['client_info']->email . $this->data['client_info']->password);
                    $_SESSION['isb2b'] = $user->isb2b == 1 ? true : false;
                }
            } else {
                if (empty($post['sms_subscribe'])) $post['sms_subscribe'] = 0;
                if (empty($post['mail_subscribe'])) $post['mail_subscribe'] = 0;
                $this->clients_model->update($post, $this->data['client_info']->id);
                $this->workappPost($post);
            }
        }


        $this->data['client_info'] = $this->clients_model->get_client($this->data['client_info']->id);

        $this->data['inner_view'] = 'pages/account/index';
        $this->data['class_page'] = '';
        $query = explode('?', $_SERVER['REQUEST_URI']);
        $this->data['lang_urls'] = array(
            'ru' => '' . $page->uriRU . '?' . $query[1],
            'ro' => '' . $page->uriRO . '?' . $query[1]
        );


        // favorite
        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;

        $product_all = $this->products_model->get_wishlist_products($this->data['wishlist']);
        $products = $this->products_model->get_products_wishlist($this->clang, $this->data['wishlist'], $start, $this->per_page);
        $count = count($product_all);

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $string = '';

        $urlPattern = $uri_parts[0] . '?favorite&' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2;
        $this->data['paginator'] = $paginator;

        $this->data['products'] = $products;
        $this->data['products_count'] = $count;

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->_render();
    }

    public function order()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 16);
        if (empty($page)) throw_on_404();

        if (empty($this->data['client_info'])) {
            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][9]->uri);
        }
        $this->data['client_info'] = $this->clients_model->get_client($this->data['client_info']->id);


        $orders = $this->orders_model->get_client_orders($this->data['client_info']->id, $this->clang);
        $this->data['orders'] = $orders;

        $this->data['inner_view'] = 'pages/account/order';
        $this->data['class_page'] = '';
        $this->data['lang_urls'] = array(
            'ru' => '' . $page->uriRU,
            'ro' => '' . $page->uriRO,
            'en' => '' . $page->uriEN,
        );

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->_render();
    }

    public function order_item()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 16);
        if (empty($page)) throw_on_404();

        if (empty($this->data['client_info'])) {
            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][9]->uri);
        }
        $this->data['client_info'] = $this->clients_model->get_client($this->data['client_info']->id);


        $order = $this->orders_model->get_client_order_by_id($this->data['client_info']->id, $this->uri3, $this->clang);
        $this->data['order'] = $order;


        $this->data['inner_view'] = 'pages/account/order_item';
        $this->data['class_page'] = '';
        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU,
        );

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->_render();
    }

    public function edit_contact_info()
    {
        check_if_POST();
        if (!empty($this->data['client_info'])) {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index, TRUE);
            }
            $post['id'] = $this->data['client_info']->id;
            if (!$this->clients_model->update($post, $post['id'])) {
                $return['info'] = ERROR_EDIT_CONTACT_INFO;
            } else {
                $return['info'] = SUCCESS_EDIT_CONTACT_INFO;
            }
        } else {
            $return['info'] = ERROR_EDIT_CONTACT_INFO;
        }
        echo json_encode($return);
    }

    public function edit_contact_pass()
    {
        check_if_POST();
        if (!empty($this->data['client_info'])) {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index, TRUE);
            }
            $post['id'] = $this->data['client_info']->id;

            if (!empty($post['password']) && !empty($post['password_new']) && !empty($post['password_ch'])) {
                $post["password"] = sha1($post["password"]);
                $user = $this->clients_model->login($this->data['client_info']->email, $post["password"]);

                if (!empty($user)) {
                    if ($post['password_new'] == $post['password_ch']) {
                        $post['password_new'] = sha1($post["password_new"]);
                        if (!$this->clients_model->update(array('password' => $post['password_new']), $post['id'])) {
                            $return['info'] = ERROR_EDIT_CONTACT_INFO;
                        } else {
                            $_SESSION['user_id'] = $user->id;
                            $_SESSION['user_login'] = $user->email;
                            $_SESSION['usr_key'] = hash('sha512', $user->email . $post['password_new']);
                            $_SESSION['isb2b'] = $user->isb2b == 1 ? true : false;
                            $return['info'] = SUCCESS_EDIT_CONTACT_INFO;
                        }
                    } else {
                        $return['info'] = ERROR_EDIT_CONTACT_INFO;
                    }
                } else {
                    $return['info'] = ERROR_EDIT_CONTACT_INFO;
                }
            } else {
                $return['info'] = ERROR_EDIT_CONTACT_INFO;
            }
        } else {
            $return['info'] = ERROR_EDIT_CONTACT_INFO;
        }
        echo json_encode($return);
    }

    public function order_history()
    {
        $page = $this->menu_model->get_page_data_by_id(24, $this->clang);
        if (empty($page)) throw_on_404();

        if (empty($this->data['client_info'])) {
            redirect('/' . $this->lclang . '/login');
        }

        $this->data['client_info'] = $this->clients_model->get_client($this->data['client_info']->id);

        $orders = $this->orders_model->get_client_orders($this->data['client_info']->id, $this->clang);
        $this->data['orders'] = $orders;

        //        Просмотренные товары
        $produse_views = get_cookie('produse_views');

        if (!empty($produse_views)) {
            $produse_views = explode(',', $produse_views);
            if (!empty($produse_views[0])) {
                $produse_views = array_reverse($produse_views);
                $this->data['products_views'] = $this->products_model->get_products_views($this->clang, $produse_views);
            }
        }
        //        Просмотренные товары END


        $this->data['inner_view'] = 'pages/account/order_history';
        $this->loadOGImgData($page);
        $this->_load_main_page($page);

        $this->_render();
    }

    public function login()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 12);
        if (empty($page)) throw_on_404();

        if (!empty($this->data['client_info'])) {
            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][12]->uri);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index);
            }
            if (!empty($post['email']) && !empty($post['password'])) {
                $post["password"] = sha1($post["password"]);
                $user = $this->clients_model->login($post['email'], $post["password"]);
                if (!empty($user)) {
                    if ($user->active == 1) {
                        $_SESSION['user_id'] = $user->id;
                        $_SESSION['user_login'] = $user->email;
                        $_SESSION['usr_key'] = hash('sha512', $user->email . $user->password);
                        $_SESSION['isb2b'] = $user->isb2b == 1 ? true : false;
                        if (!empty($_GET['checkout'])) {
                            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][4]->uri);
                        } else {
                            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][12]->uri);
                        }
                    } else {
                        $this->data['error'] = LOGIN_ERROR_ACTIVE;
                    }
                } else {
                    $this->data['post'] = $post;
                    $this->data['error'] = LOGIN_ERROR_DATE;
                }
            }
        }


        $this->data['inner_view'] = 'pages/account/login';
        $this->data['class_page'] = '';

        $this->data['page'] = $page;

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU,
        );

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->_render();
    }

    public function login_form()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $response = array();
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index);
            }
            if (!empty($post['number']) && !empty($post['password'])) {
                $post['number'] = str_replace(" ", '', $post['number']);
                $post['number'] = '+373' . substr($post['number'], -8);

                //                dump($post['number']);

                $post["password"] = sha1($post["password"]);
                $user = $this->clients_model->login($post['number'], $post["password"]);

                if (!empty($user)) {
                    if ($user->active == 1) {
                        $_SESSION['user_id'] = $user->id;
                        $_SESSION['user_login'] = $user->email;
                        $_SESSION['usr_key'] = hash('sha512', $user->email . $user->password);
                        $_SESSION['isb2b'] = $user->isb2b == 1 ? true : false;
                        $response['success'] = 1;

                        $this->workappGet($user);
                    } else {
                        $response['error'] = LOGIN_ERROR_ACTIVE;
                    }
                } else {
                    $response['post'] = $post;
                    $response['error'] = LOGIN_ERROR_DATE;
                }
            } elseif (!empty($post['number_off'])) {
                $post['number'] = str_replace(" ", '', $post['number']);
                $post['number'] = '+373' . substr($post['number'], -8);
                $user_number = $this->clients_model->check_number($post['number']);
                if (!empty($user_number)) {
                    $password = rand(100000, 999999);
                    $number = substr($post['number'], -8);
                    $this->db->where('id', $user_number->id)->update('clients', array('password' => $password));
                    $this->sendSMS($number, $password);
                    $response['code_auth'] = 1;
                    $response['number'] = $number;
                } else {
                    $response['error'] = LOGIN_ERROR_PHONE;
                }
            }
            echo json_encode($response);
        } else {
            throw_on_404();
        }
    }

    public function registration()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 10);
        if (empty($page)) throw_on_404();

        if (!empty($this->data['client_info'])) {
            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][12]->uri);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index, true);
            }

            function getCaptcha($SecretKey)
            {
                $Response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6Lda72IeAAAAAPvU_i2gWFvLzsRoOVQNXFnSRa9z&response={$SecretKey}");
                $Return = json_decode($Response);
                return $Return;
            }

            if (!empty($post['email']) && !empty($post['password'])) {
                $all_users = $this->clients_model->check_email($post['email']);
                if (!empty($all_users)) {
                    $this->data['error'] = REGISTRATION_ERROR_EMAIL;
                } elseif ($post['password'] != $post['password_check']) {
                    $this->data['error'] = REGISTRATION_ERROR_PASS;
                } else {
                    $post["password"] = sha1($post["password"]);
                    unset($post['password_check']);

                    if ($this->db->insert('clients', $post)) {
                        $this->email_registration($post);
                        $this->workappPost(['phone' => $post['number'], 'name' => $post['name'], 'surname' => $post['surname'], 'birthday' => $post['birthday']]);
                        $this->data['success'] = REGISTRATION_EMAIL_ACTIVE;
                        redirect(
                        	'/'
                        	. $this->lclang
                        	. '/?registration=success'
                        );
                    } else {
                        $this->data['error'] = REGISTRATION_ERROR;
                    }
                }
            } else {
                $this->data['error'] = REGISTRATION_ERROR;
            }
        }

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU,
        );
        $this->data['class_page'] = '';

        $this->data['inner_view'] = 'pages/account/registration';
        $this->data['page'] = $page;
        $this->loadOGImgData($page);
        $this->_init_seo_data($page);
        $this->_render();
    }

    public function registration_form()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index, true);
            }

            $response = array();

            function getCaptcha($SecretKey)
            {
                $Response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6Lda72IeAAAAAPvU_i2gWFvLzsRoOVQNXFnSRa9z&response={$SecretKey}");
                $Return = json_decode($Response);
                return $Return;
            }

            if (!empty($post['email']) && !empty($post['number'])) {
                $user_email = $this->clients_model->check_email($post['email']);
                if (empty($user_email)) {
                    $post['number'] = str_replace(" ", '', $post['number']);
                    $post['number'] = '+373' . substr($post['number'], -8);
                    $user_number = $this->clients_model->check_number(str_replace(" ", '', $post['number']));

                    if (empty($user_number)) {
                        if (empty($post['password'])) {
                            $post['password'] = rand(100000, 999999);
                            $post['password_check'] = rand(100000, 999999);
                        }

                        if ($post['password'] == $post['password_check']) {
                            $code = rand(100000, 999999);
                            $data = array(
                                'name' => '',
                                'active' => 1,
                                'phone' => str_replace(" ", '', $post['number']),
                                'email' => $post['email'],
                                'password' => sha1($post["password"]),
                                'code' => $code,
                            );
                            if ($this->db->insert('clients', $data)) {
                                $clientId = $this->db->insert_id();
                                $number = substr($post['number'], -8);

                                $smsSent = $this->sendSMS(
                                    $number,
                                    $code
                                );

                                if ($smsSent) {
                                    $response['code_auth'] = 1;
                                    $response['number'] = $number;
                                } else {
                                    $this->db
                                        ->where('id', $clientId)
                                        ->delete('clients');

                                    $response['error'] = $this->lclang === 'ro'
                                        ? 'Codul SMS nu a putut fi trimis. Încercați din nou mai târziu.'
                                        : 'Не удалось отправить SMS-код. Попробуйте позже.';
                                }
                            } else {
                                $response['error'] = REGISTRATION_ERROR;
                            }
                        } else {
                            $response['error'] = PASSWORD_CHECK_ERROR;
                        }
                    } else {
                        $response['error'] = REGISTRATION_ERROR_PHONE;
                    }
                } else {
                    $response['error'] = REGISTRATION_ERROR_EMAIL;
                }
            } else {
                $response['error'] = REGISTRATION_ERROR;
            }
            echo json_encode($response);
        } else {
            throw_on_404();
        }
    }

    public function login_form_code()
    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $response = array();
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index);
            }
            if (!empty($post['number']) && !empty($post['password'])) {
                $post['number'] = str_replace(" ", '', $post['number']);
                $post['number'] = '+373' . substr($post['number'], -8);

                $user = $this->clients_model->login_code($post['number'], $post["password"]);

                if (!empty($user)) {
                    $this->db->where('id', $user->id)->update('clients', array('code' => '1'));

                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['user_login'] = $user->email;
                    $_SESSION['usr_key'] = hash('sha512', $user->email . $user->password);
                    $_SESSION['isb2b'] = $user->isb2b == 1 ? true : false;
                    $response['success'] = 1;

                    $this->workappGet($user);
                } else {
                    $response['post'] = $post;
                    $response['error'] = LOGIN_ERROR_DATE;
                }
            }
            echo json_encode($response);
        } else {
            throw_on_404();
        }
    }

    public function reset_password()
    {
        if (!empty($this->data['client_info'])) {
            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][12]->uri);
        }

        $page = $this->menu_model->get_page_data_by_id($this->clang, 14);
        if (empty($page)) throw_on_404();

        if (empty($_GET['token'])) throw_on_404();

        $email = ilabCrypt($_GET['token'], false);
        $user = $this->clients_model->registration_email($email);

        if (empty($user)) throw_on_404();


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index, true);
            }
            if (!empty($post["password"]) && !empty($post["password_check"])) {
                if ($post["password"] == $post["password_check"]) {
                    $post["password"] = sha1($post["password"]);
                    $this->db->where('id', $user->id)->update('clients', array('password' => $post["password"]));
                    redirect('/' . $this->lclang . '#login');
                    die();
                } else {
                    $this->data['error'] = RECOVERY_ERROR;
                }
            } else {
                $this->data['error'] = RECOVERY_ERROR;
            }
        }


        $this->data['inner_view'] = 'pages/account/reset_password';
        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU,
        );
        $this->data['class_page'] = '';
        $this->data['page'] = $page;
        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->_render();
    }

    public function reset_form()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $response = array();
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index);
            }
            if (!empty($post['email'])) {

                $user = $this->clients_model->registration_email($post['email']);

                if (!empty($user)) {
                    if ($user->active == 1) {
                        $response['success'] = RESET_SUCCESS;
                        $this->email_reset($user);
                    } else {
                        $response['error'] = LOGIN_ERROR_ACTIVE;
                    }
                } else {
                    $response['post'] = $post;
                    $response['error'] = RESET_ERROR_EMAIL;
                }
            }
            echo json_encode($response);
        } else {
            throw_on_404();
        }
    }

    public function registration_active()
    {

        if (!empty($this->data['client_info'])) {
            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][12]->uri);
        }

        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            throw_on_404();
        } else {
            if (!empty($_GET['token'])) {
                $email = ilabCrypt($_GET['token'], false);
                $data = array(
                    'active' => 1,
                );
                $this->db->where('email', $email);
                $this->db->update('clients', $data);

                $page = $this->menu_model->get_page_data_by_id($this->clang, 9);
                if (empty($page)) throw_on_404();

                redirect(
                	'/'
                	. $this->lclang
                	. '/?registration=active'
                );
            } else {
                throw_on_404();
            }
        }
    }

    function email_registration($post, $password = '')
    {
        $this->load->library('parser');
        $link = 'https://' . $_SERVER['HTTP_HOST'] . '/' . $this->lclang . '/registration_active?token=' . ilabCrypt($post['email'], true);
        $parse = [
            'link' => $link,
            'password' => $password,
            'name' => $post['email'],
            'EMAIL_REGISTER_ACTIV' => EMAIL_REGISTER_ACTIV,
            'PASSWORD' => PASSWORD,
            'REGISTR_EMAIL_SUBJECT' => REGISTR_EMAIL_SUBJECT,
            'email' => $post['email']
        ];
        $tx = $this->parser->parse('layouts/email/registration', $parse, true);

        //            EMAIL TO SERVER
        $this->load->library('email');
        $config = config_smtp();
        $this->email->initialize($config);
        $this->email->from('noreply@' . $_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']);
        $this->email->to($post['email']);
        $this->email->subject(REGISTR_EMAIL_SUBJECT);
        $this->email->message($tx);
        $this->email->send();
    }


    function email_reset($post, $password = '')
    {
        $this->load->library('parser');
        $link = 'https://' . $_SERVER['HTTP_HOST'] . '/' . $this->lclang . '/password_reset?token=' . ilabCrypt($post->email, true);
        $parse = [
            'link' => $link,
            'RESET_INFO_EMAIL' => RESET_INFO_EMAIL,
            'RESET_EMAIL_SUBJECT' => RESET_EMAIL_SUBJECT,
        ];
        $tx = $this->parser->parse('layouts/email/reset', $parse, true);

        //            EMAIL TO SERVER
        $this->load->library('email');
        $config = config_smtp();
        $this->email->initialize($config);
        $this->email->from('noreply@' . $_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']);
        $this->email->to($post->email);
        $this->email->subject(RESET_EMAIL_SUBJECT);
        $this->email->message($tx);
        $this->email->send();
    }

    function workappPost($data)
    {

        if (!empty($data['phone'])) {
            $data['phone'] = str_replace("+", '', $data['phone']);
            $data['phone'] = str_replace("(", '', $data['phone']);
            $data['phone'] = str_replace(")", '', $data['phone']);
            if (empty($data['name'])) $data['name'] = 'Client';
            if (empty($data['surname'])) $data['surname'] = ' ';
            if (empty($data['birthday'])) $data['birthday'] = '19950101';
            else {
                $data['birthday'] = date("Ymd", strtotime($data['birthday']));
            }
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://api.vizaje-nica.com:90/Vizaje-Nica/hs/WORKAPP/PostCustomerInfoByPhone',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                "Phone": "' . $data['phone'] . '",
                "Name": "' . $data['name'] . '",
                "Surname": "' . $data['surname'] . '",
                "BirthDate": "' . $data['birthday'] . '"
            }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
        }
    }

    function workappGet($data)
    {

        if (!empty($data->phone)) {
            $data->phone = str_replace("+", '', $data->phone);
            $data->phone = str_replace("(", '', $data->phone);
            $data->phone = str_replace(")", '', $data->phone);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://api.vizaje-nica.com:90/Vizaje-Nica/hs/WORKAPP/GetCustomerByPhone/' . $data->phone . '',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response);

            if (empty($response->Discount)) {
                $data->phone = str_replace("+", '', $data->phone);
                $data->phone = str_replace("(", '', $data->phone);
                $data->phone = str_replace(")", '', $data->phone);
                $data->phone = str_replace("373", '0', $data->phone);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://api.vizaje-nica.com:90/Vizaje-Nica/hs/WORKAPP/GetCustomerByPhone/' . $data->phone . '',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                $response = json_decode($response);
            }

            if (!empty($response->Discount)) {
                $response->Code = str_replace(" ", '', $response->Code);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://api.vizaje-nica.com:90/Vizaje-Nica/hs/WORKAPP/GetBonusAccum/' . $response->Code,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));
                $response_Bonus = curl_exec($curl);
                curl_close($curl);
                $response_Bonus = json_decode($response_Bonus);
                
                $explode = explode(' ', $response->Name);
                $this->clients_model->update(array(
                    'Discount' => $response->Discount,
                    'Bonus' => $response_Bonus->Bonus,
                    'name' => $explode[0],
                    'surname' => $explode[1] ?? ''
                    ), $data->id);
            }
        }
    }

    public function sendSMS($number, $code)
{
	$text = 'Vizaje-Nica. Cod de autorizare: ' . $code;

	$query = http_build_query([
		'username' => 'vizaje',

		/*
		 * Сюда вставляется старое значение,
		 * которое раньше стояло в URL после password=
		 */
		'password' => 'z2ZLaKc8',

		'from' => 'Vizaje-Nica',
		'to' => '+373' . $number,
		'text' => $text,
		'coding' => 2,
		'charset' => 'utf-8',
	]);

	$url = 'https://messages.inter-mob.com/sms.asp?' . $query;

	$curl = curl_init();

    curl_setopt_array($curl, [
    	CURLOPT_URL => $url,
    	CURLOPT_RETURNTRANSFER => true,
    	CURLOPT_ENCODING => '',
    	CURLOPT_MAXREDIRS => 3,
    
    	CURLOPT_CONNECTTIMEOUT => 10,
    	CURLOPT_TIMEOUT => 20,
    
    	CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    
    	CURLOPT_FOLLOWLOCATION => true,
    	CURLOPT_SSL_VERIFYHOST => false,
    	CURLOPT_SSL_VERIFYPEER => false,
    	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    	CURLOPT_CUSTOMREQUEST => 'GET',
    ]);

	$response = curl_exec($curl);

	$curlError = curl_error($curl);
	$curlErrno = curl_errno($curl);

	$httpCode = (int) curl_getinfo(
		$curl,
		CURLINFO_HTTP_CODE
	);

	curl_close($curl);

	if (
		$response === false
		|| $curlErrno !== 0
		|| $httpCode < 200
		|| $httpCode >= 300
	) {
		log_message(
			'error',
			'SMS sending failed. Number: +373'
			. $number
			. '; HTTP: '
			. $httpCode
			. '; cURL errno: '
			. $curlErrno
			. '; cURL error: '
			. $curlError
		);

		return false;
	}

	log_message(
		'debug',
		'SMS API response for +373'
		. $number
		. ': '
		. substr((string) $response, 0, 500)
	);

	return true;
}

    public function logout()
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_login']);
        unset($_SESSION['usr_key']);
        unset($_SESSION['isb2b']);

        $referer = $this->input->server('HTTP_REFERER', TRUE);
        $currentHost = $this->input->server('HTTP_HOST', TRUE);
        $refererHost = !empty($referer) ? parse_url($referer, PHP_URL_HOST) : '';

        if (!empty($referer) && (empty($refererHost) || $refererHost === $currentHost)) {
            redirect($referer);
            die();
        }

        redirect('/' . $this->lclang . '/');
    }
}
