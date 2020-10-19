<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed requested_id
 * @property int|mixed requested_status
 * @property int|mixed my_id
 */
class FriendRequest extends Model
{
    use HasFactory;
    protected $table="tbl_friend_request";

}
