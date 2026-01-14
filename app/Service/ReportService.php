<?php

namespace App\Service;

use App\Contracts\ExportFormatInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    protected const STORAGE_DISK = 'public';
    protected const DIRECTORY = 'reports';

    protected ExportFormatInterface $exporter;

    public function setExporter(ExportFormatInterface $exporter): void
    {
        $this->exporter = $exporter;
    }

    public function loadData(string $url): array
    {
        $response = Http::get($url);

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception("Помилка API: не вдалось завантажити сторінку.");
        }
    }

    public function save(array $data, string $filename): void
    {
        if (!isset($this->exporter)) {
            throw new \Exception("Обробник репорту не встановлено");
        }

        $this->exporter->save($data, $filename);
    }

    public function download(string $filename): StreamedResponse
    {
        $path = self::DIRECTORY . '/' . $filename;

        if (!Storage::disk(self::STORAGE_DISK)->exists($path)) {
            throw new \Exception("Файл не знайдено на сервері.");
        }

        try {
            return Storage::disk(self::STORAGE_DISK)->download($path);
        } catch (\Throwable $e) {
            throw new \Exception("Помилка під час підготовки файлу до завантаження.");
        }

    }
}
