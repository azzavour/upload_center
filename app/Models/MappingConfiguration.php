<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MappingConfiguration extends Model
{
    protected $fillable = [
        'excel_format_id',
        'mapping_index',
        'column_mapping',
        'transformation_rules'
    ];

    protected $casts = [
        'column_mapping' => 'array',
        'transformation_rules' => 'array'
    ];

    public function excelFormat()
    {
        return $this->belongsTo(ExcelFormat::class);
    }

    public function uploadHistories()
    {
        return $this->hasMany(UploadHistory::class);
    }
}