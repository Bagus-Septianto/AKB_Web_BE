<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use App\Meja;

class MejaController extends Controller
{
    //method untuk menampilkan semua data meja (Read)
    public function index() {
        $mejas = Meja::all(); //mengambil semua data meja

        if(count($mejas) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $mejas
            ],200);
        } //return data semua meja dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); //return message data meja kosong
    }

    //method untuk menampilkan 1 data meja (search)
    public function show($id) {
        $meja = Meja::find($id); //mencari data meja berdasarkan id

        if(!is_null($meja)){
            return response([
                'message' => 'Retrieve Meja Success',
                'data' => $meja
            ],200);
        } //return data meja yang ditemukan dalam bentuk json

        return response([
            'message' => 'Meja Tidak Ditemukan',
            'data' => null
        ],404); //return msg ketika meja tidak ditemukan
    }

    //method untuk menambahkan 1 data meja baru (create)
    public function store(Request $request) {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'NOMOR_MEJA' => 'required|max:10', //isi validator meja
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input

        $storeData['STATUS_MEJA'] = 'Tersedia';
        $meja = Meja::create($storeData); //menambahkan data meja baru
        return response([
            'message' => 'Berhasil Menambahkan Meja',
            'data' => $meja
        ],200); //return data meja baru dalam bentuk json
    }

    //method untuk menghapus 1 data meja (delete)
    public function destroy($id) {
        $meja = Meja::find($id); //mencari data meja berdasarkan id
        
        if(is_null($meja)){
            return response([
                'message' => 'Meja Not Found',
                'data' => null
            ],404);
        } // return msg saat meja tidak ketemu

        if($meja->delete()){
            return response([
                'message' => 'Data meja berhasil dihapus',
                'data' => $meja,
            ],200);
        } //return msg saat delete berhasil

        return response([
            'message' => 'Delete Meja Failed',
            'data' => null
        ],400); // return message saat data gagal dihapus
    }

    //method untuk mengubah 1 data meja (update)
    public function update(Request $request, $id) {
        $meja = Meja::find($id); //mencari data meja berdasarkan id
        
        if(is_null($meja)){
            return response([
                'message' => 'Meja Not Found',
                'data' => null
            ],404);
        } // return msg saat meja tidak ketemu

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'NOMOR_MEJA' => 'required|max:10', //isi validator meja
            'STATUS_MEJA' => 'required|max:20'
        ]); // membuat validasi input

        if($validate->fails()) //return error invalid input
            return response(['message' => $validate->errors()],400);
        
        $meja->NOMOR_MEJA = $updateData['NOMOR_MEJA'];
        $meja->STATUS_MEJA = $updateData['STATUS_MEJA'];

        if($meja->save()){
            return response([
                'message' => 'Update Meja Success',
                'data' => $meja
            ],200);
        } //return data meja yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update Meja Failed',
            'data' => null
        ],400); //return saat gagal update data meja
    }
}
