<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComboItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'combo_id',
        'item_type',
        'item_name',
        'size',
        'quantity',
    ];

    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }
}
