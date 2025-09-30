<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function excelFormats()
    {
        return $this->hasMany(ExcelFormat::class);
    }

    public function mappingConfigurations()
    {
        return $this->hasMany(MappingConfiguration::class);
    }

    public function uploadHistories()
    {
        return $this->hasMany(UploadHistory::class);
    }

    public function masterData()
    {
        return $this->hasMany(MasterData::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}