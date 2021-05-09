<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVote extends Model
{
    protected $table = 'user_votes';
    protected $fillable = [
        'poll_id', 'user_id', 'ip', 'vote',
    ];
    protected $dates = ['created_at', 'updated_at'];

    public function poll() {
        return $this->belongsTo(Poll::class, 'poll_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function uservotedetails() {
        return $this->hasMany(UserVoteDetail::class, 'vote');
    }
}
