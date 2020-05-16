<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';
    protected $fillable = [
        'created_at', 'poll_id', 'type', 'hash', 'content'
    ];
    protected $dates = ['created_at', 'updated_at'];

    private function poll() {
        return $this->belongsTo(Poll::class, 'poll_id');
    }
}
