<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;

class PreviewImport implements ToCollection, WithHeadingRow, WithLimit
{
    /**
     * Baris sampel (maks 3) setelah heading.
     */
    public array $rows = [];

    /**
     * Baca header + 3 baris data saja.
     */
    public function limit(): int
    {
        return 4;
    }

    public function collection(Collection $rows)
    {
        // Simpan maksimal 3 baris pertama setelah heading
        $this->rows = $rows->take(3)->toArray();
    }
}
