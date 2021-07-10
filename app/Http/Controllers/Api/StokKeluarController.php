<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Validator;
use App\JenisBahan;
use App\StokKeluar;

class StokKeluarController extends Controller
{
    //method untuk menampilkan semua data stokkeluar (Read)
    public function index() {
        //$stokkeluars = StokKeluar::all(); //mengambil semua data stokkeluar
        $stokkeluars = DB::table('stok_keluar')
                        // ->select()
                        ->select('stok_keluar.ID_STOK_KELUAR', 'stok_keluar.ID_BAHAN', 
                            'bahan.NAMA_BAHAN', 'stok_keluar.JUMLAH_STOK_KELUAR', 
                            'stok_keluar.TANGGAL_STOK_KELUAR', 'stok_keluar.STATUS_STOK_DIBUANG')
                        ->join('bahan', 'bahan.ID_BAHAN', 'stok_keluar.ID_BAHAN')
                        ->get();

        if(count($stokkeluars) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $stokkeluars
            ],200);
        } //return data semua stokkeluar dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); //return message data stokkeluar kosong
    }

    //method untuk menampilkan 1 data meja (search)
    public function show($id) {
        $stokkeluar = StokKeluar::find($id); //mencari data meja berdasarkan id

        if(!is_null($stokkeluar)){
            return response([
                'message' => 'Retrieve Stok Keluar Success',
                'data' => $stokkeluar
            ],200);
        } //return data meja yang ditemukan dalam bentuk json

        return response([
            'message' => 'Stok Keluar Tidak Ditemukan',
            'data' => null
        ],404); //return msg ketika meja tidak ditemukan
    }

    //method untuk menambahkan 1 data meja baru (create)
    public function store(Request $request) {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'ID_BAHAN' => 'required|numeric',
            'JUMLAH_STOK_KELUAR' => 'required|numeric',
            'TANGGAL_STOK_KELUAR' => 'required|date',
            //'STATUS_STOK_DIBUANG' => 'required',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input
        
        $jmlStokKeluar = $storeData['JUMLAH_STOK_KELUAR'];
        DB::table('bahan')
            ->where('ID_BAHAN', $storeData['ID_BAHAN'])
            ->update([
                'JUMLAH_BAHAN' => DB::raw("JUMLAH_BAHAN - '$jmlStokKeluar'") 
            ]);

        $stokkeluar = StokKeluar::create($storeData); //menambahkan data meja baru
        return response([
            'message' => 'Berhasil Menambahkan Stok Keluar',
            'data' => $stokkeluar
        ],200); //return data meja baru dalam bentuk json
    }

    //method untuk menghapus 1 data meja (delete)
    public function destroy($id) {
        $stokkeluar = StokKeluar::find($id); //mencari data meja berdasarkan id
        
        if(is_null($stokkeluar)){
            return response([
                'message' => 'Stok Keluar Not Found',
                'data' => null
            ],404);
        } // return msg saat meja tidak ketemu

        $bahan = JenisBahan::where('ID_BAHAN', '=', $stokkeluar->ID_BAHAN)->first();
        $bahan->JUMLAH_BAHAN = $bahan->JUMLAH_BAHAN + $stokkeluar->JUMLAH_STOK_KELUAR; //kembalikan stok
        $bahan->save();

        if($stokkeluar->delete()){
            return response([
                'message' => 'Data Stok Keluar berhasil dihapus',
                'data' => $stokkeluar,
            ],200);
        } //return msg saat delete berhasil

        return response([
            'message' => 'Delete Stok Keluar Failed',
            'data' => null
        ],400); // return message saat data gagal dihapus
    }

    //method untuk mengubah 1 data meja (update)
    public function update(Request $request, $id) {
        $stokkeluar = StokKeluar::find($id); //mencari data meja berdasarkan id
        
        if(is_null($stokkeluar)){
            return response([
                'message' => 'Stok Keluar Not Found',
                'data' => null
            ],404);
        } // return msg saat meja tidak ketemu

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'ID_BAHAN' => 'required|numeric',
            'JUMLAH_STOK_KELUAR' => 'required|numeric',
            'TANGGAL_STOK_KELUAR' => 'required|date',
            //'STATUS_STOK_DIBUANG' => 'required',
        ]); // membuat validasi input

        if($validate->fails()) //return error invalid input
            return response(['message' => $validate->errors()],400);
        
        if($updateData['ID_BAHAN'] == $stokkeluar->ID_BAHAN) { //yang berubah cuma jumlah stok aja
            $bahan = JenisBahan::where('ID_BAHAN', '=', $updateData['ID_BAHAN'])->first();
            $bahan->JUMLAH_BAHAN = $bahan->JUMLAH_BAHAN + $stokkeluar->JUMLAH_STOK_KELUAR; //hapus perubahan lama
            $bahan->JUMLAH_BAHAN = $bahan->JUMLAH_BAHAN - $updateData['JUMLAH_STOK_KELUAR']; //ubah menggunakan data baru
            $bahan->save();
        }else if($updateData['ID_BAHAN'] != $stokkeluar->ID_BAHAN) {
            $bahanLama = JenisBahan::where('ID_BAHAN', '=', $stokkeluar->ID_BAHAN)->first();
            $bahanLama->JUMLAH_BAHAN = $bahanLama->JUMLAH_BAHAN + $stokkeluar->JUMLAH_STOK_KELUAR; //hapus perubahan lama
            $bahanLama->save();

            $bahanBaru = JenisBahan::where('ID_BAHAN', '=', $updateData['ID_BAHAN'])->first();
            $bahanBaru->JUMLAH_BAHAN = $bahanBaru->JUMLAH_BAHAN - $updateData['JUMLAH_STOK_KELUAR']; //ubah menggunakan data baru
            $bahanBaru->save();
        }
        $stokkeluar->ID_BAHAN = $updateData['ID_BAHAN'];
        $stokkeluar->JUMLAH_STOK_KELUAR = $updateData['JUMLAH_STOK_KELUAR'];
        $stokkeluar->TANGGAL_STOK_KELUAR = $updateData['TANGGAL_STOK_KELUAR'];
        //if( $updateData['STATUS_STOK_DIBUANG'] != null )
        $stokkeluar->STATUS_STOK_DIBUANG = $updateData['STATUS_STOK_DIBUANG'];

        if($stokkeluar->save()){
            return response([
                'message' => 'Update Stok Keluar Success',
                'data' => $stokkeluar
            ],200);
        }

        return response([
            'message' => 'Update Stok Keluar Failed',
            'data' => null
        ],400);
    }
}
