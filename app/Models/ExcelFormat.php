<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelFormat extends Model
{
    protected $fillable = [
        'format_name',
        'format_code',
        'description',
        'expected_columns',
        'target_table',
        'is_active'
    ];

    protected $casts = [
        'expected_columns' => 'array',
        'is_active' => 'boolean'
    ];

    public function mappingConfigurations()
    {
        return $this->hasMany(MappingConfiguration::class);
    }

    public function uploadHistories()
    {
        return $this->hasMany(UploadHistory::class);
    }
}