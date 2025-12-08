<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'department_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function stages()
    {
        return $this->hasMany(CustomerStage::class, 'customer_code', 'code');
    }

    public function documentTypes()
    {
        return DocumentType::whereHas('stages', function ($q) {
            $q->where('customer_code', $this->code);
        });
    }
}
