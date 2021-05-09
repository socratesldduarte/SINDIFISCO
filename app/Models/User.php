<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'poll_id', 'document', 'able', 'name', 'email', 'mobile', 'administrator', 'committee', 'can_be_voted', 'password', 'enabled_until',
    ];
    protected $dates = ['created_at', 'updated_at', 'enabled_until'];

    public function votes() {
        return $this->hasMany(UserVote::class, 'user_id');
    }

    public function logs() {
        return $this->hasMany(Log::class, 'user_id');
    }

    public function poll() {
        return $this->belongsTo(Poll::class, 'poll_id');
    }
}
