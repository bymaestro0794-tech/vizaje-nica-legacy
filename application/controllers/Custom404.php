<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Custom404 extends CI_Controller
{
    public function index()
    {
        $this->output->set_status_header(404);

        $firstSegment = strtolower(
            (string) $this->uri->segment(1)
        );

        $language = $firstSegment === 'ro'
            ? 'ro'
            : 'ru';

        $data = [
            'lclang' => $language,
            'page_title' => $language === 'ro'
                ? 'Pagina nu a fost găsită'
                : 'Страница не найдена',
        ];

        $this->load->view(
            'errors/html/error_404_custom',
            $data
        );
    }
}