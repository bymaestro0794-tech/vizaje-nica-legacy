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

        $this->load->model('Partners_referral_analytics_model');
        $reconciliation = $this->partners_referral_analytics_model->reconcileOrderEvents(100);
        $result = $this->partners_referral_analytics_model->deliverPending(50);

        echo 'Partners analytics worker: reconciled=' . (int) $reconciliation['reconciled']
            . ' delivered=' . (int) $result['delivered']
            . ' retry=' . (int) $result['retry']
            . ' failed=' . (int) $result['failed']
            . ' skipped=' . (int) $result['skipped'] . PHP_EOL;
    }
}
