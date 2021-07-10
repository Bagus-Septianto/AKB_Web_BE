<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use App\JenisBahan;

class JenisBahanController extends Controller
{
    //method untuk menampilkan semua data bahan (Read)
    public function index() {
        $bahans = JenisBahan::all(); //mengambil semua data bahan

        if(count($bahans) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $bahans
            ],200);
        } //return data semua bahan dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); //return message data bahan kosong
    }

    //method untuk menampilkan 1 data bahan (search)
    public function show($id) {
        $bahan = JenisBahan::find($id); //mencari data bahan berdasarkan id
        //JUMLAH_BAHAN / SERVING_SIZE = sisa porsi

        if(!is_null($bahan)){
            return response([
                'message' => 'Retrieve Bahan Success',
                'data' => $bahan
            ],200);
        } //return data bahan yang ditemukan dalam bentuk json

        return response([
            'message' => 'Bahan Tidak Ditemukan',
            'data' => null
        ],404); //return msg ketika bahan tidak ditemukan
    }

    //method untuk menambahkan 1 data bahan baru (create)
    public function store(Request $request) {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'NAMA_BAHAN' => 'required|max:20', 
            //'JUMLAH_BAHAN' => 'required|numeric', // ini jenis bahan
            'UNIT_BAHAN' => 'required|max:10', 
            'PERHITUNGAN' => 'required|max:100', 
            'SERVING_SIZE' => 'required'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input
        
        $storeData['JUMLAH_BAHAN'] = "0"; // ini biar ga null
        $bahan = JenisBahan::create($storeData); //menambahkan data bahan baru
        return response([
            'message' => 'Berhasil Menambahkan Bahan',
            'data' => $bahan
        ],200); //return data bahan baru dalam bentuk json
    }

    //method untuk menghapus 1 data bahan (delete)
    public function destroy($id) {
        $bahan = JenisBahan::find($id); //mencari data bahan berdasarkan id
        
        if(is_null($bahan)){
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ],404);
        } // return msg saat bahan tidak ketemu

        if($bahan->delete()){
            return response([
                'message' => 'Data bahan berhasil dihapus',
                'data' => $bahan,
            ],200);
        } //return msg saat delete berhasil

        return response([
            'message' => 'Delete Bahan Failed',
            'data' => null
        ],400); // return message saat data gagal dihapus
    }

    //method untuk mengubah 1 data bahan (update)
    public function update(Request $request, $id) {
        $bahan = JenisBahan::find($id); //mencari data bahan berdasarkan id
        
        if(is_null($bahan)){
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ],404);
        } // return msg saat bahan tidak ketemu

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'NAMA_BAHAN' => 'required|max:20', 
            //'JUMLAH_BAHAN' => 'required|numeric', // ini jenis bahan
            'UNIT_BAHAN' => 'required|max:10', 
            'PERHITUNGAN' => 'required|max:100', 
            'SERVING_SIZE' => 'required'
        ]); // membuat validasi input

        if($validate->fails()) //return error invalid input
            return response(['message' => $validate->errors()],400);
        
        $bahan->NAMA_BAHAN = $updateData['NAMA_BAHAN'];
        //$bahan->JUMLAH_BAHAN = $updateData['JUMLAH_BAHAN']; // ini jenis bahan
        $bahan->JUMLAH_BAHAN = "0"; // ini biar ga null
        $bahan->UNIT_BAHAN = $updateData['UNIT_BAHAN'];
        $bahan->PERHITUNGAN = $updateData['PERHITUNGAN'];
        $bahan->SERVING_SIZE = $updateData['SERVING_SIZE'];

        if($bahan->save()){
            return response([
                'message' => 'Update Bahan Success',
                'data' => $bahan
            ],200);
        } //return data bahan yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update Bahan Failed',
            'data' => null
        ],400); //return saat gagal update data bahan
    }
}
