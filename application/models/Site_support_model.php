<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site_support_model extends CI_Model
{
    private $table = 'site_support_settings';
    private $defaultContact = '@vizaje_nica_support_bot';

    public function isInstalled()
    {
        return $this->db->table_exists($this->table);
    }

    public function getSettings()
    {
        $defaults = $this->buildSettings($this->defaultContact, true, false);

        if (!$this->isInstalled()) {
            return $defaults;
        }

        $row = $this->db
            ->where('id', 1)
            ->limit(1)
            ->get($this->table)
            ->row();

        if (empty($row)) {
            return $defaults;
        }

        $contact = trim((string) ($row->telegram_contact ?? ''));
        if ($contact === '') {
            $contact = $this->defaultContact;
        }

        return $this->buildSettings($contact, !empty($row->is_enabled), true);
    }

    public function saveSettings($enabled, $contact)
    {
        if (!$this->isInstalled()) {
            return array(
                'success' => false,
                'error' => 'Сначала примените SQL-патч database/patches/2026-10-22-create-site-support-settings.sql.',
            );
        }

        $contact = trim((string) $contact);
        if ($contact === '') {
            $current = $this->getSettings();
            $contact = (string) ($current['contact'] ?? $this->defaultContact);
        }

        $normalized = $this->normalizeContact($contact);
        if ($normalized === false) {
            return array(
                'success' => false,
                'error' => 'Укажите username Telegram (например, @vizaje_nica_support_bot) или ссылку https://t.me/....',
            );
        }

        $data = array(
            'is_enabled' => !empty($enabled) ? 1 : 0,
            'telegram_contact' => $contact,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        );

        $exists = $this->db
            ->select('id')
            ->where('id', 1)
            ->limit(1)
            ->get($this->table)
            ->row();

        $saved = !empty($exists)
            ? $this->db->where('id', 1)->update($this->table, $data)
            : $this->db->insert($this->table, array_merge(array('id' => 1), $data));

        return array(
            'success' => (bool) $saved,
            'error' => $saved ? '' : 'Не удалось сохранить настройку поддержки.',
        );
    }

    private function buildSettings($contact, $enabled, $installed)
    {
        $normalized = $this->normalizeContact($contact);

        if ($normalized === false) {
            $normalized = array('url' => '', 'label' => trim((string) $contact));
        }

        return array(
            'enabled' => (bool) $enabled,
            'contact' => trim((string) $contact),
            'url' => (string) $normalized['url'],
            'label' => (string) $normalized['label'],
            'installed' => (bool) $installed,
        );
    }

    private function normalizeContact($contact)
    {
        $contact = trim((string) $contact);
        if ($contact === '') {
            return false;
        }

        if (preg_match('~^(?:https?://)?(?:www\.)?(?:t\.me|telegram\.me)/([^/?#]+)~i', $contact, $matches)) {
            $token = trim((string) $matches[1]);
            if ($token === '') {
                return false;
            }

            return array(
                'url' => 'https://t.me/' . rawurlencode($token),
                'label' => strpos($token, '+') === 0 ? $token : '@' . ltrim($token, '@'),
            );
        }

        if (preg_match('/^@[A-Za-z0-9_]{3,64}$/', $contact)) {
            return array(
                'url' => 'https://t.me/' . rawurlencode(substr($contact, 1)),
                'label' => $contact,
            );
        }

        if (preg_match('/^[A-Za-z0-9_]{3,64}$/', $contact)) {
            return array(
                'url' => 'https://t.me/' . rawurlencode($contact),
                'label' => '@' . $contact,
            );
        }

        if (preg_match('/^\+?[0-9][0-9 ()-]{5,30}$/', $contact)) {
            $phone = preg_replace('/[^0-9]/', '', $contact);

            return array(
                'url' => 'https://t.me/+' . rawurlencode($phone),
                'label' => '+' . $phone,
            );
        }

        if (filter_var($contact, FILTER_VALIDATE_URL)) {
            return array('url' => $contact, 'label' => $contact);
        }

        return false;
    }
}
