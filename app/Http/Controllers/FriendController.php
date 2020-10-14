<?php

namespace App\Http\Controllers;

use App\Models\FriendList;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    public function showUserList(){
        try {
            if(isset(auth('api')->user()->id)){
            $users = User::where('id', '!=', Auth()->user()->id)
                ->get();
            return response()->json($users);
        }else{
                return response()->json(['response'=>false,'message'=>'Unauthorised user access'],401);
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }
}
