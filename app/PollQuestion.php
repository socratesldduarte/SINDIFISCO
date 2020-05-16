<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PollQuestion extends Model
{
    protected $table = 'poll_questions';
    protected $fillable = [
        'poll_id', 'question', 'description', 'selection_number',
    ];
    protected $dates = ['created_at', 'updated_at'];

    private function poll() {
        return $this->belongsTo(Poll::class, 'poll_id');
    }

    private function pollquestionoptions() {
        return $this->hasMany(PollQuestionOption::class, 'poll_question_id');
    }
}
