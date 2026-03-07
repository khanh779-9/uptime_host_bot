<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Config/config.php';
require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Core/MonitorChecker.php';
require_once APP_PATH . '/Core/Model.php';
require_once APP_PATH . '/Models/Monitor.php';

function parse_cli_options(array $argv): array
{
    $options = [
        'loop' => false,
        'sleep_seconds' => 30,
    ];

    foreach ($argv as $arg) {
        if ($arg === '--loop' || $arg === '--watch') {
            $options['loop'] = true;
            continue;
        }

        if (str_starts_with($arg, '--sleep=')) {
            $sleepValue = (int) substr($arg, strlen('--sleep='));
            if ($sleepValue > 0) {
                $options['sleep_seconds'] = $sleepValue;
            }
        }
    }

    return $options;
}

function run_check_cycle(Monitor $monitorModel): int
{
    $monitors = $monitorModel->allActive();

    foreach ($monitors as $monitor) {
        $targetType = (string) ($monitor['target_type'] ?? 'web');
        $checkResult = MonitorChecker::checkTarget($monitor);
        $monitorModel->updateCheckResultWithLatency(
            (int) $monitor['id'],
            (int) $checkResult['status'],
            $checkResult['response_time_ms'],
            (int) ($monitor['expected_status'] ?? 200)
        );

        echo '[' . date('Y-m-d H:i:s') . '] [' . $targetType . '] ' . $monitor['url']
            . ' => ' . (int) $checkResult['status'] . ' (' . (int) $checkResult['response_time_ms'] . 'ms)' . PHP_EOL;
    }

    return count($monitors);
}

$options = parse_cli_options($argv ?? []);
$monitorModel = new Monitor();

if (!$options['loop']) {
    run_check_cycle($monitorModel);
    exit(0);
}

echo '[checker] started in loop mode, sleep=' . (int) $options['sleep_seconds'] . 's' . PHP_EOL;

while (true) {
    $checked = run_check_cycle($monitorModel);

    $maxSleepSeconds = max(1, (int) $options['sleep_seconds']);
    $nextDueInSeconds = $monitorModel->nextActiveDueInSeconds();

    if ($nextDueInSeconds === null) {
        $waitSeconds = $maxSleepSeconds;
    } else {
        $waitSeconds = min($maxSleepSeconds, max(1, $nextDueInSeconds));
    }

    echo '[checker] cycle done: checked=' . $checked . ', next_due=' . ($nextDueInSeconds ?? 'none') . 's, sleep=' . $waitSeconds . 's' . PHP_EOL;
    sleep($waitSeconds);
}
