<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * 1. Helper getPIC (Engineering)
     * Kriteria: Dept sesuai parameter, TIDAK BISA check, TIDAK BISA approve.
     */
    public static function getPIC(int $departmentId)
    {
        return self::where('department_id', $departmentId)
            ->where('checked', false)
            ->where('approved', false)
            ->get();
    }

    /**
     * 2. Helper getLeader (Engineering)
     * Kriteria: Dept sesuai parameter, BISA check, tapi (biasanya) belum level approve.
     */
    public static function getLeader(int $departmentId)
    {
        return self::where('department_id', $departmentId)
            ->where('checked', true)
            ->where('approved', false) // Asumsi: Leader cuma check, gak approve final
            ->get();
    }

    /**
     * 3. Helper getSupervisor (Engineering)
     * Kriteria: Dept sesuai parameter, BISA approve (Level tertinggi di dept).
     */
    public static function getSupervisor(int $departmentId)
    {
        return self::where('department_id', $departmentId)
            ->where('approved', true)
            ->get();
    }

    /**
     * 4. Helper getManagement
     * Kriteria: Cari user yang nama department-nya 'Management'.
     */
    public static function getManagement()
    {
        return self::whereHas('department', function (Builder $query) {
            $query->where('name', 'Management');
        })->get();
    }
}
