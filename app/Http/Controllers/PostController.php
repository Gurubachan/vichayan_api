<?php

namespace App\Http\Controllers;

use App\Models\PostContent;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $data=array();
    public function index()
    {
        try {
            $posts=Post::where('user_id','=',Auth::user()->id)
                ->where('postStatus','=',0)
                ->orderByRaw('created_at DESC')
                ->offset(0)
                ->limit(10)
                ->get();
            foreach ($posts as $p){
                if($p->isContaintAttched){
                    $postContent=Post::find($p->id)->postContent()->get();
                }else{
                    $postContent="";
                }

               $this->data[]=array(
                    'postId'=>base64_encode($p->id),
                    'message'=>$p->message,
                    'userId'=>base64_encode($p->user_id),
                    'username'=>$p->users->username,
                    'name'=>$p->users->firstname.' '.$p->users->lastname,
                    'shortName'=>strtoupper($p->users->firstname[0].''.$p->users->lastname[0]),
                    'profilePic'=>$p->users->active_profile_image,
                    'likeCount'=>$p->likeCount,
                    'commentCount'=>$p->commentCount,
                    'shareCount'=>$p->shareCount,
                    'privacy'=>config('constants.privacy')[$p->privacy],
                    'isContentAttached'=>$p->isContaintAttached,
                    'created_at'=>$p->created_at,
                    'postContent'=>$postContent
                );
            }
            if($posts->count()>0){
                return response()->json(['response'=>true,
                    'message'=>$posts->count().' post available till now',
                    'data'=>$this->data]);
            }else{
                return response()->json(['response'=>false,'message'=>'No post found'],200);
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
             //return $request->input();

            $inputs=json_decode($request->getContent(),true);
           // return $inputs;
           /* $validated=$request->validate([
                'message'=>'required|string|min:1',
                'containtAttached'=>'required|integer|min:0|max:1',
                'isPublished'=>'required|integer|min:0|max:1',
                'privacy'=>'required|integer|min:0|max:4',
                'postStatus'=>'required|integer|min:0|max:1'
            ]);*/
            $validator=Validator::make($inputs,[
                'message'=>'required|string|min:1',
                'contentAttached'=>'required|integer|min:0|max:1',
                'isPublished'=>'required|integer|min:0|max:1',
                'privacy'=>'required|integer|min:0|max:4',
                'postStatus'=>'required|integer|min:0|max:1'
            ]);

            if($validator->fails()){
                return response()->json(['response'=>false,'message'=>$validator->errors()], 401);
            }
            $post= new Post();
            $post->message=strip_tags($inputs['message']);
            $post->isContaintAttached=$inputs['contentAttached'];
            $post->postStatus=$inputs['postStatus'];
            $post->privacy=$inputs['privacy'];
            $post->user_id=Auth::user()->id;
            $post->save();

            if($inputs['contentAttached']){
                $file = $request->file->store('public/'.base64_encode(Auth::user()->username).'/postContent');

                $postContent= new PostContent();
                $postContent->postId=$post->id;
                $postContent->content=$file;
                $postContent->save();


            }

            return response()->json(['response'=>true,'message'=>'Post live now','data'=>$post],200);
        }catch (Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return void
     */
    public function update(Request $request, int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return void
     */
    public function destroy(int $id)
    {
        //
    }
}
