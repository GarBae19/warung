<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterDepartemenModel;

class MasterDepartemen extends Controller
{
    function getDepartemen(Request $request)
    {
        $search = $request->q;

        $data = MasterDepartemenModel::select('id', 'nama_departemen')
            ->where('nama_departemen', 'like', "%{$search}%")
            ->orWhere('id', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'text' => $item->nama_departemen
            ];
        }

        return response()->json($result);
    }
}
