<?php

include_once 'TaskEntry.php';

class EntryLog
{

    public function __construct(public readonly array $log)
    {
    }

    public static function append(TaskEntry $entry): void
    {
        file_put_contents('time.log', $entry->toString(), FILE_APPEND);
    }

    public static function load(): self
    {
        $contents = file_get_contents('time.log');
        $lines = explode("\n", $contents);

        $entries = [];
        foreach ($lines as $line) {
            if (empty($line)) continue;

            $entries[] = TaskEntry::parse($line);
        }

        return new self($entries);
    }

    public function filterToday(): self
    {
        $cutoff = date_create();
        $cutoff->setTime(0, 0);

        return $this->filter(fn ($e) => $e->start >= $cutoff);
    }

    public function filterThisWeek(): self
    {
        $cutoff = date_create('sunday this week');
        $cutoff->setTime(0, 0);

        return $this->filter(fn ($e) => $e->start >= $cutoff);
    }


    public function filterTask(string $task): self
    {
        return $this->filter(fn ($e) => $e->task === $task);
    }

    public function filter(closure $fn): self
    {
        return new self(array_filter($this->log, $fn));
    }

    public function sum(): int
    {
        $sum = 0;

        foreach ($this->log as $entry) {
            $sum += $entry->getDuration();
        }

        return $sum;
    }

    public function sumByTask(): array
    {
        $sums = [];

        foreach ($this->log as $entry) {
            $sums[$entry->task] = ($sums[$entry->task] ?? 0) + $entry->getDuration();
        }

        return $sums;
    }

    public function printTaskSummary(TaskEntry $entry): void
    {
        $entries = $this->filterTask($entry->task);

        printf("\n");
        printf(" Just Now: %s\n", $this->printDuration($entry->getDuration()));
        printf("    Today: %s\n", $this->printDuration($entries->filterToday()->sum()));
        printf("This Week: %s\n", $this->printDuration($entries->filterThisWeek()->sum()));
    }

    public function printDuration(int $seconds): string
    {
        return date_create()->setTimestamp($seconds)->format('H:i:s');
    }

    public function printSummary(): void
    {
        printf("\n");
        printf("Today:\n");

        foreach ($this->filterToday()->sumByTask() as $task => $duration) {
            printf("%10s %s\n", $task, $this->printDuration($duration));
        }

        printf("\nThis Week:\n");

        foreach ($this->filterThisWeek()->sumByTask() as $task => $duration) {
            printf("%10s %s\n", $task, $this->printDuration($duration));
        }
    }


}
