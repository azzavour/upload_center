<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


/**
 * App\Models\User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int|null $department_id
 * @property bool $is_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department|null $department
 */

class User extends Authenticatable
{
    
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function uploadHistories()
    {
        return $this->hasMany(UploadHistory::class, 'uploaded_by');
    }

    // Helper methods
    public function isUser()
    {
        return $this->role === 'user';
    }

public function hasDepartment(): bool
{
    return !is_null($this->department_id);
}

public function isAdmin(): bool
{
    return $this->role === 'admin';  // ← Ganti jadi 'role'
}
    
}