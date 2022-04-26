<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempUser extends Model
{
    protected $table = 'temp_users';
    protected $fillable = [
        'user_id', 'is_processed', 'processed_at', 'document', 'name', 'birthday', 'code_area', 'phone', 'phone2',
        'phone2_desc', 'phone3', 'address_type', 'address', 'address_number', 'address_line2', 'pobox', 'district',
        'zipcode', 'city', 'province', 'email', 'email2', 'gender', 'situation', 'password_plain', 'password_bcrypt'
    ];

    protected $dates = ['processed_at', 'created_at', 'updated_at'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
