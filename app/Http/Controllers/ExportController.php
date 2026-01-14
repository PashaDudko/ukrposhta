<?php

namespace App\Http\Controllers;

use App\Report\ExportFactory;
use App\Service\ReportService;
use Illuminate\Http\Request;

class ExportController
{
    public function __construct(readonly ExportFactory $exportFactory, readonly ReportService $reportService)
    {
    }

    public function __invoke(Request $request)
    {
        try {
            $format = $request->get('format', 'csv'); // дефолтний формат, або отримуємо із запиту, якщо додамо інші
            $filename = "users_export_" . now()->format('Y-m-d_H-i-s') . "." . $format;

            $users = $this->reportService->loadData(config('services.api_users'));

            $exporter = $this->exportFactory->make($format);

            $this->reportService->setExporter($exporter);
            $this->reportService->save($users, $filename);

            return $this->reportService->download($filename);
        } catch (\Throwable $e) {
            \Log::error("Помилка: " . $e->getMessage(), [
                'file' => $filename,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Помилка! Перевірте логи, щоб знати що саме пішло не так.');
        }
    }
}
