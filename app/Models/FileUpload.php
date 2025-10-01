<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    protected $fillable = [
        'upload_history_id',
        'department_id',
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'target_table',
        'format_name',
        'rows_inserted',
        'upload_mode',
        'uploaded_at'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'rows_inserted' => 'integer'
    ];

    public function uploadHistory()
    {
        return $this->belongsTo(UploadHistory::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
