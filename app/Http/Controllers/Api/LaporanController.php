<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Validator;

class LaporanController extends Controller
{
    public function stokbahancustom($awal, $akhir) {
        $dateAwal = Carbon::parse($awal);
        $dateAkhir = Carbon::parse($akhir);
        // $diff = $dateAkhir->diffInDays($dateAwal);
        
        $unit = DB::table('bahan')->select('ID_BAHAN', 'NAMA_BAHAN', 'JUMLAH_BAHAN', 'UNIT_BAHAN')->get();

        for ($j = 0; $j < $unit->count(); $j++) {
            $dataMasuk = DB::table('bahan')
                            ->join('stok_masuk', 'stok_masuk.ID_BAHAN', 'bahan.ID_BAHAN')
                            ->selectRaw('IFNULL(SUM(stok_masuk.JUMLAH_STOK_MASUK), 0) AS incoming')
                            ->where('stok_masuk.ID_BAHAN', '=', $unit[$j]->ID_BAHAN)
                            ->whereBetween('stok_masuk.TANGGAL_STOK_MASUK', [$dateAwal, $dateAkhir])
                            ->first();
            $dataKeluar = DB::table('bahan')
                            ->join('stok_keluar', 'stok_keluar.ID_BAHAN', 'bahan.ID_BAHAN')
                            ->selectRaw('IFNULL(SUM(stok_keluar.JUMLAH_STOK_KELUAR), 0) AS waste')
                            ->where('stok_keluar.ID_BAHAN', '=', $unit[$j]->ID_BAHAN)
                            ->where('stok_keluar.STATUS_STOK_DIBUANG', '=', 'Dibuang')
                            ->whereBetween('stok_keluar.TANGGAL_STOK_KELUAR', [$dateAwal, $dateAkhir])
                            ->first();
            $jumlahBahan = $unit[$j]->JUMLAH_BAHAN;
            $jumlahBahanKurangMasuk = DB::table('bahan')
                                        ->join('stok_masuk', 'stok_masuk.ID_BAHAN', 'bahan.ID_BAHAN')
                                        ->selectRaw("'$jumlahBahan'-IFNULL(SUM(stok_masuk.JUMLAH_STOK_MASUK), 0) AS remaining")
                                        ->where('stok_masuk.ID_BAHAN', '=', $unit[$j]->ID_BAHAN)
                                        ->whereBetween('stok_masuk.TANGGAL_STOK_MASUK', [$dateAkhir, Carbon::now()])
                                        ->first();
            $remaining = DB::table('bahan')
                            ->join('stok_keluar', 'stok_keluar.ID_BAHAN', 'bahan.ID_BAHAN')
                            ->selectRaw("'$jumlahBahanKurangMasuk->remaining'+IFNULL(SUM(stok_keluar.JUMLAH_STOK_KELUAR), 0) AS remaining")
                            ->where('stok_keluar.ID_BAHAN', '=', $unit[$j]->ID_BAHAN)
                            ->whereBetween('stok_keluar.TANGGAL_STOK_KELUAR', [$dateAkhir, Carbon::now()])
                            ->first();
                            
            $laporan[$j] = array(
                "nomor" => $j + 1,
                "nama" => $unit[$j]->NAMA_BAHAN,
                "unit" => $unit[$j]->UNIT_BAHAN,
                "incoming" => $dataMasuk->incoming,
                "remaining" => $remaining->remaining,
                "waste" => $dataKeluar->waste
            );
        }

        return response([
            'message' => 'Tampil Data Laporan Stok Bahan Berhasil',
            'data' => $laporan,
        ], 200);
    }

    public function stokbahan($tahun, $bulan, $bahan) {
        $daysinmonth = Carbon::create($tahun, $bulan)->daysInMonth;
        $unit = DB::table('bahan')->select('UNIT_BAHAN', 'JUMLAH_BAHAN')->where('ID_BAHAN', '=', $bahan)->get();
        for ($i = 0; $i < $daysinmonth; $i++) {
            $dataMasuk = DB::table('bahan')
                            ->join('stok_masuk', 'stok_masuk.ID_BAHAN', 'bahan.ID_BAHAN')
                            ->selectRaw('IFNULL(SUM(stok_masuk.JUMLAH_STOK_MASUK), 0) AS incoming')
                            ->where('stok_masuk.ID_BAHAN', '=', $bahan)
                            ->whereDay('stok_masuk.TANGGAL_STOK_MASUK', $i+1)
                            ->whereMonth('stok_masuk.TANGGAL_STOK_MASUK', $bulan)
                            ->whereYear('stok_masuk.TANGGAL_STOK_MASUK', $tahun)
                            ->first();
            $dataKeluar = DB::table('bahan')
                            ->join('stok_keluar', 'stok_keluar.ID_BAHAN', 'bahan.ID_BAHAN')
                            ->selectRaw('IFNULL(SUM(stok_keluar.JUMLAH_STOK_KELUAR), 0) AS waste')
                            ->where('stok_keluar.ID_BAHAN', '=', $bahan)
                            ->where('stok_keluar.STATUS_STOK_DIBUANG', '=', 'Dibuang')
                            ->whereDay('stok_keluar.TANGGAL_STOK_KELUAR', $i+1)
                            ->whereMonth('stok_keluar.TANGGAL_STOK_KELUAR', $bulan)
                            ->whereYear('stok_keluar.TANGGAL_STOK_KELUAR', $tahun)
                            ->first();
            $tanggal = $i+1 . ' ' . Carbon::createFromFormat('!m', $bulan)->format('F') . ' ' . $tahun;
            $jumlahBahan = $unit[0]->JUMLAH_BAHAN;
            $jumlahBahanKurangMasuk = DB::table('bahan')
                                        ->join('stok_masuk', 'stok_masuk.ID_BAHAN', 'bahan.ID_BAHAN')
                                        ->selectRaw("'$jumlahBahan'-IFNULL(SUM(stok_masuk.JUMLAH_STOK_MASUK), 0) AS remaining")
                                        ->where('stok_masuk.ID_BAHAN', '=', $bahan)
                                        ->whereBetween('stok_masuk.TANGGAL_STOK_MASUK', [Carbon::parse($tanggal), Carbon::now()])
                                        ->first();
            $remaining = DB::table('bahan')
                            ->join('stok_keluar', 'stok_keluar.ID_BAHAN', 'bahan.ID_BAHAN')
                            ->selectRaw("'$jumlahBahanKurangMasuk->remaining'+IFNULL(SUM(stok_keluar.JUMLAH_STOK_KELUAR), 0) AS remaining")
                            ->where('stok_keluar.ID_BAHAN', '=', $bahan)
                            ->whereBetween('stok_keluar.TANGGAL_STOK_KELUAR', [Carbon::parse($tanggal), Carbon::now()])
                            ->first();
            $laporan[$i] = array(
                "nomor" => $i + 1,
                "tanggal" => $tanggal,
                "unit" => $unit[0]->UNIT_BAHAN,
                "incoming" => $dataMasuk->incoming,
                "remaining" => $remaining->remaining,
                "waste" => $dataKeluar->waste
            );
        }

        return response([
            'message' => 'Tampil Data Laporan Stok Bahan Berhasil',
            'data' => $laporan
        ], 200);
    }
    
    public function penjualanitem($tahun, $bulan) {
        $idMenuUtama = DB::table('menu')
                            ->select('ID_MENU')
                            ->where('JENIS_MENU','=','Makanan Utama')
                            ->pluck('ID_MENU');
        for ($i = 0; $i < count($idMenuUtama); $i++) { 
            $dataMenuUtama = DB::table('detil_pesanan')
                                ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
                                ->join('pesanan', 'pesanan.ID_PESANAN', '=', 'detil_pesanan.ID_PESANAN')
                                ->select('menu.NAMA_MENU','menu.UNIT_MENU',
                                        DB::RAW('MAX(detil_pesanan.JUMLAH_PESANAN) AS "penjualan_harian_tertinggi"'),
                                        DB::RAW('SUM(detil_pesanan.JUMLAH_PESANAN) AS "total_penjualan"'))
                                ->where('menu.JENIS_MENU', '=', 'Makanan Utama')
                                ->where('menu.ID_MENU', '=', $idMenuUtama[$i])
                                ->whereMonth('pesanan.TANGGAL_PESANAN', $bulan)
                                ->whereYear('pesanan.TANGGAL_PESANAN', $tahun)
                                ->first();
    
            $utama[$i] = array(
                "nomor" => $i+1,
                "nama_menu" => $dataMenuUtama->NAMA_MENU,
                "unit_menu" => $dataMenuUtama->UNIT_MENU,
                "penjualan_harian_tertinggi" => $dataMenuUtama->penjualan_harian_tertinggi,
                "total_penjualan" => $dataMenuUtama->total_penjualan,
            );
        }

        $idMenuSide = DB::table('menu')
                            ->select('ID_MENU')
                            ->where('JENIS_MENU','=','Makanan Side Dish')
                            ->pluck('ID_MENU');
        for ($i = 0; $i < count($idMenuSide); $i++) { 
            $dataMenuSide = DB::table('detil_pesanan')
                                ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
                                ->join('pesanan', 'pesanan.ID_PESANAN', '=', 'detil_pesanan.ID_PESANAN')
                                ->select('menu.NAMA_MENU','menu.UNIT_MENU',
                                        DB::RAW('MAX(detil_pesanan.JUMLAH_PESANAN) AS "penjualan_harian_tertinggi"'),
                                        DB::RAW('SUM(detil_pesanan.JUMLAH_PESANAN) AS "total_penjualan"'))
                                ->where('menu.JENIS_MENU', '=', 'Makanan Side Dish')
                                ->where('menu.ID_MENU', '=', $idMenuSide[$i])
                                ->whereMonth('pesanan.TANGGAL_PESANAN', $bulan)
                                ->whereYear('pesanan.TANGGAL_PESANAN', $tahun)
                                ->first();
    
            $side[$i] = array(
                "nomor" => $i+1,
                "nama_menu" => $dataMenuSide->NAMA_MENU,
                "unit_menu" => $dataMenuSide->UNIT_MENU,
                "penjualan_harian_tertinggi" => $dataMenuSide->penjualan_harian_tertinggi,
                "total_penjualan" => $dataMenuSide->total_penjualan,
            );
        }

        $idMinuman = DB::table('menu')
                            ->select('ID_MENU')
                            ->where('JENIS_MENU','=','Minuman')
                            ->pluck('ID_MENU');
        for ($i = 0; $i < count($idMinuman); $i++) { 
            $dataMinuman = DB::table('detil_pesanan')
                                ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
                                ->join('pesanan', 'pesanan.ID_PESANAN', '=', 'detil_pesanan.ID_PESANAN')
                                ->select('menu.NAMA_MENU','menu.UNIT_MENU',
                                        DB::RAW('MAX(detil_pesanan.JUMLAH_PESANAN) AS "penjualan_harian_tertinggi"'),
                                        DB::RAW('SUM(detil_pesanan.JUMLAH_PESANAN) AS "total_penjualan"'))
                                ->where('menu.JENIS_MENU', '=', 'Minuman')
                                ->where('menu.ID_MENU', '=', $idMinuman[$i])
                                ->whereMonth('pesanan.TANGGAL_PESANAN', $bulan)
                                ->whereYear('pesanan.TANGGAL_PESANAN', $tahun)
                                ->first();
    
            $minuman[$i] = array(
                "nomor" => $i+1,
                "nama_menu" => $dataMinuman->NAMA_MENU,
                "unit_menu" => $dataMinuman->UNIT_MENU,
                "penjualan_harian_tertinggi" => $dataMinuman->penjualan_harian_tertinggi,
                "total_penjualan" => $dataMinuman->total_penjualan,
            );
        }
        
        return response([
            'message' => 'Tampil Data Laporan Penjualan Item All Berhasil',
            'utama' => $utama,
            'side' => $side,
            'minuman' => $minuman,
        ], 200);
    }

    public function penjualanitemall($tahun) {
        $idMenuUtama = DB::table('menu')
                            ->select('ID_MENU')
                            ->where('JENIS_MENU','=','Makanan Utama')
                            ->pluck('ID_MENU');
        for ($i = 0; $i < count($idMenuUtama); $i++) { 
            $dataMenuUtama = DB::table('detil_pesanan')
                                ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
                                ->join('pesanan', 'pesanan.ID_PESANAN', '=', 'detil_pesanan.ID_PESANAN')
                                ->select('menu.NAMA_MENU','menu.UNIT_MENU',
                                        DB::RAW('MAX(detil_pesanan.JUMLAH_PESANAN) AS "penjualan_harian_tertinggi"'),
                                        DB::RAW('SUM(detil_pesanan.JUMLAH_PESANAN) AS "total_penjualan"'))
                                ->where('menu.JENIS_MENU', '=', 'Makanan Utama')
                                ->where('menu.ID_MENU', '=', $idMenuUtama[$i])
                                ->whereYear('pesanan.TANGGAL_PESANAN', $tahun)
                                ->first();
    
            $utama[$i] = array(
                "nomor" => $i+1,
                "nama_menu" => $dataMenuUtama->NAMA_MENU,
                "unit_menu" => $dataMenuUtama->UNIT_MENU,
                "penjualan_harian_tertinggi" => $dataMenuUtama->penjualan_harian_tertinggi,
                "total_penjualan" => $dataMenuUtama->total_penjualan,
            );
        }

        $idMenuSide = DB::table('menu')
                            ->select('ID_MENU')
                            ->where('JENIS_MENU','=','Makanan Side Dish')
                            ->pluck('ID_MENU');
        for ($i = 0; $i < count($idMenuSide); $i++) { 
            $dataMenuSide = DB::table('detil_pesanan')
                                ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
                                ->join('pesanan', 'pesanan.ID_PESANAN', '=', 'detil_pesanan.ID_PESANAN')
                                ->select('menu.NAMA_MENU','menu.UNIT_MENU',
                                        DB::RAW('MAX(detil_pesanan.JUMLAH_PESANAN) AS "penjualan_harian_tertinggi"'),
                                        DB::RAW('SUM(detil_pesanan.JUMLAH_PESANAN) AS "total_penjualan"'))
                                ->where('menu.JENIS_MENU', '=', 'Makanan Side Dish')
                                ->where('menu.ID_MENU', '=', $idMenuSide[$i])
                                ->whereYear('pesanan.TANGGAL_PESANAN', $tahun)
                                ->first();
    
            $side[$i] = array(
                "nomor" => $i+1,
                "nama_menu" => $dataMenuSide->NAMA_MENU,
                "unit_menu" => $dataMenuSide->UNIT_MENU,
                "penjualan_harian_tertinggi" => $dataMenuSide->penjualan_harian_tertinggi,
                "total_penjualan" => $dataMenuSide->total_penjualan,
            );
        }

        $idMinuman = DB::table('menu')
                            ->select('ID_MENU')
                            ->where('JENIS_MENU','=','Minuman')
                            ->pluck('ID_MENU');
        for ($i = 0; $i < count($idMinuman); $i++) { 
            $dataMinuman = DB::table('detil_pesanan')
                                ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
                                ->join('pesanan', 'pesanan.ID_PESANAN', '=', 'detil_pesanan.ID_PESANAN')
                                ->select('menu.NAMA_MENU','menu.UNIT_MENU',
                                        DB::RAW('MAX(detil_pesanan.JUMLAH_PESANAN) AS "penjualan_harian_tertinggi"'),
                                        DB::RAW('SUM(detil_pesanan.JUMLAH_PESANAN) AS "total_penjualan"'))
                                ->where('menu.JENIS_MENU', '=', 'Minuman')
                                ->where('menu.ID_MENU', '=', $idMinuman[$i])
                                ->whereYear('pesanan.TANGGAL_PESANAN', $tahun)
                                ->first();
    
            $minuman[$i] = array(
                "nomor" => $i+1,
                "nama_menu" => $dataMinuman->NAMA_MENU,
                "unit_menu" => $dataMinuman->UNIT_MENU,
                "penjualan_harian_tertinggi" => $dataMinuman->penjualan_harian_tertinggi,
                "total_penjualan" => $dataMinuman->total_penjualan,
            );
        }
        
        return response([
            'message' => 'Tampil Data Laporan Penjualan Item All Berhasil',
            'utama' => $utama,
            'side' => $side,
            'minuman' => $minuman,
        ], 200);
    }

    public function pendapatanbulanan($tahun) {
        for($i = 0; $i < 12; $i++) {
            // subtotal dari tabel pembayaran 
            $makanan[$i] = DB::table('pesanan')
            ->join('detil_pesanan', 'detil_pesanan.ID_PESANAN', '=', 'pesanan.ID_PESANAN')
            ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
            ->selectRaw('ifnull(sum(detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU), 0) as subtotalmakanan')
            ->where('menu.JENIS_MENU', '=', 'Makanan Utama')
            ->whereNotNull('pesanan.ID_PEMBAYARAN')
            ->whereMonth('pesanan.TANGGAL_PESANAN', $i+1)
            ->whereYear('pesanan.TANGGAL_PESANAN', $tahun)
            ->first();

            $sidedish[$i] = DB::table('pesanan')
            ->join('detil_pesanan', 'detil_pesanan.ID_PESANAN', '=', 'pesanan.ID_PESANAN')
            ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
            ->selectRaw('ifnull(sum(detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU), 0) as subtotalsidedish')
            ->where('menu.JENIS_MENU', '=', 'Makanan Side Dish')
            ->whereNotNull('pesanan.ID_PEMBAYARAN')
            ->whereMonth('pesanan.TANGGAL_PESANAN', $i+1)
            ->whereYear('pesanan.TANGGAL_PESANAN', $tahun)
            ->first();
            
            $minuman[$i] = DB::table('pesanan')
            ->join('detil_pesanan', 'detil_pesanan.ID_PESANAN', '=', 'pesanan.ID_PESANAN')
            ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
            ->selectRaw('ifnull(sum(detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU), 0) as subtotalminuman')
            ->where('menu.JENIS_MENU', '=', 'Minuman')
            ->whereNotNull('pesanan.ID_PEMBAYARAN')
            ->whereMonth('pesanan.TANGGAL_PESANAN', $i+1)
            ->whereYear('pesanan.TANGGAL_PESANAN', $tahun)
            ->first();
            
            $pendapatan[$i] = $makanan[$i]->subtotalmakanan + $sidedish[$i]->subtotalsidedish + $minuman[$i]->subtotalminuman;
            $pengeluaranbln[$i] = array(
                "nomor" => $i + 1,
                "bulan" => Carbon::createFromFormat('!m', $i+1)->format('F'),
                "makanan" => $makanan[$i]->subtotalmakanan,
                "sidedish" => $sidedish[$i]->subtotalsidedish,
                "minuman" => $minuman[$i]->subtotalminuman,
                "total" => $pendapatan[$i]
            );
        }
        
        return response([
            'message' => 'Tampil Data Laporan Pendapatan Bulanan Berhasil',
            'data' => $pengeluaranbln,
        ], 200);
    }

    public function pendapatantahunan($tahun, $tahun1) {
        $awal = $tahun;
        $akhir = $tahun1;
        for($i = 0; $i <= $akhir - $awal; $i++) {
            // subtotal dari tabel pembayaran 
            $makanan[$i] = DB::table('pesanan')
            ->join('detil_pesanan', 'detil_pesanan.ID_PESANAN', '=', 'pesanan.ID_PESANAN')
            ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
            ->selectRaw('ifnull(sum(detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU), 0) as subtotalmakanan')
            ->where('menu.JENIS_MENU', '=', 'Makanan Utama')
            ->whereNotNull('pesanan.ID_PEMBAYARAN')
            ->whereYear('pesanan.TANGGAL_PESANAN', $awal + $i)
            ->first();

            $sidedish[$i] = DB::table('pesanan')
            ->join('detil_pesanan', 'detil_pesanan.ID_PESANAN', '=', 'pesanan.ID_PESANAN')
            ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
            ->selectRaw('ifnull(sum(detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU), 0) as subtotalsidedish')
            ->where('menu.JENIS_MENU', '=', 'Makanan Side Dish')
            ->whereNotNull('pesanan.ID_PEMBAYARAN')
            ->whereYear('pesanan.TANGGAL_PESANAN', $awal + $i)
            ->first();
            
            $minuman[$i] = DB::table('pesanan')
            ->join('detil_pesanan', 'detil_pesanan.ID_PESANAN', '=', 'pesanan.ID_PESANAN')
            ->join('menu', 'menu.ID_MENU', '=', 'detil_pesanan.ID_MENU')
            ->selectRaw('ifnull(sum(detil_pesanan.JUMLAH_PESANAN * menu.HARGA_MENU), 0) as subtotalminuman')
            ->where('menu.JENIS_MENU', '=', 'Minuman')
            ->whereNotNull('pesanan.ID_PEMBAYARAN')
            ->whereYear('pesanan.TANGGAL_PESANAN', $awal + $i)
            ->first();
            
            $pendapatan[$i] = $makanan[$i]->subtotalmakanan + $sidedish[$i]->subtotalsidedish + $minuman[$i]->subtotalminuman;
            $pengeluaranbln[$i] = array(
                "nomor" => $i + 1,
                "tahun" => $awal + $i,
                "makanan" => $makanan[$i]->subtotalmakanan,
                "sidedish" => $sidedish[$i]->subtotalsidedish,
                "minuman" => $minuman[$i]->subtotalminuman,
                "total" => $pendapatan[$i]
            );
        }
        
        return response([
            'message' => 'Tampil Data Laporan Pendapatan Tahunan Berhasil',
            'data' => $pengeluaranbln,
        ], 200);
    }

    public function pengeluaranbulanan($tahun) {        
        for($i = 0; $i < 12; $i++) {
            $makanan[$i] = DB::table('stok_masuk')
            ->join('bahan', 'bahan.ID_BAHAN', '=', 'stok_masuk.ID_BAHAN')
            ->join('menu', 'bahan.ID_BAHAN', '=', 'menu.ID_BAHAN')
            ->selectRaw('ifnull(sum(stok_masuk.HARGA_STOK_MASUK), 0) as subtotalmakanan')
            ->where('menu.JENIS_MENU', '=', 'Makanan Utama')
            ->whereMonth('stok_masuk.TANGGAL_STOK_MASUK', $i+1)
            ->whereYear('stok_masuk.TANGGAL_STOK_MASUK', $tahun)
            ->first();

            $sidedish[$i] = DB::table('stok_masuk')
            ->join('bahan', 'bahan.ID_BAHAN', '=', 'stok_masuk.ID_BAHAN')
            ->join('menu', 'bahan.ID_BAHAN', '=', 'menu.ID_BAHAN')
            ->selectRaw('ifnull(sum(stok_masuk.HARGA_STOK_MASUK), 0) as subtotalsidedish')
            ->where('menu.JENIS_MENU', '=', 'Makanan Side Dish')
            ->whereMonth('stok_masuk.TANGGAL_STOK_MASUK', $i+1)
            ->whereYear('stok_masuk.TANGGAL_STOK_MASUK', $tahun)
            ->first();
            
            $minuman[$i] =  DB::table('stok_masuk')
            ->join('bahan', 'bahan.ID_BAHAN', '=', 'stok_masuk.ID_BAHAN')
            ->join('menu', 'bahan.ID_BAHAN', '=', 'menu.ID_BAHAN')
            ->selectRaw('ifnull(sum(stok_masuk.HARGA_STOK_MASUK), 0) as subtotalminuman')
            ->where('menu.JENIS_MENU', '=', 'Minuman')
            ->whereMonth('stok_masuk.TANGGAL_STOK_MASUK', $i+1)
            ->whereYear('stok_masuk.TANGGAL_STOK_MASUK', $tahun)
            ->first();

            $pendapatan[$i] = $makanan[$i]->subtotalmakanan + $sidedish[$i]->subtotalsidedish + $minuman[$i]->subtotalminuman;
            
            $pengeluaranbln[$i] = array(
                "nomor" => $i+1,
                "bulan" => Carbon::createFromFormat('!m', $i+1)->format('F'),
                "makanan" => $makanan[$i]->subtotalmakanan,
                "sidedish" => $sidedish[$i]->subtotalsidedish,
                "minuman" => $minuman[$i]->subtotalminuman,
                "total" => $pendapatan[$i]
            );
        }

        return response([
            'message' => 'Tampil Data Laporan Pengeluaran Bulanan Berhasil',
            'data' => $pengeluaranbln,
        ], 200);
    }

    public function pengeluarantahunan($tahun, $tahun1) {
        $awal = $tahun;
        $akhir = $tahun1;
        for($i = 0; $i <= $akhir - $awal; $i++) {    
            $makanan[$i] = DB::table('stok_masuk')
            ->join('bahan', 'bahan.ID_BAHAN', '=', 'stok_masuk.ID_BAHAN')
            ->join('menu', 'bahan.ID_BAHAN', '=', 'menu.ID_BAHAN')
            ->selectRaw('ifnull(sum(stok_masuk.HARGA_STOK_MASUK), 0) as subtotalmakanan')
            ->where('menu.JENIS_MENU', '=', 'Makanan Utama')
            ->whereYear('stok_masuk.TANGGAL_STOK_MASUK', $awal + $i)
            ->first();

            $sidedish[$i] = DB::table('stok_masuk')
            ->join('bahan', 'bahan.ID_BAHAN', '=', 'stok_masuk.ID_BAHAN')
            ->join('menu', 'bahan.ID_BAHAN', '=', 'menu.ID_BAHAN')
            ->selectRaw('ifnull(sum(stok_masuk.HARGA_STOK_MASUK), 0) as subtotalsidedish')
            ->where('menu.JENIS_MENU', '=', 'Makanan Side Dish')
            ->whereYear('stok_masuk.TANGGAL_STOK_MASUK', $awal + $i)
            ->first();
            
            $minuman[$i] = DB::table('stok_masuk')
            ->join('bahan', 'bahan.ID_BAHAN', '=', 'stok_masuk.ID_BAHAN')
            ->join('menu', 'bahan.ID_BAHAN', '=', 'menu.ID_BAHAN')
            ->selectRaw('ifnull(sum(stok_masuk.HARGA_STOK_MASUK), 0) as subtotalminuman')
            ->where('menu.JENIS_MENU', '=', 'Minuman')
            ->whereYear('stok_masuk.TANGGAL_STOK_MASUK', $awal + $i)
            ->first();
            
            $pendapatan[$i] = $makanan[$i]->subtotalmakanan + $sidedish[$i]->subtotalsidedish + $minuman[$i]->subtotalminuman;
            $pengeluaranbln[$i] = array(
                "nomor" => $i + 1,
                "tahun" => $awal + $i,
                "makanan" => $makanan[$i]->subtotalmakanan,
                "sidedish" => $sidedish[$i]->subtotalsidedish,
                "minuman" => $minuman[$i]->subtotalminuman,
                "total" => $pendapatan[$i]
            );
        }
        
        return response([
            'message' => 'Tampil Data Laporan Pengeluaran Tahunan Berhasil',
            'data' => $pengeluaranbln,
        ], 200);
    }
}
