<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = [
        'track_id',
        'track_name',
        'artist_id',
        'artist_name',
        'album_name',
        'genre',
        'release_date',
        'track_price',
        'collection_price',
        'country',
        'currency',
        'upload_history_id'
    ];

    protected $casts = [
        'release_date' => 'date',
        'track_price' => 'decimal:2',
        'collection_price' => 'decimal:2'
    ];

    public function uploadHistory()
    {
        return $this->belongsTo(UploadHistory::class);
    }
}