<?php

namespace App\Report;

use App\Contracts\ExportFormatInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class CsvReport implements ExportFormatInterface
{
    public function getReportColumnsNames(): array
    {
        return [
            'ID',
            'Name',
            'Username',
            'Email',
            'Address',
            'Phone',
            'Website',
            'Company',
        ];
    }

    /**
     * Перераховуємо правила форматування даних в цих полях (наприклад, розділяємо дані масиву company через '|' , як було зазначено в вимогах)
     */
    public function getFormatters(): array
    {
        return [
            'id'       => fn($val) => $val . ', ',
            'name'     => fn($val) => $val ? $val . ', ' : 'N/A, ',
            'username' => fn($val) => $val ? $val . ', ' : 'N/A, ',
            'email'    => fn($val) => $val ? $val . ', ' : 'N/A, ',
            'address'  => fn($val) => Arr::isAssoc($val) ? $this->format($val, ['geo']): 'N/A, ',
            'phone' => fn($val) => $val ? $val . ', ' : 'N/A, ',
            'website' => fn($val) => $val ? $val . ', ' : 'N/A, ',
            'company'  => fn($val) => Arr::isAssoc($val) ? $this->format($val, [], '|'): 'N/A, ',
        ];
    }

    private function format(array $data, array $keysToRemove, string $delimiter = ''): string    {
        $filtered = Arr::except($data, $keysToRemove);
        $str = '';

        foreach ($filtered as $value) {
            $str .=  $delimiter ? $delimiter . $value . $delimiter . ', ' : $value . ', ';
        }

        return $str;
    }

    public function save(array $data, string $filename): void
    {
        $handle = fopen('php://temp', 'r+');
        $formatters = $this->getFormatters();

        fputcsv($handle, $this->getReportColumnsNames());

        foreach (array_chunk($data, self::CHUNK_SIZE) as $chunk) {
            foreach ($chunk as $user) {
                $formattedRow = [];

                foreach ($formatters as $field => $callback) {
                    $value = $user[$field] ?? null;
                    $formattedRow[] = $callback($value);
                }

                fputcsv($handle, $formattedRow);
            }
        }

        rewind($handle);

        $isSaved = Storage::disk('public')->put("reports/{$filename}", $handle);

        if (!$isSaved) {
            throw new \Exception("Не вдалося зберегти файл звіту на диск.");
        }

        fclose($handle);
    }
}
