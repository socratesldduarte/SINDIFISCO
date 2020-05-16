<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'logs';
    protected $fillable = [
        'user_id', 'code', 'ip', 'description'
    ];
    protected $dates = ['created_at', 'updated_at'];

    private function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
