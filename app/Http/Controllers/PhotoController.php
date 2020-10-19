<?php

namespace App\Http\Controllers;

use App\Models\PostContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function upload(Request $request){
        try {
            if ($files = $request->file('file')) {
                $file = $request->file->store('public/'.base64_encode(Auth::user()->username).'/postContent');
                $content_url[]=asset("myApp/public".Storage::url($file));
                $data=array(
                    'response'=>true,
                    'message'=>'File upload successfully',
                    'data'=>$content_url
                );
                return response()->json($data);
            }else{
                $data=array('response'=>false,'message'=>'No file attached');
                return response()->json($data);
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }

    }
}
