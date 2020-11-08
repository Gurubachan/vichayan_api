<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property false|mixed|string user_id
 * @property false|mixed|string post_id
 */
class SavedPost extends Model
{
    use HasFactory;
    protected $table="tbl_save_post";

}
