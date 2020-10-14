<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use phpseclib\Math\BigInteger;

/**
 * @property mixed|string message
 * @property mixed|boolean isContaintAttached
 * @property mixed|boolean postStatus
 * @property mixed|boolean privacy
 * @property mixed|BigInteger user_id
 * @property mixed|BigInteger id
 */
class Post extends Model
{
    use HasFactory;
    protected $table="posts";

    public function users(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function postContent(){
        return $this->hasMany(PostContent::class,'postId','id');
    }
}
