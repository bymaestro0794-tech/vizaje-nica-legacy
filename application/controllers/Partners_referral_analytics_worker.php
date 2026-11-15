<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** CLI-only sender for the durable referral analytics outbox. */
class Partners_referral_analytics_worker extends CI_Controller
{
    public function run()
    {
        if (!is_cli()) {
            show_404();
            return;
        }

        require_once APPPATH . 'libraries/Partners_referral_analytics_worker_runner.php';
        $runner = new Partners_referral_analytics_worker_runner();

        try {
            $this->load->config('partners_referral_analytics');
            $settings = (array) $this->config->item('partners_referral_analytics');
            $result = $runner->run(
                !empty($settings['enabled']),
                array($this, 'connectWorkerDatabase'),
                array($this, 'loadWorkerModel')
            );
        } catch (Throwable $exception) {
            $result = $runner->failure('WORKER_CONFIGURATION_ERROR');
        }

        echo $runner->formatOutput($result);
        exit((int) $result['exit_code']);
    }

    /** Open the configured CI database with framework error pages disabled. */
    public function connectWorkerDatabase()
    {
        $databaseFile = APPPATH . 'config/' . ENVIRONMENT . '/database.php';
        if (!is_file($databaseFile)) {
            $databaseFile = APPPATH . 'config/database.php';
        }
        if (!is_file($databaseFile)) {
            throw new RuntimeException('Database configuration unavailable.');
        }

        include $databaseFile;

        foreach ($this->load->get_package_paths() as $packagePath) {
            if ($packagePath === APPPATH) {
                continue;
            }
            $packageDatabaseFile = $packagePath . 'config/' . ENVIRONMENT . '/database.php';
            if (is_file($packageDatabaseFile)) {
                include $packageDatabaseFile;
            } else {
                $packageDatabaseFile = $packagePath . 'config/database.php';
                if (is_file($packageDatabaseFile)) {
                    include $packageDatabaseFile;
                }
            }
        }

        if (!isset($active_group, $db[$active_group]) || !is_array($db[$active_group])) {
            throw new RuntimeException('Database configuration unavailable.');
        }

        $connectionConfig = $db[$active_group];
        $connectionConfig['db_debug'] = false;
        $queryBuilder = isset($query_builder)
            ? $query_builder
            : (isset($active_record) ? $active_record : true);

        require_once BASEPATH . 'database/DB.php';
        $CI =& get_instance();
        $CI->db = '';
        $CI->db =& DB($connectionConfig, $queryBuilder);

        if (!is_object($CI->db) || empty($CI->db->conn_id)) {
            throw new RuntimeException('Database connection unavailable.');
        }

        return $CI->db;
    }

    /** CI3 model aliases are case-sensitive PHP properties; pin a lowercase alias. */
    public function loadWorkerModel()
    {
        $this->load->model(
            'Partners_referral_analytics_model',
            'partners_referral_analytics_model'
        );

        return $this->partners_referral_analytics_model;
    }
}
