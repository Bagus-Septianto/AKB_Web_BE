<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Validator;
use App\Pesanan;
use App\DetilPesanan;
use App\Menu;

class PesananController extends Controller
{
    //method untuk menampilkan semua data pesanan (Read)
    public function index() {
        $pesanans = Pesanan::all(); //mengambil semua data pesanan
        $detilPesanans = DetilPesanan::all();

        if(count($pesanans) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'dataPesanan' => $pesanans,
                'dataDetilPesanan' => $detilPesanans
            ],200);
        } //return data semua Pesanan dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); //return message data pesanan kosong
    }

    public function indexcashier() {
        $Pesanans = DB::table('pesanan') // customer
                            ->select('customer.ID_CUSTOMER', 'pesanan.ID_PESANAN', 'customer.NAMA_CUSTOMER',
                                    'meja.NOMOR_MEJA', 'pesanan.ID_PEMBAYARAN')
                                    //DB::raw('SUM(detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU) AS total')
                            //->select()
                            ->join('reservasi',     'reservasi.ID_RESERVASI',   'pesanan.ID_RESERVASI')
                            ->join('meja',          'meja.ID_MEJA',             'reservasi.ID_MEJA')
                            ->join('customer',      'customer.ID_CUSTOMER',     'reservasi.ID_CUSTOMER')
                            // tambahin ini kalo mau ada detilnya (dicomment karena masuk ke objeknya)
                            // ->join('detil_pesanan', 'detil_pesanan.ID_PESANAN', 'pesanan.ID_PESANAN')
                            // ->join('menu',          'menu.ID_MENU',             'detil_pesanan.ID_MENU')
                            ->whereNull('pesanan.ID_PEMBAYARAN') //yg belom dibayar ditampilin
                            // ->groupBy('pesanan.ID_PESANAN')
                            ->get();
        
        for($i = 0; $i < $Pesanans->count(); $i++) { // looping sebanyak objek pesanan
            // objek detil_pesanan masuk ke objek pesanan, biar objek pesanan ga kedobel dobel
            $totalz = DB::table('detil_pesanan')
                            ->selectRaw('SUM(detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU) AS total') //mejik
                            ->join('menu', 'menu.ID_MENU', 'detil_pesanan.ID_MENU')
                            ->where('ID_PESANAN', $Pesanans[$i]->ID_PESANAN)
                            ->first();
            $Pesanans[$i]->subtotal = $totalz->total; //majik, ngeluaring "totalz" dari "total". total maksudnya total yang harus dibayar
            // detil pesanannnya
            $Pesanans[$i]->detilPesanan = DB::table('detil_pesanan')
                                            ->select('menu.NAMA_MENU', 'detil_pesanan.JUMLAH_PESANAN',
                                                     'menu.HARGA_MENU', DB::raw('detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU AS Subtotal'))
                                            ->join('menu', 'menu.ID_MENU', 'detil_pesanan.ID_MENU')
                                            ->where('ID_PESANAN', $Pesanans[$i]->ID_PESANAN)
                                            ->get();
        }
        //return response(['asdf'=>$Pesanans[0]->detilPesanan],200);

        if(count($Pesanans) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'dataPesanan' => $Pesanans,
            ],200);
        } //return data semua Pesanan dalam bentuk json

        return response([
            'message' => 'Tidak ada Pesanan',
            'data' => null
        ],404); //return message data pesanan kosong
    }

    public function indexchef() {
        $detilPesanans = DB::table('detil_pesanan')
                            ->select('detil_pesanan.ID_DETIL_PESANAN', 
                                    'detil_pesanan.JUMLAH_PESANAN',
                                    'detil_pesanan.STATUS_PESANAN',
                                    'menu.NAMA_MENU')
                            ->join('menu', 'detil_pesanan.ID_MENU',
                                    'menu.ID_MENU')
                            ->where('detil_pesanan.STATUS_PESANAN', '=', 'Not Served')
                            ->get();

        if(count($detilPesanans) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'dataDetilPesanan' => $detilPesanans
            ],200);
        } //return data semua Pesanan dalam bentuk json

        return response([
            'message' => 'Tidak ada Pesanan',
            'data' => null
        ],404); //return message data pesanan kosong
    }

    public function indexwaiter() {
        $detilPesanans = DB::table('detil_pesanan')
                            ->select('detil_pesanan.ID_DETIL_PESANAN', 
                                    'detil_pesanan.JUMLAH_PESANAN',
                                    'detil_pesanan.STATUS_PESANAN',
                                    'menu.NAMA_MENU')
                            ->join('menu', 'detil_pesanan.ID_MENU',
                                    'menu.ID_MENU')
                            ->where('detil_pesanan.STATUS_PESANAN', '=', 'Ready to Serve')
                            ->get();

        if(count($detilPesanans) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'dataDetilPesanan' => $detilPesanans
            ],200);
        } //return data semua Pesanan dalam bentuk json

        return response([
            'message' => 'Tidak ada Pesanan',
            'data' => null
        ],404); //return message data pesanan kosong
    }

    //method untuk menampilkan 1 data pesanan (search)
    public function show($id) { //berdasarkan ID_PESANAN
        $pesanan = Pesanan::find($id); //mencari data pesanan berdasarkan id
        $detilPesanans = DetilPesanan::select()->where('ID_PESANAN', $id)->get();

        if(!is_null($pesanan)){
            return response([
                'message' => 'Retrieve Pesanan Success',
                'dataPesanan' => $pesanan,
                'dataDetilPesanans' => $detilPesanans
            ],200);
        } //return data pesanan yang ditemukan dalam bentuk json

        return response([
            'message' => 'Pesanan Tidak Ditemukan',
            'data' => null
        ],404); //return msg ketika pesanan tidak ditemukan
    }

    public function showpublic($id) { //berdasarkan ID_RESERVASI
        $pesanan = DB::table('pesanan')
                    ->select()
                    ->where('ID_RESERVASI', '=', $id)
                    ->whereNull('ID_PEMBAYARAN')
                    ->first();
        $detilPesanans = DB::table('detil_pesanan')
                            ->select()
                            ->where('ID_PESANAN', '=', $pesanan->ID_PESANAN)
                            ->get();
        //$detilPesanans = DetilPesanan::select()->where('ID_PESANAN', $id)->get();
        $menu = DB::table('menu')
                    ->select()
                    ->get();
        for($i = 0; $i < $menu->count(); $i++) {
            for($j = 0; $j < $detilPesanans->count(); $j++) {
                if ($menu[$i]->ID_MENU == $detilPesanans[$j]->ID_MENU) {
                    $detilPesanans[$j]->NAMA_MENU = $menu[$i]->NAMA_MENU;
                    $detilPesanans[$j]->JENIS_MENU = $menu[$i]->JENIS_MENU;
                    $detilPesanans[$j]->HARGA_MENU = $menu[$i]->HARGA_MENU;
                }
            }
        }
        

        if(!is_null($pesanan)){
            return response([
                'message' => 'Retrieve Pesanan Success',
                'dataPesanan' => $pesanan,
                'dataDetilPesanans' => $detilPesanans
            ],200);
        } //return data pesanan yang ditemukan dalam bentuk json

        return response([
            'message' => 'Pesanan Tidak Ditemukan',
            'data' => null
        ],404); //return msg ketika pesanan tidak ditemukan
    }

    //method untuk menambahkan 1 data pesanan baru (create)
    //pas manggil API PERLU nyantumin ID_RESERVASI dari aplikasi client
    public function store(Request $request) {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'ID_MENU' => 'required', //mungkin dibikin array
            'ID_RESERVASI' => 'required',
            'JUMLAH_PESANAN' => 'required',
            'TOTAL_MENU' => 'required', //jangan lupa nambahin TOTAL_MENU saat post
            'TOTAL_ITEM' => 'required', //jangan lupa nambahin TOTAL_ITEM saat post
            //'TANGGAL_PESANAN' => 'required|date' //pas ngepost auto saja ambil dari sysdate
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input

        //INSERT ke tabel pesanan dulu, ID_PEMBAYARAN = NULL
        $DataPesanan['ID_PEMBAYARAN'] = NULL; //masih pesan, bayarnya nanti dicashier
        $DataPesanan['ID_RESERVASI'] = $storeData['ID_RESERVASI'];
        $DataPesanan['TOTAL_MENU'] = $storeData['TOTAL_MENU']; // bisa diganti dengan count($request->ID_MENU);
        $DataPesanan['TOTAL_ITEM'] = $storeData['TOTAL_ITEM'];
        $DataPesanan['TANGGAL_PESANAN'] = Carbon::now(); //bisa pake sysdate dibackend
        $pesanan = Pesanan::create($DataPesanan); //menambahkan data pesanan baru

        //INSERT ke tabel detil_pesanan
        $idPesanan = Pesanan::max('ID_PESANAN'); //ambil data id terbesar/terbaru(auto increment)
        for ($i = 0; $i < count($request->ID_MENU); $i++) {
            $arrDetilPesanan[] = [
                'ID_PESANAN' => strval($idPesanan),
                'ID_MENU' => $request->ID_MENU[$i],
                'JUMLAH_PESANAN' => $request->JUMLAH_PESANAN[$i],
                //'HARGA_PESANAN' => $request->HARGA_PESANAN[$i],
                'STATUS_PESANAN' => 'Not Served',
            ];
        }
        DetilPesanan::insert($arrDetilPesanan);
        
        return response([
            'message' => 'Berhasil Menambahkan Pesanan',
            'dataPesanan' => $pesanan,
            'dataDetilPesanan' => $arrDetilPesanan
        ],200); //return data pesanan baru dalam bentuk json
    }

    public function storepublic(Request $request) {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'ID_MENU' => 'required', //mungkin dibikin array
            'ID_RESERVASI' => 'required',
            'JUMLAH_PESANAN' => 'required',
            // Kalau input 1-1 gaperlu pake TOTAL_MENU di android
            // 'TOTAL_MENU' => 'required', //jangan lupa nambahin TOTAL_MENU saat post
            // 'TOTAL_ITEM' => 'required', //jangan lupa nambahin TOTAL_ITEM saat post
            // 'TANGGAL_PESANAN' => 'required|date' //pas ngepost auto saja ambil dari sysdate
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input

        // Check tabel pesanan, where ID_RESERVASI ada, ID_PEMBAYARAN = null
        $check = DB::table('pesanan')
                    ->select('ID_PESANAN', 'ID_PEMBAYARAN', 'ID_RESERVASI', 'TOTAL_MENU', 'TOTAL_ITEM')
                    ->where('ID_RESERVASI', '=', $storeData['ID_RESERVASI'])
                    ->whereNull('ID_PEMBAYARAN')
                    ->first();

        if ($check == null) {
            // kosong / belum ada pesanan maka bikin baru
            // INSERT ke tabel pesanan dulu, ID_PEMBAYARAN = NULL
            $DataPesanan['ID_PEMBAYARAN'] = NULL; //masih pesan, bayarnya nanti dicashier
            $DataPesanan['ID_RESERVASI'] = $storeData['ID_RESERVASI'];
            $DataPesanan['TOTAL_MENU'] = 1; // bisa diganti dengan count($request->ID_MENU);
            $DataPesanan['TOTAL_ITEM'] = $storeData['JUMLAH_PESANAN'];
            $DataPesanan['TANGGAL_PESANAN'] = Carbon::now(); //bisa pake sysdate dibackend
            $pesanan = Pesanan::create($DataPesanan); //menambahkan data pesanan baru

            //INSERT ke tabel detil_pesanan
            $idPesanan = Pesanan::max('ID_PESANAN'); //ambil data id terbesar/terbaru(auto increment)
            DB::table('reservasi')
                ->where('ID_RESERVASI', '=', $storeData['ID_RESERVASI'])
                ->update(['ID_PESANAN' => strval($idPesanan)]);
            DetilPesanan::insert([
                'ID_PESANAN' => strval($idPesanan),
                'ID_MENU' => $storeData['ID_MENU'],
                'JUMLAH_PESANAN' => $storeData['JUMLAH_PESANAN'],
                //'HARGA_PESANAN' => $request->HARGA_PESANAN[$i],
                'STATUS_PESANAN' => 'Not Served',
            ]);
        } else {
            // Sudah ada pesanan
            // Ambil ID_MENU, JUMLAH_PESANAN dari detil_pesanan yang ID_RESERVASInya = $storeData['ID_RESERVASI']
            $checkDetil = DB::table('detil_pesanan')
                            ->select('detil_pesanan.ID_MENU', 'detil_pesanan.JUMLAH_PESANAN')
                            ->join('pesanan', 'pesanan.ID_PESANAN', 'detil_pesanan.ID_PESANAN')
                            ->where('pesanan.ID_RESERVASI', '=', $storeData['ID_RESERVASI'])
                            ->get();

            for($i = 0; $i < $checkDetil->count(); $i++) { // looping sebanyak objek checkDetil
                if ($checkDetil[$i]->ID_MENU == $storeData['ID_MENU']) {
                    // di tabel detilPesanan ada
                    // TOTAL_MENU tetap, TOTAL_ITEM nambah
                    // tetap nambahin di detil_pesanan, soalnya STATUS_PESANAN bisa beda beda
                    $newTOTAL_ITEM = $check->TOTAL_ITEM + $storeData['JUMLAH_PESANAN'];
                    // update TOTAL_ITEM di tabel pesanan
                    DB::table('pesanan')
                        ->where('ID_RESERVASI', '=', $storeData['ID_RESERVASI'])
                        ->whereNull('ID_PEMBAYARAN')
                        ->update(['TOTAL_ITEM' => $newTOTAL_ITEM]);
                    // insert ke tabel detil_pesanan
                    DB::table('detil_pesanan')
                        ->insert([
                            'ID_PESANAN' => $check->ID_PESANAN,
                            'ID_MENU' => $storeData['ID_MENU'],
                            'JUMLAH_PESANAN' => $storeData['JUMLAH_PESANAN'],
                            'STATUS_PESANAN' => 'Not Served'
                        ]);
                    break;
                } else if ($i == $checkDetil->count()-1) {
                    // end of the loop gaketemu (belom ada di detil pesanan)
                    // TOTAL_MENU nambah, TOTAL_ITEM nambah
                    $newTOTAL_MENU = $check->TOTAL_MENU + 1;
                    $newTOTAL_ITEM = $check->TOTAL_ITEM + $storeData['JUMLAH_PESANAN'];
                    DB::table('pesanan')
                        ->where('ID_RESERVASI', '=', $storeData['ID_RESERVASI'])
                        ->whereNull('ID_PEMBAYARAN')
                        ->update([
                            'TOTAL_MENU' => $newTOTAL_MENU,
                            'TOTAL_ITEM' => $newTOTAL_ITEM
                        ]);
                    // gaperlu break karena sudah end of the loop
                    // insert ke tabel detil_pesanan
                    DB::table('detil_pesanan')
                        ->insert([
                            'ID_PESANAN' => $check->ID_PESANAN,
                            'ID_MENU' => $storeData['ID_MENU'],
                            'JUMLAH_PESANAN' => $storeData['JUMLAH_PESANAN'],
                            'STATUS_PESANAN' => 'Not Served'
                        ]);
                }
            }
        }
        
        return response([
            'message' => 'Berhasil Menambahkan Pesanan',
        ],200); //return data pesanan baru dalam bentuk json
    }

    //method untuk mengubah 1 data pesanan (update)
    public function updateDetil($id) {
        $detilPesanan = DetilPesanan::find($id); //mencari data pesanan berdasarkan id
        
        if(is_null($detilPesanan)){
            return response([
                'message' => 'Pesanan Not Found',
                'data' => null
            ],404);
        } // return msg saat pesanan tidak ketemu
        
        $detilPesanan->STATUS_PESANAN = 'Ready to Serve';

        if($detilPesanan->save()){
            $servingBahan = DB::table('bahan')
                            ->select('bahan.SERVING_SIZE', 'bahan.ID_BAHAN', 'bahan.JUMLAH_BAHAN')
                            ->join('menu', 'menu.ID_BAHAN', 'bahan.ID_BAHAN')
                            ->join('detil_pesanan', 'detil_pesanan.ID_MENU', 'menu.ID_MENU')
                            ->where('menu.ID_MENU', '=', $detilPesanan->ID_MENU)
                            ->first();
                        // detil_pesanan.JUMLAH_PESANAN * bahan.SERVING_SIZE
            $stokKeluar = $detilPesanan->JUMLAH_PESANAN * $servingBahan->SERVING_SIZE;
            
            // Insert data ke tabel stok_keluar
            DB::table('stok_keluar')->insert([
                'ID_BAHAN' => $servingBahan->ID_BAHAN,
                'JUMLAH_STOK_KELUAR' => $stokKeluar, // detil_pesanan.JUMLAH_PESANAN * bahan.SERVING_SIZE
                'TANGGAL_STOK_KELUAR' => Carbon::now()->toDateString(),
                'STATUS_STOK_DIBUANG' => null
            ]);

            // Update stok di tabel bahan
            DB::table('bahan')
                ->where('ID_BAHAN', $servingBahan->ID_BAHAN)
                ->update([
                    //JUMLAH_BAHAN - $stokKeluar
                    'JUMLAH_BAHAN' => $servingBahan->JUMLAH_BAHAN - $stokKeluar
                ]);

            return response([
                'message' => 'Update Pesanan Success',
                'data' => $detilPesanan
            ],200);
        } //return data pesanan yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update Pesanan Failed',
            'data' => null
        ],400); //return saat gagal update data pesanan
    }

    public function detilUpdate($id) {
        $detilPesanan = DetilPesanan::find($id); //mencari data pesanan berdasarkan id
        
        if(is_null($detilPesanan)){
            return response([
                'message' => 'Pesanan Not Found',
                'data' => null
            ],404);
        } // return msg saat pesanan tidak ketemu
        
        $detilPesanan->STATUS_PESANAN = 'Served';

        if($detilPesanan->save()){
            return response([
                'message' => 'Update Pesanan Success',
                'data' => $detilPesanan
            ],200);
        } //return data pesanan yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update Pesanan Failed',
            'data' => null
        ],400); //return saat gagal update data pesanan
    }
}
