<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterData extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'upload_history_id',
        'source_table',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function uploadHistory()
    {
        return $this->belongsTo(UploadHistory::class);
    }
}