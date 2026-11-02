<?php namespace Pauldro\Minicli\v2\Util;

class Timer
{
    public $startTime = 0;
    public $endTime = 0;

    public function start()
    {
        $this->startTime = time();
    }

    public function end()
    {
        $this->endTime = time();
    }

    public function getDuration(): string
    {
        $duration = abs($this->endTime - $this->startTime);

        $units = [
            'y' => intval($duration / 31556926) % 12,
            'w' => intval($duration / 604800) % 52,
            'd' => intval($duration / 86400) % 7,
            'h' => intval($duration / 3600) % 24,
            'm' => intval($duration / 60) % 60,
            's' => intval($duration) % 60
        ];
        $parts = [];

        foreach ($units as $unit => $value) {
            if ($value < 1) {
                continue;
            }
            $parts[] = $value . $unit;
        }
        if ($parts === []) {
            return '0s';
        }
        return implode(' ', $parts);
    }
}
