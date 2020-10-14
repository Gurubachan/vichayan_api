<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed postId
 * @property mixed content
 */
class PostContent extends Model
{
    use HasFactory;
    protected $table="tbl_post_content";

    protected $hidden=['created_at','updated_at'];
}
