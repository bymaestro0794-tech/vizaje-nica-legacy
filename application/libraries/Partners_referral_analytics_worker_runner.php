<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Safe orchestration and status reporting for the CLI analytics worker. */
class Partners_referral_analytics_worker_runner
{
    const OUTBOX_TABLE = 'partners_referral_analytics_outbox';

    public function run($enabled, $connectDatabase, $loadModel)
    {
        if (!$enabled) {
            return array(
                'exit_code' => 0,
                'status' => 'disabled',
                'code' => null,
                'reconciliation' => array('scanned' => 0, 'reconciled' => 0),
                'delivery' => array('delivered' => 0, 'retry' => 0, 'failed' => 0, 'skipped' => 0),
            );
        }

        try {
            $database = call_user_func($connectDatabase);
        } catch (Throwable $exception) {
            return $this->failure('DATABASE_UNAVAILABLE');
        }
        if (!is_object($database) || empty($database->conn_id)) {
            return $this->failure('DATABASE_UNAVAILABLE');
        }

        $previousDbDebug = isset($database->db_debug) ? $database->db_debug : null;
        if (isset($database->db_debug)) {
            $database->db_debug = false;
        }

        try {
            try {
                $tableExists = $database->table_exists(self::OUTBOX_TABLE);
            } catch (Throwable $exception) {
                return $this->failure('DATABASE_QUERY_FAILED');
            }
            if (!$tableExists) {
                $databaseError = method_exists($database, 'error') ? $database->error() : array();
                if (isset($databaseError['code']) && (int) $databaseError['code'] !== 0) {
                    return $this->failure('DATABASE_QUERY_FAILED');
                }
                return $this->failure('OUTBOX_TABLE_MISSING');
            }

            try {
                $model = call_user_func($loadModel);
            } catch (Throwable $exception) {
                return $this->failure('WORKER_MODEL_UNAVAILABLE');
            }
            if (!is_object($model)
                || !method_exists($model, 'reconcileOrderEvents')
                || !method_exists($model, 'deliverPending')) {
                return $this->failure('WORKER_MODEL_UNAVAILABLE');
            }

            try {
                $reconciliation = $model->reconcileOrderEvents(100);
                if ($this->hasError($reconciliation)) {
                    return $this->failure($reconciliation['error']);
                }

                $delivery = $model->deliverPending(50);
                if ($this->hasError($delivery)) {
                    return $this->failure($delivery['error']);
                }
            } catch (Throwable $exception) {
                return $this->failure('DATABASE_OPERATION_FAILED');
            }

            return array(
                'exit_code' => 0,
                'status' => 'ok',
                'code' => null,
                'reconciliation' => $reconciliation,
                'delivery' => $delivery,
            );
        } finally {
            if (isset($database->db_debug) && $previousDbDebug !== null) {
                $database->db_debug = $previousDbDebug;
            }
        }
    }

    public function failure($code)
    {
        if (!preg_match('/^[A-Z0-9_]{1,64}$/', (string) $code)) {
            $code = 'WORKER_OPERATION_FAILED';
        }

        return array(
            'exit_code' => 1,
            'status' => 'error',
            'code' => (string) $code,
            'reconciliation' => array('scanned' => 0, 'reconciled' => 0),
            'delivery' => array('delivered' => 0, 'retry' => 0, 'failed' => 0, 'skipped' => 0),
        );
    }

    public function formatOutput(array $result)
    {
        if (isset($result['status']) && $result['status'] === 'disabled') {
            return 'Partners analytics worker: disabled' . PHP_EOL;
        }

        if (!empty($result['exit_code'])) {
            return 'Partners analytics worker: failed code='
                . (isset($result['code']) ? $result['code'] : 'WORKER_OPERATION_FAILED')
                . PHP_EOL;
        }

        $reconciliation = isset($result['reconciliation']) && is_array($result['reconciliation'])
            ? $result['reconciliation']
            : array();
        $delivery = isset($result['delivery']) && is_array($result['delivery'])
            ? $result['delivery']
            : array();

        return 'Partners analytics worker: reconciled='
            . (isset($reconciliation['reconciled']) ? (int) $reconciliation['reconciled'] : 0)
            . ' delivered=' . (isset($delivery['delivered']) ? (int) $delivery['delivered'] : 0)
            . ' retry=' . (isset($delivery['retry']) ? (int) $delivery['retry'] : 0)
            . ' failed=' . (isset($delivery['failed']) ? (int) $delivery['failed'] : 0)
            . ' skipped=' . (isset($delivery['skipped']) ? (int) $delivery['skipped'] : 0)
            . PHP_EOL;
    }

    private function hasError($result)
    {
        return is_array($result) && !empty($result['error']);
    }
}
