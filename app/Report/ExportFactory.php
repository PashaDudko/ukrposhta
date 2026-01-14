<?php

namespace App\Report;

use App\Contracts\ExportFormatInterface;

class ExportFactory
{
    public function make(string $format): ExportFormatInterface
    {
        return match($format) {
            'csv' => new CsvReport(),
            default => throw new \Exception("Формат: {$format} не підтримується"),
        };
    }
}
