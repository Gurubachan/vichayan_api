<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed user_id
 * @property false|mixed|string post_id
 * @property bool|mixed isActive
 */
class Likes extends Model
{
    use HasFactory;
    protected $table="tbl_likes";
}
