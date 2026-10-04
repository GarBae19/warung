<?php

namespace App\Exports;

use App\Models\MasterBarangModel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterBarangExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function query()
    {
        $query = MasterBarangModel::with(['jenis_barang', 'brand', 'satuan']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('kode_barang', 'like', "%{$this->search}%")
                    ->orWhere('nama_barang', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Jenis Barang',
            'Brand',
            'Min Stock',
            'Satuan',
        ];
    }

    public function map($barang): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $barang->kode_barang,
            $barang->nama_barang,
            $barang->jenis_barang->nama_jenis ?? '-',
            $barang->brand->nama_brand ?? '-',
            $barang->min_stock ?? '-',
            $barang->satuan->nama_satuan ?? '-',
        ];
    }
}
