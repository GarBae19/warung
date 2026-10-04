<?php

namespace App\Http\Controllers;

use App\Models\MasterCabangModel;
use Illuminate\Http\Request;
use App\Models\MasterHeaderStdHargaJual;
use App\Models\MasterDetailStdHargaJual;
use App\Models\MasterDetailStdHargaBeliModel;
use App\Models\MasterBarangModel;
use App\Models\KonversiSatuanModel;
use App\Exports\StdHargaJualExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Exception;

class MasterStdHargaJual extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search  = $request->search;
            $perPage = $request->per_page ?? 10;

            $query = DB::table('master_header_std_harga_jual as a')
                ->join('master_detail_std_harga_jual as b', 'a.id', '=', 'b.id_header_std_harga_jual')
                ->join('master_barang as c', 'a.id_barang', '=', 'c.id')
                ->join('master_satuan as d', 'a.id_satuan', '=', 'd.id')
                ->join('master_cabang as e', 'b.id_cabang', '=', 'e.id')
                ->leftJoin('master_header_std_harga_beli as f', function ($join) {
                    $join->on('a.id_barang', '=', 'f.id_barang')
                        ->on('a.id_satuan', '=', 'f.id_satuan');
                })
                ->leftJoin('master_detail_std_harga_beli as g', function ($join) {
                    $join->on('f.id', '=', 'g.id_header_std_harga_beli')
                        ->on('b.id_cabang', '=', 'g.id_cabang');
                })
                ->select(
                    'a.id',
                    'a.kode_std_harga_jual',
                    'c.kode_barang',
                    'c.nama_barang',
                    'd.nama_satuan',
                    'e.nama_cabang',
                    'b.harga_jual',
                    'g.harga_beli'
                );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('a.kode_std_harga_jual', 'like', "%{$search}%")
                        ->orWhere('c.kode_barang', 'like', "%{$search}%")
                        ->orWhere('c.nama_barang', 'like', "%{$search}%");
                });
            }

            // paginate berdasarkan header, bukan baris detail
            $headerIds = (clone $query)
                ->select('a.id')
                ->distinct()
                ->orderBy('a.kode_std_harga_jual', 'desc')
                ->paginate($perPage);

            $ids = collect($headerIds->items())->pluck('id');

            $rows = $query
                ->whereIn('a.id', $ids)
                ->orderBy('a.kode_std_harga_jual', 'desc')
                ->orderBy('e.nama_cabang', 'asc')
                ->get();

            $lastKode = null;
            $rows = $rows->map(function ($row) use (&$lastKode) {
                $row->id_encrypted = encrypt($row->id);
                $row->item_kode    = $row->kode_barang ?? '-';
                $row->item_nama    = ($row->nama_barang ?? '-') . ' (' . ($row->nama_satuan ?? '-') . ')';
                $row->show_header  = $lastKode !== $row->kode_std_harga_jual;
                $lastKode = $row->kode_std_harga_jual;
                return $row;
            });

            return response()->json([
                'data'            => $rows,
                'current_page'    => $headerIds->currentPage(),
                'last_page'       => $headerIds->lastPage(),
                'from'            => $headerIds->firstItem(),
                'to'              => $headerIds->lastItem(),
                'total'           => $headerIds->total(),
                'next_page_url'   => $headerIds->nextPageUrl(),
                'prev_page_url'   => $headerIds->previousPageUrl(),
            ]);
        }

        $atribute = 'Master STD Harga Jual';
        $cabangs  = MasterCabangModel::all();
        return view('masterStdHargaJual.index', compact('atribute', 'cabangs'));
    }

    function create()
    {
        $kodehargaJual = $this->getKodeHargaJualBarang();
        $kodehargaJual = json_decode($kodehargaJual->getContent(), true);
        if ($kodehargaJual['status'] == 'success') {
            $kodehargaJual = $kodehargaJual['kodehargaJual'];
            $atribute =  'Master STD Harga Jual';
            $cabangs = MasterCabangModel::all();
            return view('masterStdHargaJual.create', compact('kodehargaJual', 'atribute', 'cabangs'));
        } else {
            return redirect()->back()->with('error', 'Gagal mendapatkan kode STD Harga Jual');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_std_harga_jual' => 'required|unique:master_header_std_harga_jual,kode_std_harga_jual',
                'id_barang2' => 'required',
                'id_satuan' => 'required',
                'harga_jual.*' => 'required|numeric|min:0',
            ], [
                'harga_jual.*.required' => 'Harga jual untuk semua cabang wajib diisi.',
                'harga_jual.*.numeric'  => 'Harga jual harus berupa angka.',
            ]);

            $headerStdHargaJual = MasterHeaderStdHargaJual::create([
                'id_barang'           => $validated['id_barang2'],
                'id_satuan'           => $validated['id_satuan'],
                'kode_std_harga_jual' => $validated['kode_std_harga_jual'],
                'created_by' => auth()->user()->name,
            ]);

            $cabangs = MasterCabangModel::all();

            foreach ($cabangs as $cabang) {
                $harga = $request->input('harga_jual.' . $cabang->id);
                $harga = $harga ? str_replace('.', '', $harga) : 0;

                MasterDetailStdHargaJual::create([
                    'id_header_std_harga_jual' => $headerStdHargaJual->id,
                    'id_cabang' => $cabang->id,
                    'harga_jual' => $harga
                ]);
            }

            return redirect()->route('masterStdHargaJual.index')
                ->with('success', 'Barang berhasil disimpan : ' . $request->kode_std_harga_jual);
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Barang gagal disimpan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Request $request)
    {
        $kodehargaJual = decrypt($request->id_std_harga_jual);

        $atribute = 'Master STD Harga Jual';

        $dataHeader = MasterHeaderStdHargaJual::find($kodehargaJual);

        $dataDetailCollection = MasterDetailStdHargaJual::where('id_header_std_harga_jual', $kodehargaJual)->get();

        $dataDetail = [];
        foreach ($dataDetailCollection as $detail) {
            $masterDetailHargaBeli = MasterDetailStdHargaBeliModel::where('id_header_std_harga_beli', function ($query) use ($dataHeader) {
                $query->select('id')
                    ->from('master_header_std_harga_beli')
                    ->where('id_barang', $dataHeader->id_barang)
                    ->where('id_satuan', $dataHeader->id_satuan)
                    ->limit(1);
            })->where('id_cabang', $detail->id_cabang)->first();

            $dataDetail[$detail->id_cabang] = [
                'harga_beli' => $masterDetailHargaBeli->harga_beli,
                'harga_jual' => $detail->harga_jual
            ];
        }

        $cabangs = MasterCabangModel::all();
        $idEncpted = encrypt($kodehargaJual);

        $mode = $request->mode;

        return view('masterStdHargaJual.show', compact('dataHeader', 'dataDetail', 'atribute', 'cabangs', 'idEncpted', 'mode'));
    }

    public function update(Request $request, $id)
    {
        try {
            $id = decrypt($id);

            $headerStdHargaJual = MasterHeaderStdHargaJual::findOrFail($id);

            $validated = $request->validate([
                'id_barang2' => 'required',
                'id_satuan' => 'required',
                'harga_jual.*' => 'required|numeric|min:0',
            ], [
                'harga_jual.*.required' => 'Harga jual untuk semua cabang wajib diisi.',
                'harga_jual.*.numeric'  => 'Harga jual harus berupa angka.',
            ]);

            $headerStdHargaJual->update([
                'id_barang'           => $validated['id_barang2'],
                'id_satuan'           => $validated['id_satuan'],
                'updated_by' => auth()->user()->name,
            ]);

            $cabangs = MasterCabangModel::all();

            foreach ($cabangs as $cabang) {
                $harga = $request->input('harga_jual.' . $cabang->id);
                $harga = $harga ? str_replace('.', '', $harga) : 0;

                MasterDetailStdHargaJual::updateOrCreate(
                    [
                        'id_header_std_harga_jual' => $headerStdHargaJual->id,
                        'id_cabang' => $cabang->id,
                    ],
                    [
                        'harga_jual' => $harga,
                    ]
                );
            }

            return redirect()->route('masterStdHargaJual.index')
                ->with('success', 'Barang berhasil diperbarui : ' . $headerStdHargaJual->kode_std_harga_jual);
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Barang gagal diperbarui: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $id = decrypt($id);

            $hargaBeliDetail = MasterDetailStdHargaJual::where('id_header_std_harga_jual', $id);
            $hargaBeliDetail->delete();

            $hargaBeli = MasterHeaderStdHargaJual::find($id);
            $hargaBeli->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'hargaBeli berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus hargaBeli: ' . $e->getMessage()
            ]);
        }
    }

    function getKodeHargaJualBarang()
    {
        try {
            $last = MasterHeaderStdHargaJual::orderBy('kode_std_harga_jual', 'desc')->first();

            if ($last) {
                $lastNumber = (int) substr($last->kode_std_harga_jual, 4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $kodehargaJual = 'STDJ' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => 'success',
                'kodehargaJual' => $kodehargaJual,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'kodehargaJual' => $e,
            ]);
        }
    }

    function getDataBarangStdHargaJual(Request $request)
    {
        $search = $request->q;

        $barangs = MasterBarangModel::select('id', 'nama_barang')
            ->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            })
            ->get();

        $satuans = KonversiSatuanModel::select(
            'id_barang',
            'id_satuan_asal',
            'id_satuan_konversi'
        )
            ->whereIn('id_barang', $barangs->pluck('id'))
            ->with([
                'satuanAsal:id,nama_satuan',
                'satuanKonversi:id,nama_satuan'
            ])
            ->get();

        $headers = MasterHeaderStdHargaJual::select('id_barang', 'id_satuan')
            ->get()
            ->map(fn($item) => $item->id_barang . '-' . $item->id_satuan)
            ->toArray();

        $headerLookup = array_flip($headers);

        $result = [];

        foreach ($barangs as $barang) {
            foreach ($satuans as $satuan) {
                if ($satuan->id_barang != $barang->id) {
                    continue;
                }

                $idSatuan = $satuan->id_satuan_konversi ?? $satuan->id_satuan_asal;

                if (isset($headerLookup[$barang->id . '-' . $idSatuan])) {
                    continue;
                }

                $namaSatuan = optional($satuan->satuanKonversi)->nama_satuan
                    ?? optional($satuan->satuanAsal)->nama_satuan;

                $result[] = [
                    'id'         => $barang->id,
                    'text'       => $barang->nama_barang . ' - ' . $namaSatuan,
                    'id_satuan'  => $idSatuan,
                    'id_barang'  => $barang->id,
                ];
            }
        }

        return response()->json($result);
    }

    function getHargaBeli(Request $request)
    {
        $id_barang = $request->id_barang;
        $id_satuan = $request->id_satuan;

        $dataBeli = MasterDetailStdHargaBeliModel::wherehas('headerStdHargaBeli', function ($query) use ($id_barang, $id_satuan) {
            $query->where('id_barang', $id_barang)->where('id_satuan', $id_satuan);
        })->get();

        if ($dataBeli) {
            return response()->json([
                'status' => 'success',
                'dataBeli' => $dataBeli,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan',
            ]);
        }
    }

    protected function getFilteredData(Request $request)
    {
        $search = $request->search;

        $query = DB::table('master_header_std_harga_jual as a')
            ->join('master_detail_std_harga_jual as b', 'a.id', '=', 'b.id_header_std_harga_jual')
            ->join('master_barang as c', 'a.id_barang', '=', 'c.id')
            ->join('master_satuan as d', 'a.id_satuan', '=', 'd.id')
            ->join('master_cabang as e', 'b.id_cabang', '=', 'e.id')
            ->leftJoin('master_header_std_harga_beli as f', function ($join) {
                $join->on('a.id_barang', '=', 'f.id_barang')
                    ->on('a.id_satuan', '=', 'f.id_satuan');
            })
            ->leftJoin('master_detail_std_harga_beli as g', function ($join) {
                $join->on('f.id', '=', 'g.id_header_std_harga_beli')
                    ->on('b.id_cabang', '=', 'g.id_cabang');
            })
            ->select(
                'a.id',
                'a.kode_std_harga_jual',
                'c.kode_barang',
                'c.nama_barang',
                'd.nama_satuan',
                'e.nama_cabang',
                'b.harga_jual',
                'g.harga_beli'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('a.kode_std_harga_jual', 'like', "%{$search}%")
                    ->orWhere('c.kode_barang', 'like', "%{$search}%")
                    ->orWhere('c.nama_barang', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('a.kode_std_harga_jual', 'desc')
            ->orderBy('e.nama_cabang', 'asc');
    }

    private function prepareExportData(Request $request)
    {
        $rows = $this->getFilteredData($request)->get();

        $lastKode = null;
        $rows = $rows->map(function ($row) use (&$lastKode) {
            $row->item_kode   = $row->kode_barang ?? '-';
            $row->item_nama   = ($row->nama_barang ?? '-') . ' (' . ($row->nama_satuan ?? '-') . ')';
            $row->show_header = $lastKode !== $row->kode_std_harga_jual;
            $lastKode = $row->kode_std_harga_jual;
            return $row;
        });

        // hitung rowspan per grup kode_std_harga_jual
        $groups = [];
        $groupIndex = -1;
        foreach ($rows as $row) {
            if ($row->show_header) {
                $groupIndex++;
                $groups[$groupIndex] = 1;
            } else {
                $groups[$groupIndex]++;
            }
        }

        $groupIndex = -1;
        $no = 0;
        foreach ($rows as $row) {
            if ($row->show_header) {
                $groupIndex++;
                $no++;
                $row->no      = $no;
                $row->rowspan = $groups[$groupIndex];
            }
        }

        return $rows;
    }

    public function exportPdf(Request $request)
    {
        $data = $this->prepareExportData($request);
        $pdf  = Pdf::loadView('masterStdHargaJual.export-pdf', compact('data'));
        return $pdf->download('data_std_harga_jual.pdf');
    }

    public function print(Request $request)
    {
        $data = $this->prepareExportData($request);
        return view('masterStdHargaJual.export-pdf', compact('data'));
    }

    public function exportExcel(Request $request)
    {
        $search = is_string($request->search) ? $request->search : null;
        return Excel::download(new StdHargaJualExport($search), 'data_std_harga_jual.xlsx');
    }

    public function exportCsv(Request $request)
    {
        $search = is_string($request->search) ? $request->search : null;
        return Excel::download(new StdHargaJualExport($search), 'data_std_harga_jual.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
