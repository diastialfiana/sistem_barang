<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoleLog extends Model
{
    protected $fillable = ['user_id', 'old_role', 'new_role', 'changed_by', 'changed_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
