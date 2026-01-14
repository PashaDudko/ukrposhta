<?php

namespace App\Contracts;

interface ExportFormatInterface
{
    public const CHUNK_SIZE = 100; //якщо раптом кілкьість користувачів буде не, а на порядок більша

    public function getReportColumnsNames(): array; //перераховуємо, які саме поля повинні бути в завантажуваному репорті
    public function save(array $data, string $filename): void;
}

