<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'code', 'user_id', 'branch_id', 'request_date', 
        'status', 'rejection_reason'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(RequestItem::class);
    }

    public function approvals()
    {
        return $this->hasMany(RequestApproval::class);
    }
}
