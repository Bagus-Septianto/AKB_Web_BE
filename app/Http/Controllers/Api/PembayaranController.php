<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Validator;
use App\Pembayaran;
use App\Meja;
use App\Pesanan;


class PembayaranController extends Controller
{
    public function index() {
        $pembayarans = Pembayaran::all();

        if(count($pembayarans) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $pembayarans
            ],200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ],404);
    }

    public function show($id) {
        $pembayaran = Pembayaran::find($id);

        if(!is_null($pembayaran)){
            return response([
                'message' => 'Retrieve Pembayaran Success',
                'data' => $pembayaran
            ],200);
        }

        return response([
            'message' => 'Pembayaran Tidak Ditemukan',
            'data' => null
        ],404);
    }

    public function storeCash(Request $request) {
        $storeData = $request->all();
        $validate = Validator::make($storeData, [
            'ID_PESANAN' => 'required',
            'id' => 'required',
            // 'ID_DETIL_KARTU' => 'required', // cash tidak perlu ID_DETIL_KARTU
            'NOMOR_MEJA' => 'required|max:10', // sudah dapat dari frontend
            // 'TANGGAL_PEMBAYARAN' => 'required', // ambil dibackend
            // 'NOMOR_PEMBAYARAN' => 'required', // ambil dari backend
            'JENIS_PEMBAYARAN' => 'required', // udah diisi dari frontend isinya 'Cash'
            // 'KODE_VERIFIKASI' => 'required', // cash tidak perlu kode verif
            'SUBTOTAL' => 'required|numeric',
            'PAJAK' => 'required|numeric', // SUBTOTAL * 0.1
            'TOTAL' => 'required|numeric', // SUBTOTAL + PAJAK
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400);

        // Set TANGGAL_PEMBAYARAN dengan waktu sekarang
        $storeData['TANGGAL_PEMBAYARAN'] = Carbon::now();
        $cariNoBayar = Carbon::parse($storeData['TANGGAL_PEMBAYARAN'])
                        ->format('dmy'); //280521
        // Search di Tabel Pembayaran, nomor pembayaran hari ini
        $pembayaranSearch = Pembayaran::where('NOMOR_PEMBAYARAN', 'LIKE', 'AKB-' . $cariNoBayar . '-%')->get();
        $suffix = $pembayaranSearch->count() + 1; // banyak nomor pembayaran hari ini + 1
        $storeData['NOMOR_PEMBAYARAN'] = 'AKB-' . Carbon::parse($storeData['TANGGAL_PEMBAYARAN'])->format('dmy') . '-' . $suffix;

        // Edit Meja, Nomor Meja yang sudah dibayar diubah statusnya
        Meja::where('NOMOR_MEJA', $storeData['NOMOR_MEJA'])->update(['STATUS_MEJA' => 'Tersedia']);

        // Insert data
        $pembayaran = Pembayaran::create($storeData);

        // Update Tabel Pesanan, Pesanan sudah dibayar (sudah punya ID_PEMBAYARAN)
        Pesanan::where('ID_PESANAN', $storeData['ID_PESANAN'])->update(['ID_PEMBAYARAN' => $pembayaran->ID_PEMBAYARAN]);

        // Struk
        $struk = DB::table('pesanan')
                    ->select('pesanan.ID_PESANAN', 'pesanan.TOTAL_MENU', 'pesanan.TOTAL_ITEM')
                    ->where('pesanan.ID_PEMBAYARAN', $pembayaran->ID_PEMBAYARAN)
                    ->first();
        $detilPesanan = DB::table('detil_pesanan')
                        ->select('detil_pesanan.JUMLAH_PESANAN', 'menu.NAMA_MENU', 'menu.HARGA_MENU',
                         DB::Raw('detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU as Subtotal'))
                        ->join('menu', 'menu.ID_MENU', 'detil_pesanan.ID_MENU')
                        ->where('detil_pesanan.ID_PESANAN', $struk->ID_PESANAN)
                        ->get();
        $struk->detilPesanan = $detilPesanan;
        return response([
            'message' => 'Berhasil Menambahkan Pembayaran',
            'struk' => $struk
        ],200);
    }

    public function storeCard(Request $request) {
        $storeData = $request->all();
        $validate = Validator::make($storeData, [
            'ID_PESANAN' => 'required',
            'id' => 'required',
            // 'ID_DETIL_KARTU' => 'required', // cash tidak perlu ID_DETIL_KARTU
            'NOMOR_MEJA' => 'required|max:10', // sudah dapat dari frontend
            // 'TANGGAL_PEMBAYARAN' => 'required', // ambil dibackend
            // 'NOMOR_PEMBAYARAN' => 'required', // ambil dari backend
            'JENIS_PEMBAYARAN' => 'required', // udah diisi dari frontend isinya 'Cash'
            'KODE_VERIFIKASI' => 'required', // cash tidak perlu kode verif
            'SUBTOTAL' => 'required|numeric',
            'PAJAK' => 'required|numeric', // SUBTOTAL * 0.1
            'TOTAL' => 'required|numeric', // SUBTOTAL + PAJAK
            // Detil Kartu
            'NAMA_PEMILIK_KARTU' => 'required|max:50',
            'NOMOR_KARTU' => 'required|max:20',
            'TANGGAL_EXP' => 'required|max:15'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400);

        // Set TANGGAL_PEMBAYARAN dengan waktu sekarang
        $storeData['TANGGAL_PEMBAYARAN'] = Carbon::now();
        $cariNoBayar = Carbon::parse($storeData['TANGGAL_PEMBAYARAN'])
                        ->format('dmy'); //280521
        // Search di Tabel Pembayaran, nomor pembayaran hari ini
        $pembayaranSearch = Pembayaran::where('NOMOR_PEMBAYARAN', 'LIKE', 'AKB-' . $cariNoBayar . '-%')->get();
        $suffix = $pembayaranSearch->count() + 1; // banyak nomor pembayaran hari ini + 1
        $storeData['NOMOR_PEMBAYARAN'] = 'AKB-' . Carbon::parse($storeData['TANGGAL_PEMBAYARAN'])->format('dmy') . '-' . $suffix;

        // Edit Meja, Nomor Meja yang sudah dibayar diubah statusnya
        Meja::where('NOMOR_MEJA', $storeData['NOMOR_MEJA'])->update(['STATUS_MEJA' => 'Tersedia']);

        // Insert data
        $pembayaran = Pembayaran::create($storeData);

        // Update Tabel Pesanan, Pesanan sudah dibayar (sudah punya ID_PEMBAYARAN)
        Pesanan::where('ID_PESANAN', $storeData['ID_PESANAN'])->update(['ID_PEMBAYARAN' => $pembayaran->ID_PEMBAYARAN]);

        // Insert data Detil Kartu
        DB::table('detil_kartu')
            ->insert([
                'ID_PEMBAYARAN' => $pembayaran->ID_PEMBAYARAN,
                'NAMA_PEMILIK_KARTU' => $storeData['NAMA_PEMILIK_KARTU'],
                'NOMOR_KARTU' => $storeData['NOMOR_KARTU'],
                'TANGGAL_EXP' => $storeData['TANGGAL_EXP'],
            ]);
        $detilKartu = DB::table('detil_kartu')
                            ->selectRaw('MAX(ID_DETIL_KARTU) as ID_DETIL_KARTU')
                            ->first();
        Pembayaran::where('ID_PEMBAYARAN', $pembayaran->ID_PEMBAYARAN)->update(['ID_DETIL_KARTU' => $detilKartu->ID_DETIL_KARTU]);

        // Struk
        $struk = DB::table('pesanan')
                    ->select('pesanan.ID_PESANAN', 'pesanan.TOTAL_MENU', 'pesanan.TOTAL_ITEM')
                    ->where('pesanan.ID_PEMBAYARAN', $pembayaran->ID_PEMBAYARAN)
                    ->first();
        $detilPesanan = DB::table('detil_pesanan')
                        ->select('detil_pesanan.JUMLAH_PESANAN', 'menu.NAMA_MENU', 'menu.HARGA_MENU',
                         DB::Raw('detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU as Subtotal'))
                        ->join('menu', 'menu.ID_MENU', 'detil_pesanan.ID_MENU')
                        ->where('detil_pesanan.ID_PESANAN', $struk->ID_PESANAN)
                        ->get();
        $struk->detilPesanan = $detilPesanan;
        return response([
            'message' => 'Berhasil Menambahkan Pembayaran',
            'struk' => $struk
        ],200);
    }

    public function destroy($id) {
        $pembayaran = Pembayaran::find($id);
        
        if(is_null($pembayaran)){
            return response([
                'message' => 'Pembayaran Not Found',
                'data' => null
            ],404);
        }

        if($pembayaran->delete()){
            return response([
                'message' => 'Data Pembayaran berhasil dihapus',
                'data' => $pembayaran,
            ],200);
        }

        return response([
            'message' => 'Delete Pembayaran Failed',
            'data' => null
        ],400);
    }

    public function update(Request $request, $id) {
        $pembayaran = Pembayaran::find($id);
        
        if(is_null($pembayaran)){
            return response([
                'message' => 'Pembayaran Not Found',
                'data' => null
            ],404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'id' => 'required',
            'NOMOR_MEJA' => 'required|max:10',
            // 'TANGGAL_PEMBAYARAN' => 'required', // ambil dibackend
            // 'NOMOR_PEMBAYARAN' => 'required', // ambil dari backend
            'JENIS_PEMBAYARAN' => 'required', // udah diisi dari frontend isinya 'Cash'
            // 'KODE_VERIFIKASI' => 'required', // cash tidak perlu kode verif
            'SUBTOTAL' => 'required|numeric',
            'PAJAK' => 'required|numeric', // SUBTOTAL * 0.1
            'TOTAL' => 'required|numeric', // SUBTOTAL + PAJAK
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400);
        
        $pembayaran->NOMOR_MEJA = $updateData['NOMOR_MEJA'];
        $pembayaran->TANGGAL_PEMBAYARAN = $updateData['TANGGAL_PEMBAYARAN'];
        $pembayaran->NOMOR_PEMBAYARAN = $updateData['NOMOR_PEMBAYARAN'];
        $pembayaran->JENIS_PEMBAYARAN = $updateData['JENIS_PEMBAYARAN'];
        $pembayaran->KODE_VERIFIKASI = $updateData['KODE_VERIFIKASI'];
        $pembayaran->SUBTOTAL = $updateData['SUBTOTAL'];
        $pembayaran->PAJAK = $updateData['PAJAK'];
        $pembayaran->TOTAL = $updateData['TOTAL'];

        if($pembayaran->save()){
            return response([
                'message' => 'Update Pembayaran Success',
                'data' => $pembayaran
            ],200);
        }

        return response([
            'message' => 'Update Pembayaran Failed',
            'data' => null
        ],400);
    }
}
