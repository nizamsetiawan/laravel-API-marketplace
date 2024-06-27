<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class AuthController extends Controller
{
   //api login
    public function login(Request $request){
        $validasi = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if($validasi->fails()){
            return $this->error($validasi->errors()->first());
        }

        $user = User::where('email', $request->email)->first();
        if($user){
            if(password_verify($request->password, $user->password)){
                return $this->success($user, 'Login Berhasil');
            } else {
                return $this->error('Password Salah');
            }
        } else {
            return $this->error('Email Tidak Terdaftar');
        }
    }

    //api register
    public function register(Request $request){
        $validasi = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|unique:users',
            'password' => 'required|min:6',
        ]);

        if($validasi->fails()){
            return $this->error($validasi->errors()->first());

        }

        $user = User::create(array_merge(
            $validasi->validated(),
            ['password' => bcrypt($request->password)]
        ));
        if ($user) {
            return $this->success($user, 'Selamat Datang ' . $user->name);
        } else {
            return $this->error('Gagal Register');
        }


    }

    //api update but password
    public function update(Request $request, $id){
       $user = User::where('id', $id) ->first();
       if ($user) {
        $user -> update($request->all());
        return $this-> success($user);
       }
       return $this->error("tidak ada user");
    }

    public function uploud(Request $request, $id){
        $user = User::where('id', $id) ->first();
        if ($user) {
            $fileName = "";
            if ($request -> image) {
                $image = $request -> image -> getClientOriginalName();
                $image = str_replace(' ', '', $image);
                $image = date('Hs').rand(1,999). "_". $image;
                $fileName = $image;
                $request->image->storeAs('public/user', $image);
            } else {
                return $this -> error("Image Wajib Diisi");
            }
         $user -> update(['image' => $fileName]);
         return $this-> success($user);
        }
        return $this->error("tidak ada user");
     }


 public function success($data, $message = "Success"){
        return response()->json([
            'code' => 200,
            'message' => $message,
            'data' => $data
        ]);
    }
    public function error($message){
        return response()->json([
            'code' => 400,
            'message' => $message
        ], 400);
    }
}
