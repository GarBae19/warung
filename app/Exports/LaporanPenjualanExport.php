<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPenjualanExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $rows;
    protected $grandTotalBeli;
    protected $grandTotalJual;

    public function __construct($rows, $grandTotalBeli, $grandTotalJual)
    {
        $this->rows           = $rows;
        $this->grandTotalBeli = $grandTotalBeli;
        $this->grandTotalJual = $grandTotalJual;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            '#',
            'Kode Transaksi',
            'Tanggal',
            'Item Nama',
            'Satuan',
            'Qty',
            'Harga Beli',
            'Harga Jual',
            'Total Beli',
            'Total Jual',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->kode_pos ?? '-',
            $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y') : '-',
            $row->nama_barang ?? '-',
            $row->nama_satuan ?? '-',
            $row->qty,
            $row->harga_beli,
            $row->harga_jual,
            $row->harga_beli * $row->qty,
            $row->harga_jual * $row->qty,
        ];
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->rows->count() + 1;

        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle("G2:J{$lastRow}")->getNumberFormat()
            ->setFormatCode('#,##0');

        // Baris grand total di bawah data
        $totalRow = $lastRow + 2;
        $sheet->setCellValue("H{$totalRow}", 'GRAND TOTAL');
        $sheet->setCellValue("I{$totalRow}", $this->grandTotalBeli);
        $sheet->setCellValue("J{$totalRow}", $this->grandTotalJual);
        $sheet->getStyle("H{$totalRow}:J{$totalRow}")->getFont()->setBold(true);
        $sheet->getStyle("I{$totalRow}:J{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');

        $profitRow = $totalRow + 1;
        $sheet->setCellValue("H{$profitRow}", 'PROFIT');
        $sheet->setCellValue("J{$profitRow}", $this->grandTotalJual - $this->grandTotalBeli);
        $sheet->getStyle("H{$profitRow}:J{$profitRow}")->getFont()->setBold(true);
        $sheet->getStyle("J{$profitRow}")->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }
}
