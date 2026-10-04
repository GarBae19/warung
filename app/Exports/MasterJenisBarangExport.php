<?php

namespace App\Exports;

use App\Models\MasterJenisBarangModel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterJenisBarangExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function query()
    {
        $query = MasterJenisBarangModel::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('kode_jenis', 'like', "%{$this->search}%")
                    ->orWhere('nama_jenis', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return ['No', 'Kode Jenis', 'Nama Jenis'];
    }

    public function map($jenis): array
    {
        static $no = 0;
        $no++;

        return [$no, $jenis->kode_jenis, $jenis->nama_jenis];
    }
}
