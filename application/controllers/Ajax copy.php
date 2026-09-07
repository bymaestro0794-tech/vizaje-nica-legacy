<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Console extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        @session_start();
        header('Content-type: text/html; charset=utf-8');
        //        check_if_POST();
        //        $this->load->library('session');

        if (empty($_SESSION['lang'])) get_prefered_language();
        $this->lclang = get_language(FALSE);
        $this->clang = get_language(TRUE);
        assign_language(uri(1));
    }
}
