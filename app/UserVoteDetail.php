<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserVoteDetail extends Model
{
    protected $table = 'user_vote_details';
    protected $fillable = [
        'vote', 'poll_id', 'question', 'poll_question_option_id',
    ];
    protected $dates = ['created_at', 'updated_at'];

    private function poll() {
        return $this->belongsTo(Poll::class, 'poll_id');
    }

    private function user() {
        return $this->belongsTo(PollQuestionOption::class, 'poll_question_option_id');
    }

    private function uservote() {
        return $this->belongsTo(UserVote::class, 'vote');
    }
}
