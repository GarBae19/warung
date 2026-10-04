<?php

namespace App\Exports;

use App\Models\KonversiSatuanModel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KonversiSatuanExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function query()
    {
        $query = KonversiSatuanModel::with(['barang', 'satuanAsal', 'satuanKonversi']);

        if ($this->search) {
            $query->whereHas('barang', function ($qb) {
                $qb->where('kode_barang', 'like', "%{$this->search}%")
                    ->orWhere('nama_barang', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return ['No', 'Kode Barang', 'Nama Barang', 'Nilai Konversi', 'Satuan Asal', 'Satuan Konversi'];
    }

    public function map($konversi): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $konversi->barang->kode_barang ?? '-',
            $konversi->barang->nama_barang ?? '-',
            $konversi->nilai_konversi,
            $konversi->satuanAsal->nama_satuan ?? '-',
            $konversi->satuanKonversi->nama_satuan ?? '-',
        ];
    }
}
