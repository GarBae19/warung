<?php

namespace App\Http\Controllers;

use App\Models\MasterCabangModel;
use Illuminate\Http\Request;
use App\Models\MasterHeaderStdHargaBeliModel;
use App\Models\MasterDetailStdHargaBeliModel;
use App\Models\MasterBarangModel;
use App\Models\KonversiSatuanModel;
use App\Exports\StdHargaBeliExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class MasterStdHargaBeli extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search  = $request->search;
            $perPage = $request->per_page ?? 10;

            $query = DB::table('master_header_std_harga_beli as a')
                ->join('master_detail_std_harga_beli as b', 'a.id', '=', 'b.id_header_std_harga_beli')
                ->join('master_barang as c', 'a.id_barang', '=', 'c.id')
                ->join('master_satuan as d', 'a.id_satuan', '=', 'd.id')
                ->join('master_cabang as e', 'b.id_cabang', '=', 'e.id')
                ->select(
                    'a.id',
                    'a.kode_std_harga_beli',
                    'c.kode_barang',
                    'c.nama_barang',
                    'd.nama_satuan',
                    'e.nama_cabang',
                    'b.harga_beli'
                );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('a.kode_std_harga_beli', 'like', "%{$search}%")
                        ->orWhere('c.kode_barang', 'like', "%{$search}%")
                        ->orWhere('c.nama_barang', 'like', "%{$search}%");
                });
            }

            // paginate berdasarkan header, bukan baris detail
            $headerIds = (clone $query)
                ->select('a.id', 'a.kode_std_harga_beli')
                ->distinct()
                ->orderBy('a.kode_std_harga_beli', 'desc')
                ->paginate($perPage);

            $ids = collect($headerIds->items())->pluck('id');

            $rows = $query
                ->whereIn('a.id', $ids)
                ->orderBy('a.kode_std_harga_beli', 'desc')
                ->orderBy('e.nama_cabang', 'asc')
                ->get();

            $lastKode = null;
            $rows = $rows->map(function ($row) use (&$lastKode) {
                $row->id_encrypted = encrypt($row->id);
                $row->item_kode    = $row->kode_barang ?? '-';
                $row->item_nama    = ($row->nama_barang ?? '-') . ' (' . ($row->nama_satuan ?? '-') . ')';
                $row->show_header  = $lastKode !== $row->kode_std_harga_beli;
                $lastKode = $row->kode_std_harga_beli;
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

        $atribute = 'Master STD Harga Beli';
        $cabangs  = MasterCabangModel::all();
        return view('masterStdHargaBeli.index', compact('atribute', 'cabangs'));
    }

    function create()
    {
        // dd('Create Stock');
        $kodehargaBeli = $this->getKodeHargaBeliBarang();
        $kodehargaBeli = json_decode($kodehargaBeli->getContent(), true);
        if ($kodehargaBeli['status'] == 'success') {
            $kodehargaBeli = $kodehargaBeli['kodehargaBeli'];
            $atribute =  'Master STD Harga Beli';
            $cabangs = MasterCabangModel::all();
            return view('masterStdHargaBeli.create', compact('kodehargaBeli', 'atribute', 'cabangs'));
        } else {
            return redirect()->back()->with('error', 'Gagal mendapatkan kode STD Harga Beli');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_std_harga_beli' => 'required|unique:master_header_std_harga_beli,kode_std_harga_beli',
                'id_barang2' => 'required',
                'id_satuan' => 'required',

                // Validasi detail harga per cabang
                'harga_beli.*' => 'required|numeric|min:0',
            ], [
                'harga_beli.*.required' => 'Harga beli untuk semua cabang wajib diisi.',
                'harga_beli.*.numeric'  => 'Harga beli harus berupa angka.',
            ]);

            // $headerStdHargaBeli = MasterHeaderStdHargaBeliModel::create($validated);
            $headerStdHargaBeli = MasterHeaderStdHargaBeliModel::create([
                'id_barang'           => $validated['id_barang2'],
                'id_satuan'           => $validated['id_satuan'],
                'kode_std_harga_beli' => $validated['kode_std_harga_beli'],
                'created_by' => auth()->user()->name,
            ]);
            // $headerStdHargaBeli->update([]);

            $cabangs = MasterCabangModel::all();

            foreach ($cabangs as $cabang) {
                $harga = $request->input('harga_beli.' . $cabang->id);

                $harga = $harga ? str_replace('.', '', $harga) : 0;

                MasterDetailStdHargaBeliModel::create([
                    'id_header_std_harga_beli' => $headerStdHargaBeli->id,
                    'id_cabang' => $cabang->id,
                    'harga_beli' => $harga,
                ]);
            }

            return redirect()->route('masterStdHargaBeli.index')
                ->with('success', 'Barang berhasil disimpan : ' . $request->kode_std_harga_beli);
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Barang gagal disimpan: ' . $e->getMessage())
                ->withInput();
        }
    }


    public function show(Request $request)
    {
        $kodehargaBeli = decrypt($request->id_std_harga_beli);
        $atribute = 'Master STD Harga Beli';

        $dataHeader = MasterHeaderStdHargaBeliModel::find($kodehargaBeli);

        // Ubah dataDetail jadi keyed array berdasarkan id_cabang
        $dataDetailCollection = MasterDetailStdHargaBeliModel::where('id_header_std_harga_beli', $kodehargaBeli)->get();

        $dataDetail = [];
        foreach ($dataDetailCollection as $detail) {
            $dataDetail[$detail->id_cabang] = [
                'harga_beli' => $detail->harga_beli,
            ];
        }

        $cabangs = MasterCabangModel::all();
        $idEncpted = encrypt($kodehargaBeli);

        $mode = $request->mode;

        return view('masterStdHargaBeli.show', compact('dataHeader', 'dataDetail', 'atribute', 'cabangs', 'idEncpted', 'mode'));
    }


    // function detail(Request $request) {}

    public function update(Request $request, $id)
    {
        // dd($request->all());
        try {
            $id = decrypt($id);

            $headerStdHargaBeli = MasterHeaderStdHargaBeliModel::findOrFail($id);

            $validated = $request->validate([
                'id_barang2' => 'required',
                'id_satuan' => 'required',

                // Validasi detail harga per cabang
                'harga_beli.*' => 'required|numeric|min:0',
            ], [
                'harga_beli.*.required' => 'Harga beli max untuk semua cabang wajib diisi.',
                'harga_beli.*.numeric'  => 'Harga beli max harus berupa angka.',
            ]);

            // Update HEADER
            $headerStdHargaBeli->update([
                'id_barang'           => $validated['id_barang2'],
                'id_satuan'           => $validated['id_satuan'],
                'updated_by' => auth()->user()->name,
            ]);

            // Update / Create DETAIL per cabang
            $cabangs = MasterCabangModel::all();

            foreach ($cabangs as $cabang) {
                $harga = $request->input('harga_beli.' . $cabang->id);
                $harga = $harga ? str_replace('.', '', $harga) : 0;

                MasterDetailStdHargaBeliModel::updateOrCreate(
                    [
                        'id_header_std_harga_beli' => $headerStdHargaBeli->id,
                        'id_cabang' => $cabang->id,
                    ],
                    [
                        'harga_beli' => $harga,
                    ]
                );
            }

            return redirect()->route('masterStdHargaBeli.index')
                ->with('success', 'Barang berhasil diperbarui : ' . $headerStdHargaBeli->kode_std_harga_beli);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return redirect()->back()
                ->with('error', 'Barang gagal diperbarui: ' . $e->getMessage())
                ->withInput();
        }
    }


    public function destroy($id)
    {

        try {
            $id = decrypt($id);
            // dd($id);

            $hargaBeliDetail = MasterDetailStdHargaBeliModel::where('id_header_std_harga_beli', $id);
            $hargaBeliDetail->delete();

            $hargaBeli = MasterHeaderStdHargaBeliModel::find($id);
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


    function getKodeHargaBeliBarang()
    {
        try {
            $last = MasterHeaderStdHargaBeliModel::orderBy('kode_std_harga_beli', 'desc')->first();

            if ($last) {
                // Ambil angka dari belakang kode_std_harga_beli, misal BRD0005 → 5
                $lastNumber = (int) substr($last->kode_std_harga_beli, 4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $kodehargaBeli = 'STDB' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => 'success',
                'kodehargaBeli' => $kodehargaBeli,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'kodehargaBeli' => $e,
            ]);
        }
    }

    // function getDataBarangStdHarga(Request $request)
    // {

    //     $search = $request->q;

    //     $satuans = KonversiSatuanModel::all()->toArray();

    //     $barangs = MasterBarangModel::select('id', 'nama_barang')
    //         ->where(function ($q) use ($search) {
    //             $q->where('nama_barang', 'like', "%{$search}%")
    //                 ->orWhere('id', 'like', "%{$search}%");
    //         })
    //         // ->whereNotIn('id', function ($q) {
    //         //     $q->select('id_barang')
    //         //         ->from('master_header_std_harga_beli');
    //         // })
    //         ->get()
    //         ->toArray();

    //     $data = [];
    //     foreach ($barangs as $barang) {
    //         foreach ($satuans as $satuan) {
    //             $cekHeader = MasterHeaderStdHargaBeliModel::where('id_barang', $barang['id'])->where('id_satuan', $satuan['id_satuan_konversi'] ?? $satuan['id_satuan_asal'])->first();
    //             if ($cekHeader) {
    //                 continue;
    //             }
    //             if ($satuan['id_barang'] == $barang['id']) {
    //                 $data[$barang['id']]['id'][] = $barang['id'];
    //                 $data[$barang['id']]['text'][] = $barang['nama_barang'] . ' - ' . ($satuan['satuan_konversi']['nama_satuan'] ?? $satuan['satuan_asal']['nama_satuan']);
    //                 $data[$barang['id']]['satuan'][] = $satuan['id_satuan_konversi'] ?? $satuan['id_satuan_asal'];
    //             }
    //             // echo $satuan['id_barang'] . ' == ' . $barang['id'] . '<br>';
    //         }
    //     }
    //     $result = [];

    //     foreach ($data as $item) {
    //         foreach ($item['id'] as $index => $id) {
    //             $result[] = [
    //                 'id'   => $id,
    //                 'text' => $item['text'][$index],
    //                 'id_satuan' => $item['satuan'][$index],
    //                 'id_barang' => $id,
    //             ];
    //         }
    //     }

    //     // dd($satuan);
    //     // exit;

    //     return response()->json($result);
    // }

    function getDataBarangStdHarga(Request $request)
    {
        $search = $request->q;

        // Ambil barang sesuai search
        $barangs = MasterBarangModel::select('id', 'nama_barang')
            ->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            })
            ->get();

        // Ambil satuan + relasi (hindari N+1 relasi)
        $satuans = KonversiSatuanModel::with([
            'satuanAsal:id,nama_satuan',
            'satuanKonversi:id,nama_satuan'
        ])->get();

        // Ambil header sekali saja
        $headers = MasterHeaderStdHargaBeliModel::select('id_barang', 'id_satuan')
            ->get()
            ->map(function ($item) {
                return $item->id_barang . '-' . $item->id_satuan;
            })
            ->toArray();

        $headerLookup = array_flip($headers); // buat lookup cepat

        $result = [];

        foreach ($barangs as $barang) {
            foreach ($satuans as $satuan) {

                if ($satuan->id_barang != $barang->id) {
                    continue;
                }

                $idSatuan = $satuan->id_satuan_konversi ?? $satuan->id_satuan_asal;

                // cek tanpa query (pakai lookup)
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

    protected function getFilteredData(Request $request)
    {
        $search = $request->search;

        $query = DB::table('master_header_std_harga_beli as a')
            ->join('master_detail_std_harga_beli as b', 'a.id', '=', 'b.id_header_std_harga_beli')
            ->join('master_barang as c', 'a.id_barang', '=', 'c.id')
            ->join('master_satuan as d', 'a.id_satuan', '=', 'd.id')
            ->join('master_cabang as e', 'b.id_cabang', '=', 'e.id')
            ->select(
                'a.id',
                'a.kode_std_harga_beli',
                'c.kode_barang',
                'c.nama_barang',
                'd.nama_satuan',
                'e.nama_cabang',
                'b.harga_beli'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('a.kode_std_harga_beli', 'like', "%{$search}%")
                    ->orWhere('c.kode_barang', 'like', "%{$search}%")
                    ->orWhere('c.nama_barang', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('a.kode_std_harga_beli', 'desc')
            ->orderBy('e.nama_cabang', 'asc');
    }

    private function prepareExportData(Request $request)
    {
        $rows = $this->getFilteredData($request)->get();

        $lastKode = null;
        $rows = $rows->map(function ($row) use (&$lastKode) {
            $row->item_kode   = $row->kode_barang ?? '-';
            $row->item_nama   = ($row->nama_barang ?? '-') . ' (' . ($row->nama_satuan ?? '-') . ')';
            $row->show_header = $lastKode !== $row->kode_std_harga_beli;
            $lastKode = $row->kode_std_harga_beli;
            return $row;
        });

        // hitung rowspan per grup kode_std_harga_beli
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

    public function exportExcel(Request $request)
    {
        $search = is_string($request->search) ? $request->search : null;
        return Excel::download(new StdHargaBeliExport($search), 'data_std_harga_beli.xlsx');
    }

    public function exportCsv(Request $request)
    {
        $data = $this->getFilteredData($request->search);
        return Excel::download(new StdHargaBeliExport($data), 'data_std_harga_beli.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->prepareExportData($request);
        $pdf  = Pdf::loadView('masterStdHargaBeli.export-pdf', compact('data'));
        return $pdf->download('data_std_harga_beli.pdf');
    }

    public function print(Request $request)
    {
        $data = $this->prepareExportData($request);
        return view('masterStdHargaBeli.export-pdf', compact('data'));
    }
}
