<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Small REST client for the legacy PHP 7.4 application.
 *
 * We intentionally use cURL instead of the current PHP SDK so the search
 * integration remains compatible with production PHP 7.4. The client is
 * backend-only; API keys are never sent to the browser.
 */
class Meilisearch_client
{
    private $baseUrl;
    private $searchKey;
    private $adminKey;
    private $timeout;
    private $lastError = '';

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config_item('meilisearch_url'), '/');
        $this->searchKey = (string) config_item('meilisearch_search_key');
        $this->adminKey = (string) config_item('meilisearch_admin_key');
        $this->timeout = max(1, (int) config_item('meilisearch_timeout'));
    }

    public function isConfigured()
    {
        return $this->baseUrl !== '';
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function health()
    {
        return $this->request('GET', '/health', null, false);
    }

    public function getIndex($index)
    {
        return $this->request(
            'GET',
            '/indexes/' . rawurlencode((string) $index),
            null,
            true
        );
    }

    public function createIndex($index, $primaryKey = 'id')
    {
        return $this->request(
            'POST',
            '/indexes',
            array(
                'uid' => (string) $index,
                'primaryKey' => (string) $primaryKey,
            ),
            true
        );
    }

    public function getTask($taskUid)
    {
        return $this->request(
            'GET',
            '/tasks/' . rawurlencode((string) $taskUid),
            null,
            true
        );
    }

    public function search($index, $query, array $options = array())
    {
        $payload = array_merge(array(
            'q' => (string) $query,
            'limit' => 20,
        ), $options);

        return $this->request(
            'POST',
            '/indexes/' . rawurlencode((string) $index) . '/search',
            $payload,
            false
        );
    }

    public function updateDocuments($index, array $documents, $primaryKey = 'id')
    {
        if (empty($documents)) {
            return false;
        }

        return $this->request(
            'POST',
            '/indexes/' . rawurlencode((string) $index) . '/documents?primaryKey=' . rawurlencode((string) $primaryKey),
            $documents,
            true
        );
    }

    /**
     * Queue removal of every document from one index.
     *
     * This is intentionally not used by the incremental admin endpoint. A
     * full reset should be an explicit future operation, so a normal import
     * cannot briefly leave the search index empty by accident.
     */
    public function deleteAllDocuments($index)
    {
        return $this->request(
            'DELETE',
            '/indexes/' . rawurlencode((string) $index) . '/documents',
            null,
            true
        );
    }

    public function updateSettings($index, array $settings)
    {
        return $this->request(
            'PATCH',
            '/indexes/' . rawurlencode((string) $index) . '/settings',
            $settings,
            true
        );
    }

    public function deleteDocument($index, $documentId)
    {
        return $this->request(
            'DELETE',
            '/indexes/' . rawurlencode((string) $index) . '/documents/' . rawurlencode((string) $documentId),
            null,
            true
        );
    }

    public function deleteDocuments($index, array $documentIds)
    {
        $documentIds = array_values(array_unique(array_filter(array_map('intval', $documentIds), static function ($id) {
            return $id > 0;
        })));
        if (empty($documentIds)) {
            return false;
        }

        return $this->request(
            'POST',
            '/indexes/' . rawurlencode((string) $index) . '/documents/delete-batch',
            array('ids' => $documentIds),
            true
        );
    }

    private function request($method, $path, $payload = null, $adminRequest = false)
    {
        $this->lastError = '';

        if ($this->baseUrl === '') {
            $this->lastError = 'Meilisearch URL is not configured.';
            return false;
        }

        $key = $adminRequest ? $this->adminKey : $this->searchKey;
        if ($adminRequest && $key === '') {
            $this->lastError = 'Meilisearch admin key is not configured.';
            return false;
        }

        $headers = array('Accept: application/json');
        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        if ($key !== '') {
            $headers[] = 'Authorization: Bearer ' . $key;
        }

        $curl = curl_init($this->baseUrl . $path);
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => false,
        ));

        if ($payload !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false || $curlError !== '') {
            $this->lastError = 'Meilisearch connection failed.';
            log_message('error', 'Meilisearch request failed: ' . $curlError);
            return false;
        }

        $decoded = json_decode($response, true);
        if ($status < 200 || $status >= 300 || !is_array($decoded)) {
            $safeMessage = is_array($decoded) && !empty($decoded['message'])
                ? trim((string) $decoded['message'])
                : '';
            $this->lastError = 'Meilisearch returned HTTP ' . $status . '.'
                . ($safeMessage !== '' ? ' ' . substr($safeMessage, 0, 300) : '');
            log_message('error', 'Meilisearch HTTP ' . $status . ' response: ' . substr((string) $response, 0, 500));
            return false;
        }

        return $decoded;
    }
}
