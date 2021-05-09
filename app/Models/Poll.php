<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $table = 'polls';
    protected $fillable = [
        'poll_type_id', 'code', 'name', 'start', 'end', 'active',
    ];
    protected $dates = ['created_at', 'updated_at', 'start', 'end'];

    public function pollquestions() {
        return $this->hasMany(PollQuestion::class, 'poll_id');
    }

    public function uservotes() {
        return $this->hasMany(UserVote::class, 'poll_id');
    }

    public function documents() {
        return $this->hasMany(Document::class, 'poll_id');
    }

    public function polltype() {
        return $this->belongsTo(PollType::class, 'poll_type_id');
    }

    public function users() {
        return $this->hasMany(User::class, 'poll_id');
    }
}
