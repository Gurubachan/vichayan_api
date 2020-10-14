<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class Authentication extends Controller
{

    public function index(){
        return response()->json(['response'=>false,'message'=>'Unauthorised access'],401);
    }
    public function login(Request $request){
        try {
            //return $request->only('contact','password');
            //$request->merge(json_decode($request->getContent(),true));
            $inputs=json_decode($request->getContent(),true);
            /*$validateddata= $request->validate([
                'password'=>'required',
                'contact'=>'required'
            ]);*/

//            $validator = Validator::make($request->only('contact','password'), [
            $validator = Validator::make($inputs, [
                'password'=>'required|string|min:6',
                'contact'=>'required|integer|digits:10'
            ]);
           // return dd($inputs);
            if ($validator->fails()) {
                return response()->json(['response'=>false,'message'=>$validator->errors()], 401);
            }
            if(!auth()->attempt($inputs)){
                return response()->json(['response'=>false,'message'=>'Invalid Credential']);
            }

            $accessToken =auth()->user()->createToken('authToken')->accessToken;

            return response()->json(['response'=>true, 'token'=>$accessToken]);
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    public function register(Request $request){
        try {
            //$request->merge(json_decode($request->getContent(),true));
            /*$validateData=$request->validate(
                [
                    'firstname'=>'required|string|max:70',
                    'lastname'=>'required|string|max:70',
                    'contact'=>'required|string|digits:10|unique:users',
                    'email'=>'required|email|unique:users',
                    'password'=>'required|string|confirmed',
                    'dob'=>'required|date'
                ]
            );*/
            $inputs=json_decode($request->getContent(),true);
            $validator=Validator::make($inputs,[
                'firstname'=>'required|string|max:70',
                'lastname'=>'required|string|max:70',
                'contact'=>'required|integer|digits:10|unique:users',
                'email'=>'required|email|unique:users',
                'password'=>'required|string|confirmed',
                'dob'=>'required|date'
            ]);
            if($validator->fails()){
                return response()->json(['response'=>false,'message'=>$validator->errors()], 401);
            }
            $inputs['password']=Hash::make( $inputs['password']);
            $inputs['dob']=date("Y-m-d", strtotime($inputs['dob']));
            $user=User::create($inputs);
            $accessToken=$user->createToken('authToken')->accessToken;
            return response()->json(['user'=>$user,'token'=>$accessToken],200);

        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }


}
