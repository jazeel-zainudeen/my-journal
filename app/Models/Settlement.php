<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'ticket_id', 'amount'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
