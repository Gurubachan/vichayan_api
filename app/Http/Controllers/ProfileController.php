<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function setProfileImage(Request $request){
        try {
           // $inputs=json_decode($request->all(),true);
            if(isset(auth('api')->user()->id)) {
                request()->validate([
                    'file'  => 'required|mimes:jpg,jpeg,png|max:2048',
                ]);
                if ($files = $request->file('file')) {
                    //store file into document folder
                    $file = $request->file->store('public/documents');

                    //store your file into database
                    //$document = new Document();
                    //$document->title = $file;
                    //$document->save();
                    $myProfile=User::find(Auth::user()->id);
                    $myProfile->active_profile_image=asset("myApp/public".Storage::url($file));
                    $myProfile->save();
                    return Response()->json([
                        "response" => true,
                        "file" => asset("myApp/public".Storage::url($file)),
                        //"files" => Storage::url($file)
                    ]);
                }
                return Response()->json([
                    "response" => false,
                    "file" => ''
                ]);
            }else{
                return redirect('login');
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    public function createUserName(Request $request){
        try {
            $inputs=json_decode($request->getContent(),true);
            if(isset(auth('api')->user()->id)) {
                $validator=Validator::make($inputs,[
                    'username'=>'required|string|min:5|max:30|unique:users'
                ]);
                if($validator->fails()){
                    return response()->json(['response'=>false,'message'=>$validator->errors()]);
                }else{
                    $myData=User::where('id','=',Auth::user()->id)
                        ->whereNull('username')
                        ->get();
                    if($myData->count()==1){
                        $updatedData=User::where('id','=',Auth::user()->id)
                            ->whereNull('username')
                            ->update(['username'=>$inputs['username']]);
                        return response()->json(
                            [
                                'response'=>true,
                                'message'=>'Profile name set successfully',
                                'data'=>$updatedData
                            ]);
                    }else{
                        return response()->json(['response'=>false,'message'=>'User name already set.']);
                    }
                }
            }else{
                return redirect('login');
            }

        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }
}
