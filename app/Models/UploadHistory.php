<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadHistory extends Model
{
    protected $fillable = [
        'excel_format_id',
        'mapping_configuration_id',
        'original_filename',
        'stored_filename',
        'total_rows',
        'success_rows',
        'failed_rows',
        'error_details',
        'status',
        'uploaded_at',
        'uploaded_by'
    ];

    protected $casts = [
        'error_details' => 'array',
        'uploaded_at' => 'datetime'
    ];

    public function excelFormat()
    {
        return $this->belongsTo(ExcelFormat::class);
    }

    public function mappingConfiguration()
    {
        return $this->belongsTo(MappingConfiguration::class);
    }
}
