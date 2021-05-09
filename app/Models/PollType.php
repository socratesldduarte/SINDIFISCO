<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollType extends Model
{
    use HasFactory;
    protected $table = 'poll_types';
    protected $fillable = [
        'name',
    ];
    protected $dates = ['created_at', 'updated_at'];

    public function polls()
    {
        return $this->hasMany(Poll::class, 'poll_type_id');
    }
}

