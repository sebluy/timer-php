#!/usr/bin/env php
<?php

include_once 'EntryLog.php';
include_once 'TaskEntry.php';

$arg1 = $argv[1] ?? null;
$arg2 = $argv[2] ?? null;
$count = count($argv);

if ($arg1 === 'start' && $count === 3) {
    start($arg2);
} else if ($count === 1) {
    EntryLog::load()->printSummary();
} else {
    printUsage();
}

function start($task): void
{
    $startTime = new DateTime();

    pcntl_async_signals(true);
    pcntl_signal(SIGINT, function () use ($task, $startTime) {
        $endTime = new DateTime();
        $entry = new TaskEntry($task, $startTime, $endTime);

        EntryLog::append($entry);
        EntryLog::load()->printTaskSummary($entry);
        exit();
    });

    while (true) {
        sleep(60 * 10);
        $current = new DateTime();
        $diff = $current->diff($startTime);
        echo $diff->format('%H:%I:00') . PHP_EOL;
    }
}

function printUsage(): void
{
    echo <<<MSG
Usage:

  timer-php 
  timer-php start <task>
MSG;
}
