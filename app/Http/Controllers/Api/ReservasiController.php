<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Reservasi;

class ReservasiController extends Controller
{
    //method untuk menampilkan semua data reservasi (Read)
    public function index() {
        //$reservasis = Reservasi::all(); //mengambil semua data reservasi
        $reservasis = DB::table('reservasi')
                        // ->select()
                        ->select('reservasi.ID_RESERVASI', 'reservasi.ID_MEJA', 
                            'meja.NOMOR_MEJA', 'reservasi.ID_CUSTOMER', 'customer.NAMA_CUSTOMER',
                            'reservasi.ID_PESANAN', 'reservasi.TANGGAL_RESERVASI',
                            'reservasi.JAM_RESERVASI', 'reservasi.STATUS_RESERVASI')
                        ->join('meja', 'meja.ID_MEJA', 'reservasi.ID_MEJA')
                        ->join('customer', 'customer.ID_CUSTOMER', 'reservasi.ID_CUSTOMER')
                        ->get();

        if(count($reservasis) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $reservasis
            ],200);
        } //return data semua reservasi dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); //return message data reservasi kosong
    }

    //method untuk menampilkan 1 data reservasi (search)
    public function show($id) {
        $reservasi = Reservasi::find($id); //mencari data reservasi berdasarkan id

        if(!is_null($reservasi)){
            return response([
                'message' => 'Retrieve Reservasi Success',
                'data' => $reservasi
            ],200);
        } //return data reservasi yang ditemukan dalam bentuk json

        return response([
            'message' => 'Reservasi Tidak Ditemukan',
            'data' => null
        ],404); //return msg ketika reservasi tidak ditemukan
    }

    //method untuk menambahkan 1 data reservasi baru (create)
    public function store(Request $request) {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'ID_MEJA' => 'required|numeric', //isi validator reservasi
            'ID_CUSTOMER' => 'required|numeric',
            // 'ID_PESANAN' => 'required|numeric', //tidak diperlukan karena bisa kosong (reservasi untuk besok)
            'TANGGAL_RESERVASI' => 'required|date',
            'JAM_RESERVASI' => 'required|max:30',
            // 'STATUS_RESERVASI' => 'required|max:50' //langsung belum datang, kalau sudah scan qr nanti diubah jadi sudah datang
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input

        $storeData['STATUS_RESERVASI'] = "Belum Datang";
        $reservasi = Reservasi::create($storeData); //menambahkan data reservasi baru

        return response([
            'message' => 'Berhasil Menambahkan Reservasi',
            'data' => $reservasi
        ],200); //return data reservasi baru dalam bentuk json
    }

    //method untuk menghapus 1 data reservasi (delete)
    public function destroy($id) {
        $reservasi = Reservasi::find($id); //mencari data reservasi berdasarkan id
        
        if(is_null($reservasi)){
            return response([
                'message' => 'Reservasi Not Found',
                'data' => null
            ],404);
        } // return msg saat reservasi tidak ketemu

        if($reservasi->delete()){
            return response([
                'message' => 'Data reservasi berhasil dihapus',
                'data' => $reservasi,
            ],200);
        } //return msg saat delete berhasil

        return response([
            'message' => 'Delete Reservasi Failed',
            'data' => null
        ],400); // return message saat data gagal dihapus
    }

    //method untuk mengubah 1 data reservasi (update)
    public function update(Request $request, $id) {
        $reservasi = Reservasi::find($id); //mencari data reservasi berdasarkan id
        
        if(is_null($reservasi)){
            return response([
                'message' => 'Reservasi Not Found',
                'data' => null
            ],404);
        } // return msg saat reservasi tidak ketemu

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'ID_MEJA' => 'required|numeric', //isi validator reservasi
            'ID_CUSTOMER' => 'required|numeric',
            // 'ID_PESANAN' => 'required|numeric', //tidak diperlukan karena bisa kosong (reservasi untuk besok)
            'TANGGAL_RESERVASI' => 'required|date',
            'JAM_RESERVASI' => 'required|max:30',
            'STATUS_RESERVASI' => 'required|max:50'
        ]); // membuat validasi input

        if($validate->fails()) //return error invalid input
            return response(['message' => $validate->errors()],400);
        
        $reservasi->ID_MEJA = $updateData['ID_MEJA'];
        $reservasi->ID_CUSTOMER = $updateData['ID_CUSTOMER'];
        if( $updateData['ID_PESANAN'] != null )
            $reservasi->ID_PESANAN = $updateData['ID_PESANAN'];
        $reservasi->TANGGAL_RESERVASI = $updateData['TANGGAL_RESERVASI'];
        $reservasi->JAM_RESERVASI = $updateData['JAM_RESERVASI'];
        $reservasi->STATUS_RESERVASI = $updateData['STATUS_RESERVASI'];

        if($reservasi->save()){
            return response([
                'message' => 'Update Reservasi Success',
                'data' => $reservasi
            ],200);
        } //return data reservasi yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update Reservasi Failed',
            'data' => null
        ],400); //return saat gagal update data reservasi
    }

    public function updatepublic(Request $request, $id) {
        $reservasi = Reservasi::find($id);
        
        if(is_null($reservasi)){
            return response([
                'message' => 'Reservasi Tidak ditemukan',
                'data' => null
            ],404);
        }

        $reservasi->STATUS_RESERVASI = "Sudah Datang";
        
        DB::table('meja')
            ->where('ID_MEJA', '=', $reservasi->ID_MEJA)
            ->update(['STATUS_MEJA' => 'Tidak Tersedia']);

        if($reservasi->save()){
            return response([
                'message' => 'Selamat Datang',
                'data' => $reservasi
            ],200);
        }

        return response([
            'message' => 'Failed',
            'data' => null
        ],400);
    }
}
