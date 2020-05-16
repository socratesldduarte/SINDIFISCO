<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PollQuestionOption extends Model
{
    protected $table = 'poll_question_options';
    protected $fillable = [
        'poll_question_id', 'order', 'option', 'description',
    ];
    protected $dates = ['created_at', 'updated_at'];

    private function pollquestion() {
        return $this->belongsTo(PollQuestion::class, 'poll_question_id');
    }

    private function uservotedetails() {
        return $this->hasMany(UserVoteDetail::class, 'poll_question_option_id');
    }
}
