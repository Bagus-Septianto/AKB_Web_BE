<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\User; //import model user TODO Ganti ini kayanya
use App\Role; //import model Role
use Validator; //import library untuk validasi
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request){
    	$registrationData = $request->all();
    	$validate = Validator::make($registrationData,[
    		'NAMA_KARYAWAN' => 'required|max:50|regex:/^[a-zA-Z\s]*$/',
    		'NO_TELP_KARYAWAN' => 'required|max:15|regex:/^(08)[0-9]{8,11}$/',
    		'JENIS_KELAMIN_KARYAWAN'=>'required|max:10',
            'TANGGAL_LAHIR_KARYAWAN'=>'required|date',
            'EMAIL_KARYAWAN'=>'required|email:rfc,dns',
            'password'=>'required',
            'TANGGAL_MASUK_KARYAWAN'=>'required|date',
			'NAMA_ROLE' => 'required',
    	]); //membuat rule validasi input

    	if($validate->fails())
    		return response(['message' => $validate->errors()],400); //return error invalid input

		//$role = Role::find($registrationData['ROLE']); //mencari data role berdasarkan id
		$role = Role::where('NAMA_ROLE', $registrationData['NAMA_ROLE'])->first(); //mencari data role berdasarkan id
		if(is_null($role)){
            return response([
                'message' => 'Role Not Found',
                'data' => null
            ],404);
        } // return msg saat role tidak ketemu

		$registrationData['ID_ROLE'] = $role['ID_ROLE']; //set id role untuk masuk tabel

    	$registrationData['password'] = bcrypt($request->password); //enkripsi password
		//return response(['message' =>$registrationData['PASSWORD'], 'data'=>$request->PASSWORD],400); //TESTING
    	$user = User::create($registrationData); //membuat user baru
    	return response([
    		'message'=>'Register Success',
    		'user' => $user,
    	],200); //return data user dalam bentuk json
    }

    public function login(Request $request){
    	$loginData = $request->all();
    	$validate = Validator::make($loginData,[
    		'EMAIL_KARYAWAN' => 'required',
    		'password'=> 'required'
    	]); //membuat rule validasi input

    	if($validate->fails())
			return response(['message' => $validate->errors()],400); //return error invalid input

    	if(!Auth::attempt($loginData))
			return response(['message'=> 'Invalid Credentials'],401); //return error gagal login
			
    	$user = Auth::user();
    	$token = $user->createToken('Authentication Token')->accessToken;//generate token
		//cari role
		$idRole = Role::find($user['ID_ROLE']);
		$role = $idRole['NAMA_ROLE'];

		//checking if karyawan is nonactive
		if(!is_null($user['TANGGAL_KELUAR_KARYAWAN']))
			return response(['message' => 'Gagal Login, Karyawan sudah tidak aktif'],400);

    	return response([
    		'message' => 'Authenticated',
    		'user' => $user,
			'role' => $role,
    		'token_type' => 'Bearer',
    		'access_token' => $token
		],200); //return data user dan token dalam bentuk json
    }
	
    public function logoutApi()
    {
        if (Auth::check()) {
            Auth::user()->AauthAccessToken()->delete();
			return response([
				'message' => 'Logged Out',
			]);
        }
    }

	//READ karyawan
	public function index() {
        //$karyawans = User::all(); //mengambil semua data karyawan
        $karyawans = DB::table('karyawan')
                    ->select('karyawan.id', 'karyawan.NAMA_KARYAWAN', 'karyawan.NO_TELP_KARYAWAN', 
                    'karyawan.JENIS_KELAMIN_KARYAWAN', 'karyawan.TANGGAL_LAHIR_KARYAWAN', 
                    'karyawan.EMAIL_KARYAWAN', 'karyawan.TANGGAL_MASUK_KARYAWAN', 
                    'karyawan.TANGGAL_KELUAR_KARYAWAN', 'role.NAMA_ROLE')
                    ->join('role', 'karyawan.ID_ROLE', 'role.ID_ROLE')
                    ->get();
        
        if(count($karyawans) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $karyawans
            ],200);
        } //return data semua karyawan dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ],404); //return message data karyawan kosong
    }

	//Update karyawan
	public function update(Request $request, $id) {
        $karyawan = User::find($id); //mencari data karyawan berdasarkan id
        
        if(is_null($karyawan)){
            return response([
                'message' => 'Karyawan Not Found',
                'data' => null
            ],404);
        } // return msg saat karyawan tidak ketemu

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
    		'NAMA_KARYAWAN' => 'required|max:50',
    		'NO_TELP_KARYAWAN' => 'required|max:15',
    		'JENIS_KELAMIN_KARYAWAN'=>'required|max:10',
            'TANGGAL_LAHIR_KARYAWAN'=>'required|date',
            'EMAIL_KARYAWAN'=>'required|email:rfc,dns',
            'TANGGAL_MASUK_KARYAWAN'=>'required|date',
        ]); // membuat validasi input

        if($validate->fails()) //return error invalid input
            return response(['message' => $validate->errors()],400);

		//role thingy convert dari NAMA_ROLE tapi masuk ke ID_ROLE jadi ID_ROLE
		$role = Role::where('NAMA_ROLE', $updateData['NAMA_ROLE'])->first(); //mencari data role berdasarkan id
		if(is_null($role)){
			return response([
				'message' => 'Role Not Found',
				'data' => null
			],404);
		} // return msg saat role tidak ketemu
		$karyawan->ID_ROLE = $role['ID_ROLE']; //set id role untuk masuk tabel
        
        $karyawan->NAMA_KARYAWAN = $updateData['NAMA_KARYAWAN'];
        $karyawan->NO_TELP_KARYAWAN = $updateData['NO_TELP_KARYAWAN'];
        $karyawan->JENIS_KELAMIN_KARYAWAN = $updateData['JENIS_KELAMIN_KARYAWAN'];
        $karyawan->TANGGAL_LAHIR_KARYAWAN = $updateData['TANGGAL_LAHIR_KARYAWAN'];
        $karyawan->EMAIL_KARYAWAN = $updateData['EMAIL_KARYAWAN'];
        $karyawan->TANGGAL_MASUK_KARYAWAN = $updateData['TANGGAL_MASUK_KARYAWAN'];

        if($karyawan->save()){
            return response([
                'message' => 'Update Karyawan Success',
                'data' => $karyawan
            ],200);
        } //return data karyawan yang telah diedit dalam bentuk json

        return response([
            'message' => 'Update Karyawan Failed',
            'data' => null
        ],400); //return saat gagal update data karyawan
    }

	//NONAKTIF KARYAWAN
	public function nonactive(Request $request, $id) {
        $karyawan = User::find($id); //mencari data karyawan berdasarkan id
        
        if(is_null($karyawan)){
            return response([
                'message' => 'Karyawan Not Found',
                'data' => null
            ],404);
        } // return msg saat karyawan tidak ketemu
        
		$karyawan->TANGGAL_KELUAR_KARYAWAN = Carbon::now()->toDateString();

        if($karyawan->save()){
            return response([
                'message' => 'Nonaktif Karyawan Success',
                'data' => $karyawan
            ],200);
        } //return data karyawan yang telah diedit dalam bentuk json

        return response([
            'message' => 'Nonaktif Karyawan Failed',
            'data' => null
        ],400); //return saat gagal update data karyawan

	}

	//Search Karyawan
	public function show($id) {
        $karyawan = User::find($id); //mencari data karyawan berdasarkan id

        if(!is_null($karyawan)){
            return response([
                'message' => 'Retrieve Karyawan Success',
                'data' => $karyawan
            ],200);
        } //return data karyawan yang ditemukan dalam bentuk json

        return response([
            'message' => 'Karyawan Tidak Ditemukan',
            'data' => null
        ],404); //return msg ketika Karyawan tidak ditemukan
    }
}
