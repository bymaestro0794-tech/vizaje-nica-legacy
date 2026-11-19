<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Creator_program_mailer
{
    public function sendDecision(array $application, $event, $reason = '')
    {
        $locale = ($application['locale'] ?? 'ru') === 'ro' ? 'ro' : 'ru';
        $name = trim((string) ($application['full_name'] ?? ''));
        $firstName = htmlspecialchars(strtok($name, ' ') ?: $name, ENT_QUOTES, 'UTF-8');
        $safeReason = nl2br(htmlspecialchars(trim((string) $reason), ENT_QUOTES, 'UTF-8'));
        $isApproved = $event === 'approved';
        if (!$isApproved && $event !== 'rejected') return false;

        $messages = array(
            'ru' => array(
                'subject' => $isApproved ? 'Ваша заявка одобрена — Vizaje-Nica' : 'Информация по вашей заявке — Vizaje-Nica',
                'title' => $isApproved ? 'Заявка одобрена' : 'Заявка отклонена',
                'greeting' => 'Здравствуйте, ' . $firstName . '!',
                'copy' => $isApproved
                    ? 'Поздравляем! Ваша заявка одобрена. Кабинет креатора теперь доступен в вашем аккаунте Vizaje-Nica.'
                    : 'Спасибо за интерес к Vizaje-Nica. К сожалению, сейчас мы не можем одобрить вашу заявку.',
                'reason' => 'Комментарий команды:',
            ),
            'ro' => array(
                'subject' => $isApproved ? 'Candidatura ta a fost aprobată — Vizaje-Nica' : 'Actualizare privind candidatura ta — Vizaje-Nica',
                'title' => $isApproved ? 'Candidatura a fost aprobată' : 'Candidatura a fost respinsă',
                'greeting' => 'Salut, ' . $firstName . '!',
                'copy' => $isApproved
                    ? 'Felicitări! Candidatura ta a fost aprobată. Panoul creatorului este acum disponibil în contul tău Vizaje-Nica.'
                    : 'Îți mulțumim pentru interesul față de Vizaje-Nica. Din păcate, de această dată nu putem aproba candidatura.',
                'reason' => 'Mesaj din partea echipei:',
            ),
        );
        $primary = $messages[$locale];
        $secondaryLocale = $locale === 'ru' ? 'ro' : 'ru';
        $secondary = $messages[$secondaryLocale];
        $reasonHtml = (!$isApproved && trim((string) $reason) !== '')
            ? '<p style="margin:24px 0 0"><strong>' . $primary['reason'] . '</strong><br>' . $safeReason . '</p>'
                . '<p style="margin:8px 0 0"><strong>' . $secondary['reason'] . '</strong><br>' . $safeReason . '</p>'
            : '';
        $logoUrl = rtrim((string) config_item('base_url'), '/') . '/app/img/mini-logo.webp';
        $html = '<!doctype html><html lang="' . $locale . '"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>'
            . htmlspecialchars($primary['subject'], ENT_QUOTES, 'UTF-8')
            . '</title></head><body style="margin:0;padding:0;background-color:#ffffff;color:#111111;font-family:Arial,Helvetica,sans-serif">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="width:100%;background-color:#ffffff"><tr><td align="center" style="padding:28px 16px">'
            . '<table role="presentation" width="520" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:520px;border-collapse:collapse;background-color:#ffffff">'
            . '<tr><td style="padding:0 0 42px"><img src="' . htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') . '" width="36" height="27" alt="Vizaje-Nica" style="display:block;width:36px;height:27px;border:0;outline:none;text-decoration:none"></td></tr>'
            . '<tr><td style="padding:0 0 10px;font-family:Arial,Helvetica,sans-serif;color:#888888;font-size:11px;line-height:16px;letter-spacing:1px">'
            . ($locale === 'ru' ? 'РУССКИЙ' : 'ROMÂNĂ') . '</td></tr>'
            . '<tr><td style="padding:0 0 18px;font-family:Arial,Helvetica,sans-serif;color:#111111;font-size:24px;line-height:31px;font-weight:600">'
            . htmlspecialchars($primary['title'], ENT_QUOTES, 'UTF-8') . '</td></tr>'
            . '<tr><td style="padding:0 0 10px;font-family:Arial,Helvetica,sans-serif;color:#111111;font-size:15px;line-height:24px">'
            . $primary['greeting'] . '</td></tr>'
            . '<tr><td style="padding:0 0 30px;font-family:Arial,Helvetica,sans-serif;color:#555555;font-size:15px;line-height:24px">'
            . htmlspecialchars($primary['copy'], ENT_QUOTES, 'UTF-8') . $reasonHtml . '</td></tr>'
            . '<tr><td style="padding:0 0 24px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td height="1" bgcolor="#e8e8e8" style="height:1px;font-size:0;line-height:0;background-color:#e8e8e8">&nbsp;</td></tr></table></td></tr>'
            . '<tr><td style="padding:0 0 10px;font-family:Arial,Helvetica,sans-serif;color:#888888;font-size:11px;line-height:16px;letter-spacing:1px">'
            . ($locale === 'ru' ? 'ROMÂNĂ' : 'РУССКИЙ') . '</td></tr>'
            . '<tr><td style="padding:0 0 12px;font-family:Arial,Helvetica,sans-serif;color:#111111;font-size:19px;line-height:26px;font-weight:600">'
            . htmlspecialchars($secondary['title'], ENT_QUOTES, 'UTF-8') . '</td></tr>'
            . '<tr><td style="padding:0 0 38px;font-family:Arial,Helvetica,sans-serif;color:#555555;font-size:14px;line-height:23px">'
            . htmlspecialchars($secondary['copy'], ENT_QUOTES, 'UTF-8') . '</td></tr>'
            . '<tr><td style="padding:16px 0 0;border-top:1px solid #e8e8e8;font-family:Arial,Helvetica,sans-serif;color:#999999;font-size:12px;line-height:18px">Vizaje-Nica</td></tr>'
            . '</table></td></tr></table></body></html>';
        $plain = $primary['title'] . "\n\n" . strip_tags($primary['greeting']) . "\n\n" . $primary['copy']
            . ($reasonHtml !== '' ? "\n\n" . $primary['reason'] . "\n" . trim(strip_tags(str_replace('<br />', "\n", $safeReason))) : '')
            . "\n\n" . $secondary['title'] . "\n\n" . $secondary['copy'];

        $CI =& get_instance();
        $CI->load->library('email');
        $config = config_smtp();
        $config['mailtype'] = 'html';
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['crlf'] = "\r\n";
        $CI->email->initialize($config);
        $CI->email->from((string) ($config['smtp_user'] ?? 'noreply@vizaje-nica.com'), 'Vizaje-Nica');
        $CI->email->to((string) ($application['email'] ?? ''));
        $CI->email->subject($primary['subject']);
        $CI->email->message($html);
        $CI->email->set_alt_message($plain);
        $sent = $CI->email->send();
        $CI->email->clear(true);
        return (bool) $sent;
    }

}
