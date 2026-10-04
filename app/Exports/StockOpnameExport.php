<?php

namespace App\Exports;

use App\Models\StockOpnameHeaderModel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockOpnameExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function query()
    {
        $query = StockOpnameHeaderModel::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('kode_stock_opname', 'like', "%{$this->search}%")
                    ->orWhere('periode', 'like', "%{$this->search}%")
                    ->orWhere('approve_by', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return ['No', 'Kode Stock Opname', 'Periode', 'Approve By', 'Approve At'];
    }

    public function map($stock): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $stock->kode_stock_opname,
            $stock->periode,
            $stock->approve_by ?? '-',
            $stock->approve_at ?? '-',
        ];
    }
}
