<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creator_program extends FrontEndController
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('clients_model');
        $this->load->model('creator_program_model');
        $this->client = !empty($_SESSION['user_id']) ? $this->clients_model->get_client((int) $_SESSION['user_id']) : null;
        if (!$this->client) {
            $_SESSION['creator_application_return_locale'] = $this->lclang === 'ro' ? 'ro' : 'ru';
            redirect('/' . $this->lclang . '/' . ($this->lclang === 'ro' ? 'intrare' : 'login'));
            exit;
        }
    }

    public function index()
    {
        if (!$this->creator_program_model->isAvailable()) {
            show_error('Creator program database patch is not installed.', 503);
            return;
        }
        if (!empty($this->client->is_creator)) {
            redirect('/' . $this->lclang . '/' . ($this->lclang === 'ro' ? 'contul-meu' : 'lichnyiy-kabinet') . '?creator');
            return;
        }
        $application = $this->creator_program_model->getForClient($this->client->id);
        if ($application && in_array($application['status'], array('pending', 'approved'), true)) {
            redirect('/' . $this->lclang . '/' . ($this->lclang === 'ro' ? 'contul-meu' : 'lichnyiy-kabinet'));
            return;
        }
        $this->data['page_title'] = $this->lclang === 'ro' ? 'Înscriere în programul creatorilor' : 'Заявка в программу креаторов';
        $this->data['page_name'] = $this->data['page_title'];
        $this->setApplicationLanguageUrls();
        $this->data['inner_view'] = 'pages/creator_program/application';
        $this->data['creator_client'] = $this->client;
        $this->data['form_error'] = '';
        $this->data['form_values'] = $this->profileFormDefaults();
        $this->data['creator_application_csrf'] = $this->csrfToken();
        $this->_render();
    }

    public function submit()
    {
        if (strtoupper((string) $this->input->method(true)) !== 'POST') show_error('POST required', 405);
        if (!$this->creator_program_model->isAvailable()) show_error('Creator program database patch is not installed.', 503);
        $expectedCsrf = (string) ($_SESSION['creator_application_csrf'] ?? '');
        $actualCsrf = $this->postString('creator_application_csrf', false);
        if ($expectedCsrf === '' || $actualCsrf === '' || !hash_equals($expectedCsrf, $actualCsrf)) {
            $this->renderForm(array(), $this->lclang === 'ro' ? 'Sesiunea formularului a expirat. Reîncărcați pagina.' : 'Форма устарела. Обновите страницу и попробуйте снова.');
            return;
        }
        if (!empty($this->client->is_creator)) {
            redirect('/' . $this->lclang . '/' . ($this->lclang === 'ro' ? 'contul-meu' : 'lichnyiy-kabinet') . '?creator');
            return;
        }

        $values = array(
            'date_of_birth' => $this->postString('date_of_birth'),
            'sex' => $this->postString('sex'),
            'audience_size' => $this->postString('audience_size'),
            'partnership_format' => $this->postString('partnership_format'),
            'instagram' => $this->postString('instagram'),
            'tiktok' => $this->postString('tiktok'),
            'other_social' => $this->postString('other_social'),
            'content_description' => $this->postString('content_description'),
            'partnership_reason' => $this->postString('partnership_reason'),
            'consent' => $this->postString('consent', false),
        );
        $today = date('Y-m-d');
        $dateParts = explode('-', $values['date_of_birth']);
        $validDate = count($dateParts) === 3 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $values['date_of_birth'])
            && checkdate((int) $dateParts[1], (int) $dateParts[2], (int) $dateParts[0])
            && $values['date_of_birth'] <= $today;
        $audiences = array('under_1000', '1000_5000', '5000_10000', '10000_50000', '50000_100000', 'over_100000');
        $formats = array('blogger_influencer', 'ugc_creator', 'both');
        $hasSocial = false;
        foreach (array('instagram', 'tiktok', 'other_social') as $social) {
            if ($values[$social] !== '') {
                $hasSocial = true;
                $socialParts = parse_url($values[$social]);
                if (mb_strlen($values[$social], 'UTF-8') > 500 || !filter_var($values[$social], FILTER_VALIDATE_URL)
                    || !is_array($socialParts) || !in_array(strtolower($socialParts['scheme'] ?? ''), array('http', 'https'), true)
                    || empty($socialParts['host']) || isset($socialParts['user']) || isset($socialParts['pass'])) {
                    $this->renderForm($values, $this->lclang === 'ro' ? 'Introduceți un link valid către profil.' : 'Укажите корректную ссылку на профиль.');
                    return;
                }
            }
        }
        if (!$validDate || !in_array($values['sex'], array('', '1', '2'), true)
            || !in_array($values['audience_size'], $audiences, true)
            || !in_array($values['partnership_format'], $formats, true) || !$hasSocial
            || $values['content_description'] === '' || mb_strlen($values['content_description'], 'UTF-8') > 1000
            || $values['partnership_reason'] === '' || mb_strlen($values['partnership_reason'], 'UTF-8') > 1000
            || $values['consent'] !== '1') {
            $this->renderForm($values, $this->lclang === 'ro' ? 'Verificați câmpurile obligatorii și acordul.' : 'Проверьте обязательные поля и согласие.');
            return;
        }

        $result = $this->creator_program_model->submitApplication($this->client, $values, $this->lclang === 'ro' ? 'ro' : 'ru');
        if ($result === 'created' || $result === 'already_exists') {
            redirect('/' . $this->lclang . '/' . ($this->lclang === 'ro' ? 'contul-meu' : 'lichnyiy-kabinet') . '?creator-application=submitted');
            return;
        }
        $this->renderForm($values, $this->lclang === 'ro' ? 'Cererea nu a putut fi salvată. Încercați din nou.' : 'Не удалось сохранить заявку. Попробуйте ещё раз.');
    }

    private function postString($key, $xssFilter = true)
    {
        $value = $this->input->post($key, $xssFilter);
        return is_string($value) || is_numeric($value) ? trim((string) $value) : '';
    }

    private function renderForm(array $values, $error)
    {
        $this->data['page_title'] = $this->lclang === 'ro' ? 'Înscriere în programul creatorilor' : 'Заявка в программу креаторов';
        $this->data['page_name'] = $this->data['page_title'];
        $this->setApplicationLanguageUrls();
        $this->data['inner_view'] = 'pages/creator_program/application';
        $this->data['creator_client'] = $this->client;
        $this->data['form_error'] = $error;
        $this->data['form_values'] = array_merge($this->profileFormDefaults(), $values);
        $this->data['creator_application_csrf'] = $this->csrfToken();
        $this->_render();
    }

    private function setApplicationLanguageUrls()
    {
        $this->data['lang_urls'] = array(
            'ru' => array('partners', 'apply'),
            'ro' => array('parteneri', 'apply'),
        );
    }

    private function profileFormDefaults()
    {
        $birthday = trim((string) ($this->client->birthday ?? ''));
        $dateOfBirth = '';
        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $birthday, $parts)
            && checkdate((int) $parts[2], (int) $parts[1], (int) $parts[3])) {
            $dateOfBirth = $parts[3] . '-' . $parts[2] . '-' . $parts[1];
        }
        if ($dateOfBirth === '' && preg_match('/^\d{4}-\d{2}-\d{2}(?:\s|$)/', $birthday)) {
            $dateOfBirth = substr($birthday, 0, 10);
        }
        foreach (array('Y-m-d', 'Ymd', 'd.m.Y', 'd/m/Y') as $format) {
            if ($dateOfBirth !== '') break;
            $date = DateTime::createFromFormat('!' . $format, $birthday);
            $errors = DateTime::getLastErrors();
            if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                $dateOfBirth = $date->format('Y-m-d');
            }
        }
        $sex = trim((string) ($this->client->sex ?? ''));
        $sex = preg_match('/^[12](?:\.0+)?$/', $sex) ? (string) (int) $sex : '';
        return array(
            'date_of_birth' => $dateOfBirth,
            'sex' => in_array($sex, array('1', '2'), true) ? $sex : '',
        );
    }

    private function csrfToken()
    {
        if (empty($_SESSION['creator_application_csrf'])) {
            $_SESSION['creator_application_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['creator_application_csrf'];
    }
}
