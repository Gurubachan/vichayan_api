<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed my_id
 * @property mixed friends_id
 * @property false|mixed is_blocked
 */
class FriendList extends Model
{
    use HasFactory;
    protected $table="tbl_friend_list";
}
