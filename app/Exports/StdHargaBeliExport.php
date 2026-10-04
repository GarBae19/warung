<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StdHargaBeliExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;
    protected $no = 0;
    protected $lastKode = null;

    public function __construct($search = null)
    {
        $this->search = is_string($search) ? $search : null;
    }

    public function query()
    {
        $query = DB::table('master_header_std_harga_beli as a')
            ->join('master_detail_std_harga_beli as b', 'a.id', '=', 'b.id_header_std_harga_beli')
            ->join('master_barang as c', 'a.id_barang', '=', 'c.id')
            ->join('master_satuan as d', 'a.id_satuan', '=', 'd.id')
            ->join('master_cabang as e', 'b.id_cabang', '=', 'e.id')
            ->select(
                'a.kode_std_harga_beli',
                'c.kode_barang',
                'c.nama_barang',
                'd.nama_satuan',
                'e.nama_cabang',
                'b.harga_beli'
            );

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('a.kode_std_harga_beli', 'like', "%{$this->search}%")
                    ->orWhere('c.kode_barang', 'like', "%{$this->search}%")
                    ->orWhere('c.nama_barang', 'like', "%{$this->search}%");
            });
        }

        return $query
            ->orderBy('a.kode_std_harga_beli', 'desc')
            ->orderBy('e.nama_cabang', 'asc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Std Harga Beli',
            'Item Kode',
            'Item Nama',
            'Cabang',
            'Harga Beli',
        ];
    }

    public function map($row): array
    {
        // hanya tampilkan No, Kode, Item Kode, Item Nama sekali per grup (baris pertama tiap kode)
        $isNewGroup = $this->lastKode !== $row->kode_std_harga_beli;

        if ($isNewGroup) {
            $this->no++;
            $this->lastKode = $row->kode_std_harga_beli;
        }

        return [
            $isNewGroup ? $this->no : '',
            $isNewGroup ? $row->kode_std_harga_beli : '',
            $isNewGroup ? $row->kode_barang : '',
            $isNewGroup ? ($row->nama_barang . ' (' . $row->nama_satuan . ')') : '',
            $row->nama_cabang,
            $row->harga_beli !== null ? (float) $row->harga_beli : 0,
        ];
    }
}
