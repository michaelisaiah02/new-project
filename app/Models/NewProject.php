<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewProject extends Model
{
    /** @use HasFactory<\Database\Factories\NewProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'model',
        'part_number',
        'part_name',
        'part_type',
        'drawing_2d',
        'drawing_3d',
        'qty',
        'eee_number',
        'drawing_number',
        'drawing_revision_date',
        'material_on_drawing',
        'receive_date_sldg',
        'sldg_number',
        'masspro_target',
        'message',
        'minor_change',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'code');
    }
}
