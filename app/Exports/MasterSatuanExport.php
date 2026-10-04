<?php

namespace App\Exports;

use App\Models\MasterSatuanModel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterSatuanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function query()
    {
        $query = MasterSatuanModel::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('kode_satuan', 'like', "%{$this->search}%")
                    ->orWhere('nama_satuan', 'like', "%{$this->search}%")
                    ->orWhere('keterangan', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return ['No', 'Kode Satuan', 'Nama Satuan', 'Keterangan'];
    }

    public function map($satuan): array
    {
        static $no = 0;
        $no++;

        return [$no, $satuan->kode_satuan, $satuan->nama_satuan, $satuan->keterangan ?? '-'];
    }
}
