<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'document', 'able', 'name', 'email', 'mobile', 'administrator', 'committee', 'password', 'enabled_until',
    ];
    protected $dates = ['created_at', 'updated_at'];

    private function votes() {
        return $this->hasMany(UserVote::class, 'user_id');
    }

    private function logs() {
        return $this->hasMany(Log::class, 'user_id');
    }
}
