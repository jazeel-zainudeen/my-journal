<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }

    public function settlements()
    {
        return $this->hasMany(Settlement::class);
    }
}
