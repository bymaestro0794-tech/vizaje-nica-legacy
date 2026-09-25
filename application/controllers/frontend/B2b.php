<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class B2b extends CI_Controller
{
    public function index($language = 'ro')
    {
        $allowedLanguages = ['ro', 'ru'];

        if (!in_array($language, $allowedLanguages, true)) {
            $language = 'ro';
        }

        $this->load->helper('url');

        $queryString = trim((string) $this->input->server('QUERY_STRING'));
        $querySuffix = $queryString !== '' ? '?' . $queryString : '';

        $data = [
            'page_title' => 'B2B сотрудничество — Vizaje Nica',
            'page_description' => 'Профессиональное сотрудничество с Vizaje Nica.',
            'language' => $language,

            'current_url' => site_url($language . '/b2b') . $querySuffix,

            'language_urls' => [
                'ro' => site_url('ro/b2b') . $querySuffix,
                'ru' => site_url('ru/b2b') . $querySuffix,
            ],
        ];

        $this->load->view('pages/b2b/index', $data);
    }
}