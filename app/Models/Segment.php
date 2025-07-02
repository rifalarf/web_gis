<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $table = 'segments';

    protected $fillable = [
        'ruas_code','sta','jens_perm','kondisi','geometry',
    ];

    protected $casts = [
        // geometry → array; switch to Magellan’s MultiLineString later if you like
        'geometry' => 'array',
    ];

    public function ruas()
    {
        return $this->belongsTo(Ruas::class, 'ruas_code', 'code');
    }
}
