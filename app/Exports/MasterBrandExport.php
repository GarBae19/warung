<?php

namespace App\Exports;

use App\Models\MasterBrandModel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterBrandExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function query()
    {
        $query = MasterBrandModel::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('kode_brand', 'like', "%{$this->search}%")
                    ->orWhere('nama_brand', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return ['No', 'Kode Brand', 'Nama Brand'];
    }

    public function map($brand): array
    {
        static $no = 0;
        $no++;

        return [$no, $brand->kode_brand, $brand->nama_brand];
    }
}
