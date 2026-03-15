<?php

class TaskEntry
{

    public function __construct(
        public readonly string   $task,
        public readonly DateTime $start,
        public readonly DateTime $end,
    )
    {
    }

    public static function parse(string $line): TaskEntry
    {
        $parts = explode(' ', $line);
        return new TaskEntry($parts[0], date_create($parts[1]), date_create($parts[2]));
    }

    public function getDuration(): int
    {
        return $this->end->getTimestamp() - $this->start->getTimestamp();
    }

    public function toString(): string
    {
        return sprintf(
            "%s %s %s\n",
            $this->task,
            $this->start->format('Y-m-d\TH:i:s'),
            $this->end->format('Y-m-d\TH:i:s')
        );
    }

}
