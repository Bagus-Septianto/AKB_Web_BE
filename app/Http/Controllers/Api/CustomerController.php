<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use App\Customer;

class CustomerController extends Controller
{
    //method untuk menampilkan semua data Customer (Read)
    public function index() {
        $customers = Customer::all(); //mengambil semua data customer

        if(count($customers) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $customers
            ],200);
        } //return data semua customer dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); //return message data customer kosong
    }

    //method untuk menampilkan 1 data customer (search)
    public function show($id) {
        $customer = Customer::find($id); //mencari data customer berdasarkan id

        if(!is_null($customer)){
            return response([
                'message' => 'Retrieve Customer Success',
                'data' => $customer
            ],200);
        } //return data customer yang ditemukan dalam bentuk json

        return response([
            'message' => 'Customer Tidak Ditemukan',
            'data' => null
        ],404); //return msg ketika customer tidak ditemukan
    }

    //method untuk menambahkan 1 data customer baru (create)
    public function store(Request $request) {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'NAMA_CUSTOMER' => 'required|max:50|regex:/^[a-zA-Z\s]*$/', //isi validator customer
            'JENIS_KELAMIN_CUSTOMER' => 'required|max:10',
            'NO_TELP_CUSTOMER' => 'required|max:15|regex:/^(08)[0-9]{8,11}$/',
            'EMAIL_CUSTOMER' => 'required|max:50|email:rfc,dns',
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()],400); //return error invalid input

        $customer = Customer::create($storeData); //menambahkan data customer baru
        return response([
            'message' => 'Berhasil Menambahkan Customer',
            'data' => $customer
        ],200); //return data customer baru dalam bentuk json
    }

    //method untuk menghapus 1 data customer (delete)
    public function destroy($id) {
        $customer = Customer::find($id); //mencari data customer berdasarkan id
        
        if(is_null($customer)){
            return response([
                'message' => 'Customer Not Found',
                'data' => null
            ],404);
        } // return msg saat customer tidak ketemu

        if($customer->delete()){
            return response([
                'message' => 'Data customer berhasil dihapus',
                'data' => $customer,
            ],200);
        } //return msg saat delete berhasil

        return response([
            'message' => 'Delete Customer Failed',
            'data' => null
        ],400); // return message saat data gagal dihapus
    }

    //method untuk mengubah 1 data customer (update)
    public function update(Request $request, $id) {
        $customer = Customer::find($id); //mencari data customer berdasarkan id
        
        if(is_null($customer)){
            return response([
                'message' => 'Customer Not Found',
                'data' => null
            ],404);
        } // return msg saat customer tidak ketemu

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'NAMA_CUSTOMER' => 'required|max:50', //isi validator customer
            'JENIS_KELAMIN_CUSTOMER' => 'required|max:10',
            'NO_TELP_CUSTOMER' => 'required|max:15',
            'EMAIL_CUSTOMER' => 'required|max:50|email:rfc,dns',
        ]); // membuat validasi input

        if($validate->fails()) //return error invalid input
            return response(['message' => $validate->errors()],400);
        
        $customer->NAMA_CUSTOMER = $updateData['NAMA_CUSTOMER'];
        $customer->JENIS_KELAMIN_CUSTOMER = $updateData['JENIS_KELAMIN_CUSTOMER'];
        $customer->NO_TELP_CUSTOMER = $updateData['NO_TELP_CUSTOMER'];
        $customer->EMAIL_CUSTOMER = $updateData['EMAIL_CUSTOMER'];

        if($customer->save()){
            return response([
                'message' => 'Update Customer Success',
                'data' => $customer
            ],200);
        } //return data customer yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update Customer Failed',
            'data' => null
        ],400); //return saat gagal update data customer
    }
}
