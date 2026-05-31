<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['sender_id', 'receiver_id', 'room_id', 'content'])]
class Message extends Model
{
    //
}
