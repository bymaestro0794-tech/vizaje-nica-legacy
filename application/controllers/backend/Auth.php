<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends BackEndController
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function login()
    {
        $this->load->model('admins_model');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $login = $this->input->post('login', TRUE);
            $password = $this->input->post('password', TRUE);

            if (!empty($login) && !empty($password)) {
                if ($this->admins_model->try_login($login, $password)) {
                    redirect("/cp/menu");
                    log_message(
                        'error',
                        'ADMIN LOGIN: sid=' . session_id()
                        . ' admin_id=' . ($_SESSION['admin_id'] ?? 'none')
                        . ' login=' . ($_SESSION['login'] ?? 'none')
                        . ' key=' . (!empty($_SESSION['admin_key']) ? 'yes' : 'no')
                        . ' save_path=' . session_save_path()
                    );

                    session_write_close();

                    redirect('/cp/menu');
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Неправильное сочитание логина/пароля';
                    redirect("/cp/login");
                }
            }
        }

        $this->load->view('dashboard/auth/login');
    }

    public function logout()
    {
        unset($_SESSION['admin_id']);
        unset($_SESSION['login']);
        unset($_SESSION['key']);



        redirect('/cp/login');
    }
}
