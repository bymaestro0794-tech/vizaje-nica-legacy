<?php

/** Standalone regression checks for the CLI analytics worker (PHP 7.4). */
if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}
if (!defined('APPPATH')) {
    define('APPPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
}

require_once APPPATH . 'libraries/Partners_referral_analytics_worker_runner.php';

function analyticsWorkerAssert($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

class AnalyticsWorkerTestDatabase
{
    public $conn_id = true;
    public $db_debug = true;
    private $hasOutbox;

    public function __construct($hasOutbox)
    {
        $this->hasOutbox = $hasOutbox;
    }

    public function table_exists($table)
    {
        return $this->hasOutbox && $table === 'partners_referral_analytics_outbox';
    }

    public function error()
    {
        return array('code' => 0, 'message' => '');
    }
}

class AnalyticsWorkerTestModel
{
    public function reconcileOrderEvents($limit)
    {
        return array('scanned' => 0, 'reconciled' => 0, 'error' => null);
    }

    public function deliverPending($limit)
    {
        return array('delivered' => 0, 'retry' => 0, 'failed' => 0, 'skipped' => 0, 'error' => null);
    }
}

$runner = new Partners_referral_analytics_worker_runner();
$passed = 0;
$databaseCalls = 0;
$modelCalls = 0;

$disabled = $runner->run(false, function () use (&$databaseCalls) {
    $databaseCalls++;
    throw new RuntimeException('database callback must not run');
}, function () use (&$modelCalls) {
    $modelCalls++;
    throw new RuntimeException('model callback must not run');
});
analyticsWorkerAssert($disabled['exit_code'] === 0 && $disabled['status'] === 'disabled', 'disabled analytics exits successfully');
analyticsWorkerAssert($databaseCalls === 0 && $modelCalls === 0, 'disabled analytics avoids DB and model');
analyticsWorkerAssert($runner->formatOutput($disabled) === 'Partners analytics worker: disabled' . PHP_EOL, 'disabled output is explicit');
$passed++;

$missingTable = $runner->run(true, function () {
    return new AnalyticsWorkerTestDatabase(false);
}, function () {
    throw new RuntimeException('model must not load without outbox');
});
analyticsWorkerAssert($missingTable['exit_code'] === 1 && $missingTable['code'] === 'OUTBOX_TABLE_MISSING', 'missing outbox returns safe failure');
$passed++;

$connectionError = $runner->run(true, function () {
    throw new RuntimeException('private database details must not leak');
}, function () {
    return new AnalyticsWorkerTestModel();
});
$connectionOutput = $runner->formatOutput($connectionError);
analyticsWorkerAssert($connectionError['exit_code'] === 1, 'database connection error returns nonzero');
analyticsWorkerAssert(strpos($connectionOutput, 'DATABASE') !== false, 'database error has a stable diagnostic code');
analyticsWorkerAssert(strpos($connectionOutput, 'private database details') === false, 'database exception text is not exposed');
$passed++;

$emptyOutbox = $runner->run(true, function () {
    return new AnalyticsWorkerTestDatabase(true);
}, function () {
    return new AnalyticsWorkerTestModel();
});
analyticsWorkerAssert($emptyOutbox['exit_code'] === 0 && $emptyOutbox['status'] === 'ok', 'empty outbox succeeds');
analyticsWorkerAssert(strpos($runner->formatOutput($emptyOutbox), 'delivered=0') !== false, 'empty outbox reports zero delivery');
$passed++;

$badModel = $runner->run(true, function () {
    return new AnalyticsWorkerTestDatabase(true);
}, function () {
    return new class {
        public function reconcileOrderEvents($limit) { return array('error' => 'DATABASE_OPERATION_FAILED'); }
        public function deliverPending($limit) { throw new RuntimeException('must not run after reconcile error'); }
    };
});
analyticsWorkerAssert($badModel['exit_code'] === 1 && $badModel['code'] === 'DATABASE_OPERATION_FAILED', 'model database failures propagate as nonzero');
$passed++;

$originalArgv = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;
$_SERVER['argv'] = array('index.php', 'partners_referral_analytics_worker', 'run');
include APPPATH . 'config/autoload.php';
$workerAutoload = $autoload['libraries'];
$_SERVER['argv'] = array('index.php', 'welcome', 'index');
include APPPATH . 'config/autoload.php';
$siteAutoload = $autoload['libraries'];
if ($originalArgv === null) {
    unset($_SERVER['argv']);
} else {
    $_SERVER['argv'] = $originalArgv;
}
analyticsWorkerAssert($workerAutoload === array(), 'worker CLI skips global database autoload');
analyticsWorkerAssert($siteAutoload === array('database'), 'normal site retains database autoload');
$passed++;

echo 'Referral analytics worker checks passed: ' . $passed . PHP_EOL;
