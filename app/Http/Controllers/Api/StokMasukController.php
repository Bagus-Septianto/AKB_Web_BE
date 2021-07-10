<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Validator;
use App\StokMasuk;
use App\JenisBahan;

class StokMasukController extends Controller
{
    //method untuk menampilkan semua data stokmasuk (Read)
    public function index() {
        //$stokmasuks = StokMasuk::all(); //mengambil semua data stokmasuk
        $stokmasuks = DB::table('stok_masuk')
                        // ->select()
                        ->select('stok_masuk.ID_STOK_MASUK', 'stok_masuk.ID_BAHAN', 
                            'bahan.NAMA_BAHAN', 'stok_masuk.JUMLAH_STOK_MASUK', 
                            'stok_masuk.TANGGAL_STOK_MASUK', 'stok_masuk.HARGA_STOK_MASUK')
                        ->join('bahan', 'bahan.ID_BAHAN', 'stok_masuk.ID_BAHAN')
                        ->get();

        if(count($stokmasuks) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $stokmasuks
            ],200);
        } //return data semua stokmasuk dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); //return message data stokmasuk kosong
    }

    //method untuk menampilkan 1 data (search)
    public function show($id) {
        $stokmasuk = StokMasuk::find($id);

        if(!is_null($stokmasuk)){
            return response([
                'message' => 'Retrieve Stok Masuk Success',
                'data' => $stokmasuk
            ],200);
        }

        return response([
            'message' => 'Stok Masuk Tidak Ditemukan',
            'data' => null
        ],404); //return msg ketika meja tidak ditemukan
    }

    //method untuk menambahkan 1 data meja baru (create)
    public function store(Request $request) {
        $storeData = $request->all();
        $validate = Validator::make($storeData, [
            'ID_BAHAN' => 'required|numeric',
            'JUMLAH_STOK_MASUK' => 'required|numeric',
            'TANGGAL_STOK_MASUK' => 'required|date',
            'HARGA_STOK_MASUK' => 'required|numeric',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input

        $jmlStokMasuk = $storeData['JUMLAH_STOK_MASUK'];
        DB::table('bahan')
            ->where('ID_BAHAN', $storeData['ID_BAHAN'])
            ->update([
                'JUMLAH_BAHAN' => DB::raw("JUMLAH_BAHAN + '$jmlStokMasuk'") 
            ]);

        $stokmasuk = StokMasuk::create($storeData);
        return response([
            'message' => 'Berhasil Menambahkan Stok Masuk',
            'data' => $stokmasuk
        ],200);
    }

    //method untuk menghapus 1 data meja (delete)
    public function destroy($id) {
        $stokmasuk = StokMasuk::find($id);
        
        if(is_null($stokmasuk)){
            return response([
                'message' => 'Stok Masuk Not Found',
                'data' => null
            ],404);
        }

        $bahan = JenisBahan::where('ID_BAHAN', '=', $stokmasuk->ID_BAHAN)->first();
        $bahan->JUMLAH_BAHAN = $bahan->JUMLAH_BAHAN - $stokmasuk->JUMLAH_STOK_MASUK; //kembalikan stok
        $bahan->save();

        if($stokmasuk->delete()){
            return response([
                'message' => 'Data Stok Masuk berhasil dihapus',
                'data' => $stokmasuk,
            ],200);
        }

        return response([
            'message' => 'Delete Stok Masuk Failed',
            'data' => null
        ],400);
    }

    //method untuk mengubah 1 data meja (update)
    public function update(Request $request, $id) {
        $stokmasuk = StokMasuk::find($id); //mencari data meja berdasarkan id
        
        if(is_null($stokmasuk)){
            return response([
                'message' => 'Stok Masuk Not Found',
                'data' => null
            ],404);
        } // return msg saat meja tidak ketemu

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'ID_BAHAN' => 'required|numeric',
            'JUMLAH_STOK_MASUK' => 'required|numeric',
            'TANGGAL_STOK_MASUK' => 'required|date',
            'HARGA_STOK_MASUK' => 'required|numeric',
        ]);

        if($validate->fails()) //return error invalid input
            return response(['message' => $validate->errors()],400);
            
        if($updateData['ID_BAHAN'] == $stokmasuk->ID_BAHAN) { //yang berubah cuma jumlah stok aja
            $bahan = JenisBahan::where('ID_BAHAN', '=', $updateData['ID_BAHAN'])->first();
            $bahan->JUMLAH_BAHAN = $bahan->JUMLAH_BAHAN - $stokmasuk->JUMLAH_STOK_MASUK; //hapus perubahan lama
            $bahan->JUMLAH_BAHAN = $bahan->JUMLAH_BAHAN + $updateData['JUMLAH_STOK_MASUK']; //ubah menggunakan data baru
            $bahan->save();
        }else if($updateData['ID_BAHAN'] != $stokmasuk->ID_BAHAN) {
            $bahanLama = JenisBahan::where('ID_BAHAN', '=', $stokmasuk->ID_BAHAN)->first();
            $bahanLama->JUMLAH_BAHAN = $bahanLama->JUMLAH_BAHAN - $stokmasuk->JUMLAH_STOK_MASUK; //hapus perubahan lama
            $bahanLama->save();

            $bahanBaru = JenisBahan::where('ID_BAHAN', '=', $updateData['ID_BAHAN'])->first();
            $bahanBaru->JUMLAH_BAHAN = $bahanBaru->JUMLAH_BAHAN + $updateData['JUMLAH_STOK_MASUK']; //ubah menggunakan data baru
            $bahanBaru->save();
        }
        $stokmasuk->ID_BAHAN = $updateData['ID_BAHAN'];
        $stokmasuk->JUMLAH_STOK_MASUK = $updateData['JUMLAH_STOK_MASUK'];
        $stokmasuk->TANGGAL_STOK_MASUK = $updateData['TANGGAL_STOK_MASUK'];
        $stokmasuk->HARGA_STOK_MASUK = $updateData['HARGA_STOK_MASUK'];

        if($stokmasuk->save()){
            return response([
                'message' => 'Update Stok Masuk Success',
                'data' => $stokmasuk
            ],200);
        }

        return response([
            'message' => 'Update Stok Masuk Failed',
            'data' => null
        ],400);
    }
}
