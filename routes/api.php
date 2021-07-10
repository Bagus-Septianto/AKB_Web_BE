<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'Api\AuthController@login');

Route::group(['middleware' => 'auth:api'], function() {
    //karyawan
    Route::post('register', 'Api\AuthController@register'); //create
    Route::get('karyawan', 'Api\AuthController@index'); //read
    Route::get('karyawan/{id}', 'Api\AuthController@show'); //search
    Route::put('karyawan/{id}', 'Api\AuthController@update'); //update
    Route::put('karyawannonactive/{id}', 'Api\AuthController@nonactive'); //delete-ish
    //meja
    Route::get('meja', 'Api\MejaController@index');
    Route::get('meja/{id}', 'Api\MejaController@show');
    Route::post('meja', 'Api\MejaController@store');
    Route::put('meja/{id}', 'Api\MejaController@update');
    Route::delete('meja/{id}', 'Api\MejaController@destroy');
    //customer
    Route::get('customer', 'Api\CustomerController@index');
    Route::get('customer/{id}', 'Api\CustomerController@show');
    Route::post('customer', 'Api\CustomerController@store');
    Route::put('customer/{id}', 'Api\CustomerController@update');
    Route::delete('customer/{id}', 'Api\CustomerController@destroy');
    //pesanan
    Route::get('pesanan', 'Api\PesananController@index');
    Route::get('pesanancashier', 'Api\PesananController@indexcashier');
    Route::get('pesananchef', 'Api\PesananController@indexchef');
    Route::get('pesananwaiter', 'Api\PesananController@indexwaiter');
    Route::get('pesanan/{id}', 'Api\PesananController@show');
    Route::post('pesanan', 'Api\PesananController@store');
    Route::put('statuspesanan/{id}', 'Api\PesananController@updateDetil'); //kasir
    Route::put('pesananstatus/{id}', 'Api\PesananController@detilUpdate'); //untuk ngubah detil_pesanan.STATUS_PESANAN
    //menu
    Route::get('menu', 'Api\MenuController@index');
    Route::get('menu/{id}', 'Api\MenuController@show');
    Route::post('menu', 'Api\MenuController@store');
    Route::put('menu/{id}', 'Api\MenuController@update');
    Route::delete('menu/{id}', 'Api\MenuController@destroy');
    //jenisBahan
    Route::get('bahan', 'Api\JenisBahanController@index');
    Route::get('bahan/{id}', 'Api\JenisBahanController@show');
    Route::post('bahan', 'Api\JenisBahanController@store');
    Route::put('bahan/{id}', 'Api\JenisBahanController@update');
    Route::delete('bahan/{id}', 'Api\JenisBahanController@destroy');
    //reservasi
    Route::get('reservasi', 'Api\ReservasiController@index');
    Route::get('reservasi/{id}', 'Api\ReservasiController@show');
    Route::post('reservasi', 'Api\ReservasiController@store');
    Route::put('reservasi/{id}', 'Api\ReservasiController@update');
    Route::delete('reservasi/{id}', 'Api\ReservasiController@destroy');
    //stokkeluar
    Route::get('stokkeluar', 'Api\StokKeluarController@index');
    Route::get('stokkeluar/{id}', 'Api\StokKeluarController@show');
    Route::post('stokkeluar', 'Api\StokKeluarController@store');
    Route::put('stokkeluar/{id}', 'Api\StokKeluarController@update');
    Route::delete('stokkeluar/{id}', 'Api\StokKeluarController@destroy');
    //stokmasuk
    Route::get('stokmasuk', 'Api\StokMasukController@index');
    Route::get('stokmasuk/{id}', 'Api\StokMasukController@show');
    Route::post('stokmasuk', 'Api\StokMasukController@store');
    Route::put('stokmasuk/{id}', 'Api\StokMasukController@update');
    Route::delete('stokmasuk/{id}', 'Api\StokMasukController@destroy');
    //pembayaran
    Route::post('bayarcash', 'Api\PembayaranController@storeCash');
    Route::post('bayarcard', 'Api\PembayaranController@storeCard');

    //Laporan
    Route::get('stokbahancustom/{awal}/{akhir}', 'Api\LaporanController@stokbahancustom');
    Route::get('stokbahan/{tahun}/{bulan}/{bahan}', 'Api\LaporanController@stokbahan');

    Route::get('penjualanitem/{tahun}/{bulan}', 'Api\LaporanController@penjualanitem');
    Route::get('penjualanitemall/{tahun}', 'Api\LaporanController@penjualanitemall');

    Route::get('pendapatanbulanan/{tahun}', 'Api\LaporanController@pendapatanbulanan');
    Route::get('pendapatantahunan/{tahun}/{tahun1}', 'Api\LaporanController@pendapatantahunan');

    Route::get('pengeluaranbulanan/{tahun}', 'Api\LaporanController@pengeluaranbulanan');
    Route::get('pengeluarantahunan/{tahun}/{tahun1}', 'Api\LaporanController@pengeluarantahunan');

    //logout
    Route::post('logout','Api\AuthController@logoutApi');
});

//pemesanan gaperlu masuk middleware soalnya dari android tinggal post request saja
//tidak perlu login, qr code yang digenerate berisi data customer yang sudah ada
//qr code = masuk request (nama, dll.)

//menu
Route::get('menupublic', 'Api\MenuController@indexpublic');
Route::post('pesananpublic', 'Api\PesananController@storepublic');
Route::get('showpesananpublic/{id}', 'Api\PesananController@showpublic');
Route::put('datangpublic/{id}', 'Api\ReservasiController@updatepublic');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});