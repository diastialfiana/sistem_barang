<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'address', 'location_type'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
