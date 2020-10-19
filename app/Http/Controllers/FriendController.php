<?php

namespace App\Http\Controllers;

use App\Models\FriendList;
use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use const http\Client\Curl\AUTH_ANY;


class FriendController extends Controller
{
    public function showUserList(){
        try {
            if(isset(auth('api')->user()->id)){
            $users = User::whereNotIn('id',[Auth::user()->id])
                ->whereNotIn('id',FriendRequest::select('requested_id')->where('my_id','=',Auth::user()->id)->get()->toarray())
                ->whereNotIn('id',FriendList::select('friends_id')->where('my_id','=',Auth::user()->id)->get()->toarray())
                ->get();
            return response()->json($users);
        }else{
                return response()->json(['response'=>false,'message'=>'Unauthorised user access'],401);
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    public function myFriends(){
        try {
            if(isset(auth('api')->user()->id)){
                /*$friends=DB::table('tbl_friend_list')
                    ->join('users','tbl_friend_list.my_id','=','users.id')
                    ->select('users.*')
                    ->get();*/
                $friends=User::whereIn('id',FriendList::select('friends_id')
                    ->where('my_id',Auth::user()->id)->get()->toarray())
                    ->get();
                if($friends->count()>0){
                    return response()->json($friends);
                }else{
                    return response()->json(['message'=>'You have no friend till now']);
                }
            }else{
                return redirect('login');
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    public function friendRequest(Request $request){
        try {
            if(isset(auth('api')->user()->id)){
                $inputs=json_decode($request->getContent(),true);
                $validator=Validator::make($inputs,[
                    'friend_id'=>'required|integer',
                ]);
                if($validator->fails()){
                    return response()->json(['response'=>false,'message'=>$validator->errors()], 401);
                }else{
                    $friendRequest= new FriendRequest();
                    $friendRequest->my_id=Auth::user()->id;
                    $friendRequest->requested_id=$inputs['friend_id'];
                    $friendRequest->requested_status=0;
                    $friendRequest->save();

                    return response()->json(['response'=>true,'message'=>'Friend request sent.']);
                }
            }else{
                return redirect('login');
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    public function showFriendRequest(){
        try {
            if(isset(auth('api')->user()->id)){
               /* $myFriendRequest=DB::table('tbl_friend_request')
                    ->join('users','tbl_friend_request.requested_id','=','users.id')
                    ->select('users.*')
                    ->where('requested_status','=',0)
                    ->get();*/
                $myFriendRequest=User::where('id','!=',Auth::user()->id)
                ->whereIn('id',
                    FriendRequest::select('requested_id')
                        ->where('requested_status','=',0)
                        ->where('requested_id','=',Auth::user()->id)
                        ->get()->toarray()
                )->get();
                if($myFriendRequest->count()>0){
                    return response()->json(['response'=>true,'data'=>$myFriendRequest]);
                }else{
                    return response()->json(['response'=>false,'message'=>'No request found','data'=>[]]);
                }
            }else{
                return redirect('login');
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }
    public function acceptFriendRequest(Request $request){
        try {
            if(isset(auth('api')->user()->id)){
                $inputs=json_decode($request->getContent(),true);
                $validator=Validator::make($inputs,[
                    'friends_id'=>'required|integer|min:1',
                    'response'=>'required|integer|min:1|max:2'
                ]);
                if($validator->fails()){
                    return response()->json(['response'=>false,'message'=>$validator->errors()], 401);
                }else{
                    if($inputs['response']==1){
                        $friendList= new FriendList();
                        $friendList->my_id=Auth::user()->id;
                        $friendList->friends_id=$inputs['friends_id'];
                        $friendList->is_blocked=false;
                        $friendList->save();
                        FriendRequest::where('my_id','=',Auth::user()->id)
                            ->where('requested_id','=',$inputs['friends_id'])
                            ->update(['requested_status'=>1]);
                        return response()->json(['response'=>true,'message'=>'Connection request accepted']);
                    }else{
                        FriendRequest::where('my_id','=',Auth::user()->id)
                            ->where('requested_id','=',$inputs['friends_id'])
                            ->update(['requested_status'=>2]);
                        return response()->json(['response'=>true,'message'=>'Connection request rejected']);
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
