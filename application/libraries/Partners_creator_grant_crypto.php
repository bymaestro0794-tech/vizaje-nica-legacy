<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** AES-256-GCM envelope for opaque Partners grant tokens. */
class Partners_creator_grant_crypto
{
    public static function encrypt($plaintext, $keys)
    {
        if (!is_string($plaintext) || strlen($plaintext) < 32 || strlen($plaintext) > 256 || !is_array($keys)) {
            throw new InvalidArgumentException('Invalid creator grant.');
        }
        foreach ($keys as $keyId => $entry) {
            if (!empty($entry['active'])) {
                $nonce = random_bytes(12); $tag = '';
                $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $entry['key'], OPENSSL_RAW_DATA, $nonce, $tag, 'vizaje-partners-grant-v1', 16);
                if (!is_string($ciphertext) || strlen($tag) !== 16) throw new RuntimeException('Grant encryption unavailable.');
                return 'v1.' . $keyId . '.' . Partners_creator_protocol::encodeBase64Url($nonce)
                    . '.' . Partners_creator_protocol::encodeBase64Url($tag) . '.' . Partners_creator_protocol::encodeBase64Url($ciphertext);
            }
        }
        throw new RuntimeException('Grant encryption unavailable.');
    }

    public static function decrypt($envelope, $keys)
    {
        if (!is_string($envelope) || !is_array($keys)
            || !preg_match('/^v1\.([A-Za-z0-9._-]{1,80})\.([A-Za-z0-9_-]+)\.([A-Za-z0-9_-]+)\.([A-Za-z0-9_-]+)$/', $envelope, $m)
            || !isset($keys[$m[1]])) throw new RuntimeException('Stored creator grant unavailable.');
        $nonce = Partners_creator_protocol::decodeBase64Url($m[2]);
        $tag = Partners_creator_protocol::decodeBase64Url($m[3]);
        $ciphertext = Partners_creator_protocol::decodeBase64Url($m[4]);
        if (strlen($nonce) !== 12 || strlen($tag) !== 16 || strlen($ciphertext) > 256) throw new RuntimeException('Stored creator grant unavailable.');
        $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $keys[$m[1]]['key'], OPENSSL_RAW_DATA, $nonce, $tag, 'vizaje-partners-grant-v1');
        if (!is_string($plaintext)) throw new RuntimeException('Stored creator grant unavailable.');
        return $plaintext;
    }
}
