<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Menu;

//TODO cek lagi, masih dari controller menu
class MenuController extends Controller
{
    //method untuk menampilkan semua data menu (Read)
    public function index() {
        //$menus = Menu::all(); //mengambil semua data menu
        $menus = DB::table('menu')
                    ->select('menu.ID_MENU', 'menu.ID_BAHAN', 'bahan.NAMA_BAHAN',
                            'menu.JENIS_MENU', 'menu.NAMA_MENU', 'menu.DESKRIPSI_MENU',
                            'menu.UNIT_MENU', 'menu.HARGA_MENU', 'menu.gambar')
                    ->join('bahan', 'bahan.ID_BAHAN', 'menu.ID_BAHAN')
                    ->get();

        if(count($menus) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $menus
            ],200);
        } //return data semua menu dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); //return message data menu kosong
    }

    public function indexpublic() {
        //$menus = Menu::all(); //mengambil semua data menu
        $menus = DB::table('menu')
                    ->select('menu.ID_MENU', 'menu.ID_BAHAN', 'bahan.NAMA_BAHAN',
                            'menu.JENIS_MENU', 'menu.NAMA_MENU', 'menu.DESKRIPSI_MENU',
                            'menu.UNIT_MENU', 'menu.HARGA_MENU', 'menu.gambar', 'bahan.SERVING_SIZE', 'bahan.JUMLAH_BAHAN')
                    ->join('bahan', 'bahan.ID_BAHAN', 'menu.ID_BAHAN')
                    ->whereRaw('bahan.JUMLAH_BAHAN >= bahan.SERVING_SIZE') // >= muncul semua, > expected tapi nganu
                    //->orwhere('bahan.JUMLAH_BAHAN', '=', 'bahan.SERVING_SIZE')
                    ->get();

        // $anu = DB::table('bahan')
        //             ->whereraw('JUMLAH_BAHAN >= SERVING_SIZE')
        //             //->orWhere('bahan.JUMLAH_BAHAN', '=', 'bahan.SERVING_SIZE')
        //             ->get();
        if(count($menus) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $menus
            ],200);
        } //return data semua menu dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); //return message data menu kosong
    }

    //method untuk menampilkan 1 data menu (search)
    public function show($id) {
        $menu = Menu::find($id); //mencari data menu berdasarkan id

        if(!is_null($menu)){
            return response([
                'message' => 'Retrieve Menu Success',
                'data' => $menu
            ],200);
        } //return data menu yang ditemukan dalam bentuk json

        return response([
            'message' => 'Menu Tidak Ditemukan',
            'data' => null
        ],404); //return msg ketika menu tidak ditemukan
    }

    //method untuk menambahkan 1 data menu baru (create)
    public function store(Request $request) {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'ID_BAHAN' => 'required|numeric', //atau pake NAMA_BAHAN terus diquery macem AuthController
            'NAMA_MENU' => 'required|max:50',
            'JENIS_MENU' => 'required|max:50',
            'DESKRIPSI_MENU' => 'required|max:100',
            'UNIT_MENU' => 'required|max:10',
            'HARGA_MENU' => 'required|numeric'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input

        $menu = Menu::create($storeData); //menambahkan data menu baru
        return response([
            'message' => 'Berhasil Menambahkan Menu',
            'data' => $menu
        ],200); //return data menu baru dalam bentuk json
    }

    //method untuk menghapus 1 data menu (delete)
    public function destroy($id) {
        $menu = Menu::find($id); //mencari data menu berdasarkan id
        
        if(is_null($menu)){
            return response([
                'message' => 'Menu Not Found',
                'data' => null
            ],404);
        } // return msg saat menu tidak ketemu

        if($menu->delete()){
            return response([
                'message' => 'Data menu berhasil dihapus',
                'data' => $menu,
            ],200);
        } //return msg saat delete berhasil

        return response([
            'message' => 'Delete Menu Failed',
            'data' => null
        ],400); // return message saat data gagal dihapus
    }

    //method untuk mengubah 1 data menu (update)
    public function update(Request $request, $id) {
        $menu = Menu::find($id); //mencari data menu berdasarkan id
        
        if(is_null($menu)){
            return response([
                'message' => 'Menu Not Found',
                'data' => null
            ],404);
        } // return msg saat menu tidak ketemu

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'ID_BAHAN' => 'required|numeric', //atau pake NAMA_BAHAN terus diquery macem AuthController
            'NAMA_MENU' => 'required|max:50',
            'JENIS_MENU' => 'required|max:50',
            'DESKRIPSI_MENU' => 'required|max:100',
            'UNIT_MENU' => 'required|max:10',
            'HARGA_MENU' => 'required|numeric'
        ]); // membuat validasi input

        if($validate->fails()) //return error invalid input
            return response(['message' => $validate->errors()],400);
        
        $menu->ID_BAHAN = $updateData['ID_BAHAN'];
        $menu->NAMA_MENU = $updateData['NAMA_MENU'];
        $menu->JENIS_MENU = $updateData['JENIS_MENU'];
        $menu->DESKRIPSI_MENU = $updateData['DESKRIPSI_MENU'];
        $menu->UNIT_MENU = $updateData['UNIT_MENU'];
        $menu->HARGA_MENU = $updateData['HARGA_MENU'];
        $menu->gambar = $updateData['gambar'];

        if($menu->save()){
            return response([
                'message' => 'Update Menu Success',
                'data' => $menu
            ],200);
        } //return data menu yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update Menu Failed',
            'data' => null
        ],400); //return saat gagal update data menu
    }
}