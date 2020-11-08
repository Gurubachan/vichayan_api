<?php

namespace App\Http\Controllers;

use App\Models\Comments;
use App\Models\FriendList;
use App\Models\Likes;
use App\Models\PostContent;
use App\Models\SavedPost;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $friendlist=FriendList::select('friends_id')
                ->where('my_id','=',Auth::user()->id)
                ->where('is_blocked',false)
                ->get()
                ->toarray();
            array_push($friendlist, Auth::user()->id);
            $posts=Post::
                whereIn('user_id',$friendlist)
                ->where('postStatus','=',0)
                ->where('isDeleted',false)
                ->orderByRaw('id DESC')
                ->simplePaginate();
            $response=$this->postCreator($posts);
            return response()->json($response);
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
                $validator=Validator::make($inputs,[
                    'content_url'=>'required|URL'
                ]);
                if($validator->fails()){
                    return response()->json(['response'=>false,'message'=>$validator->errors()], 401);
                }
                $postContent= new PostContent();
                $postContent->postId=$post->id;
                $postContent->content=$inputs['content_url'];
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
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            if (isset($id)){
                $comments=$this->getComments(base64_decode($id));
                if(count($comments)>0){
                    return response()->json(['response'=>true,
                        'message'=>count($comments).' comment fetched',
                        'data'=>$comments
                        ]);
                }else{
                    return response()->json(['response'=>false,
                        'message'=>'No comment found',
                    ]);
                }
            }else{
                return response()->json(['response'=>false,'message'=>'Invalid post id.']);
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return void
     */
    public function update(Request $request, string $id)
    {
        try {
            $inputs=json_decode($request->getContent(),true);
            $id=base64_decode($id);
            $validator=Validator::make($inputs,[
                'message'=>'required|string|min:1',
                'isPublished'=>'required|integer|min:0|max:1',
                'privacy'=>'required|integer|min:0|max:4',
                'postStatus'=>'required|integer|min:0|max:1'
            ]);

            if($validator->fails()){
                return response()->json(['response'=>false,'message'=>$validator->errors()], 401);
            }
            $checkPost=Post::where('id',$id)
                ->where('user_id',Auth::user()->id)
                ->take(1)
                ->get();
            if($checkPost->count()==1){
                $post=Post::find($id);
                $post->message=strip_tags($inputs['message']);
                $post->postStatus=$inputs['postStatus'];
                $post->privacy=$inputs['privacy'];
                $post->isEdited=true;
                $post->updated_at=date('Y-m-d H:s:i');
                $post->save();
            }
            return response()->json(['response'=>true,'message'=>'Post updated.','data'=>$post],200);
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return void
     */
    public function destroy(string $id)
    {
        try {
            if(isset($id) && isset(auth('api')->user()->id)){
                $id=base64_decode($id);
                $post=Post::find($id);
                $post->isDeleted=true;
                $post->deleted_at=date('Y-m-d H:s:i');
                $post->save();
                return response()->json(['response'=>true,'message'=>'Post deleted']);
            }else{
                return response()->json(['response'=>false,'message'=>'Invalid post id']);
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    public function comment(Request $request){
        try {
            $inputs=json_decode($request->getContent(),true);
            if(isset(auth('api')->user()->id)) {
                $validator = Validator::make($inputs, [
                    'post_id' => 'required|string',
                    'comment' => 'required|string'
                ]);
                if ($validator->fails()) {
                    return response()->json(['response' => false, 'message' => $validator->errors()], 401);
                }
                $newComment = new Comments();
                $newComment->user_id = Auth::user()->id;
                $newComment->post_id = base64_decode($inputs['post_id']);
                $newComment->comment = $inputs['comment'];
                $newComment->save();

                return response()->json(['response' => true,
                    'message' => "Comment saved successfully",
                    'data' => $newComment
                ]);
            }else{
                return redirect('login');
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    public function like(Request $request){
        try {
            $inputs=json_decode($request->getContent(),true);
            if(isset(auth('api')->user()->id)){
                $validator=Validator::make($inputs,[
                    'post_id'=>'required|string',
                    'like'=>'required|boolean'
                ]);
                if($validator->fails()){
                    return response()->json(['response' => false, 'message' => $validator->errors()], 401);
                }else{
                    $likes=Likes::where('post_id',base64_decode($inputs['post_id']))
                        ->where('user_id',Auth::user()->id)
                        ->get();
                    if($likes->count()==1){
                        Likes::where('user_id', Auth::user()->id)
                            ->where('post_id', base64_decode($inputs['post_id']))
                            ->update(['isActive'=>$inputs['like']]);
                        if($inputs['like']){
                            $post=Post::find(base64_decode($inputs['post_id']));
                            $post->likeCount=$post->likeCount+1;
                            $post->save();
                            return response()->json([
                                'response'=>true,
                                'data'=>$post
                                ]);
                        }else{
                            $post=Post::find(base64_decode($inputs['post_id']));
                            $post->likeCount=$post->likeCount-1;
                            $post->save();
                            return response()->json([
                                'response'=>true,
                                'data'=>$post
                            ]);
                        }

                    }
                    if($likes->count()==0){
                        $likes=new Likes();
                        $likes->user_id = Auth::user()->id;
                        $likes->post_id = base64_decode($inputs['post_id']);
                        $likes->isActive=true;
                        $likes->save();
                        $post=Post::find(base64_decode($inputs['post_id']));
                        $post->likeCount=$post->likeCount+1;
                        $post->save();
                        return response()->json([
                            'response'=>true,
                            'data'=>$post
                        ]);
                    }
                }
            }else{
                return redirect('login');
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }

    public function likeDetails(Request $request){
        try {
            $inputs=json_decode($request->getContent(),true);
            if(isset(auth('api')->user()->id)){
                $validator=Validator::make($inputs,[
                    'post_id'=>'required|string'
                ]);
                if($validator->fails()){
                    return response()->json(['response' => false, 'message' => $validator->errors()], 401);
                }
               $users = User::whereIn('id',Likes::select('user_id')
                   ->where('post_id',base64_decode($inputs['post_id']))
                   ->where('isActive',true)
                   ->get()
                   ->toarray())
                   ->get()
                   ;
               if(count($users)>0){
                   return response()->json(['response'=>true,'data'=>$users]);
               }else{
                   return response()->json(['response'=>false,'message'=>'NO one like this post']);
               }
            }else{
                return redirect('login');
            }
        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }
    public function SavePost(Request $request){
        try {
            $inputs=json_decode($request->getContent(),true);
            if(isset(auth('api')->user()->id)){
                $validator=Validator::make($inputs,[
                    'post_id'=>'required|string'
                ]);
                if($validator->fails()){
                    return response()->json(['response' => false, 'message' => $validator->errors()], 401);
                }
                ;
                $savePost= new SavedPost();
                $savePost->post_id=base64_decode($inputs['post_id']);
                $savePost->user_id=Auth::user()->id;
                $savePost->save();
                return response()->json(['response'=>true,'message'=>'Post Saved Successfully'],200);
            }else{
                return redirect('login');
            }

        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }
    public function getSavedPost(){
        try {
            if(isset(auth('api')->user()->id)){

                $savedPost=SavedPost::select('post_id')->where('user_id',Auth::user()->id)->get()->toArray();
                //return response()->json($savedPost);
               if(count($savedPost)>0){
                    $posts=Post::
                    whereIn('id',$savedPost)
                        ->where('postStatus','=',0)
                        ->where('isDeleted',false)
                        ->orderByRaw('id DESC')
                        ->simplePaginate();
                    $response=$this->postCreator($posts);
                    return response()->json($response);
                }else{
                    return response()->json(['response'=>false,'message'=>'Not getting any data']);
                }


            }else{
                return redirect('login');
            }

        }catch (\Exception $exception){
            return response()->json(['response'=>false,'message'=>$exception->getMessage()]);
        }
    }
    public function getComments(int $id){
        return $comment=DB::table('tbl_comments')
            ->join('users','tbl_comments.user_id','=','users.id')
            ->select('tbl_comments.*',
                'users.firstname', 'users.lastname',
                'users.active_profile_image as profilepic')
            ->where('post_id','=',$id)
            ->get();

    }

    public function postCreator($posts){
        foreach ($posts as $p){
            if($p->isContaintAttached){
                $postContent=PostContent::where('postId','=',$p->id)->get();
                //$comment=Comments::where('post_id','=',$p->id)->get();
            }else{
                $postContent=null;
            }
            $amILike=Likes::
            select('isActive')
                ->where('user_id',Auth::user()->id)
                ->where('post_id',$p->id)
                ->get();
            $this->data[]=array(
                'postId'=>base64_encode($p->id),
                'message'=>$p->message,
                'userId'=>base64_encode($p->user_id),
                'username'=>$p->users->username,
                'name'=>$p->users->firstname.' '.$p->users->lastname,
                'shortName'=>strtoupper($p->users->firstname[0].''.$p->users->lastname[0]),
                'profilePic'=>$p->users->active_profile_image,
                'likeCount'=>$p->likeCount,
                'isLiked'=>$amILike,
                'commentCount'=>$p->commentCount,
                'shareCount'=>$p->shareCount,
                'privacy'=>config('constants.privacy')[$p->privacy],
                'isContentAttached'=>$p->isContaintAttached,
                'created_at'=>$p->created_at,
                'postContent'=>$postContent,
                'comment'=>$this->getComments($p->id)
            );
        }
        if($posts->count()>0){
            $links=$posts->toArray();
            unset($links['data']);
            return ['response'=>true,
                'message'=>$posts->count().' post available till now',
                'data'=>$this->data,'nextApiCall'=>$links];
        }else{
            return ['response'=>false,
                'message'=>'No post found'];
        }
    }
}
