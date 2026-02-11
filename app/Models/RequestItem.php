<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    protected $fillable = ['request_id', 'item_id', 'item_name', 'quantity', 'notes', 'due_date'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
