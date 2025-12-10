<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $primaryKey = 'part_number';

    public $incrementing = false;

    protected $keyType = 'string';

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
        'minor_change',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'code');
    }
}
