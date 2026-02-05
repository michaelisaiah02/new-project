<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'password',
        'department_id',
        'whatsapp',
        'email',
        'approved',
        'checked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'approved' => 'boolean',
            'checked' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Ambil nomor WA Management (Dept 1)
    public static function getManagementNumbers()
    {
        return self::where('department_id', 1)->pluck('whatsapp')->implode(',');
    }

    // Ambil nomor Checker (User yang checked = true)
    public static function getCheckerNumbers()
    {
        return self::where('checked', true)->pluck('whatsapp')->implode(',');
    }

    // Ambil nomor Approver (User yang approved = true)
    public static function getApproverNumbers()
    {
        return self::where('approved', true)->pluck('whatsapp')->implode(',');
    }

    // Ambil nomor Engineering (Dept 3,4,5)
    // Logic: Lo harus nentuin project ini buat enginnering 2, 3, atau 9.
    // Asumsi: Gue kirim ke SEMUA engineering dulu ya, nanti lo filter lagi kalo ada logic spesifik.
    public static function getEngineeringNumbers()
    {
        return self::whereIn('department_id', [3, 4, 5])->pluck('whatsapp')->implode(',');
    }
}
