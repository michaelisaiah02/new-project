<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'department', 'id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function type()
    {
        $name = strtolower($this->name);

        return match (true) {
            str_contains($name, 'marketing') => 'marketing',
            str_contains($name, 'management') => 'management',
            str_contains($name, 'engineering') => 'engineering',
            default => 'other',
        };
    }
}
