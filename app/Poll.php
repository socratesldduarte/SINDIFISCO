<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $table = 'polls';
    protected $fillable = [
        'name', 'start', 'end', 'active',
    ];
    protected $dates = ['created_at', 'updated_at', 'start', 'end'];

    private function questions() {
        return $this->hasMany(PollQuestion::class, 'poll_id');
    }

    private function uservotes() {
        return $this->hasMany(UserVote::class, 'poll_id');
    }

    private function documents() {
        return $this->hasMany(Document::class, 'poll_id');
    }
}
