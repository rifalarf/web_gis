<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruas extends Model
{
    protected $table      = 'ruas';
    protected $primaryKey = 'code';   // use CODE as the natural key
    public    $incrementing = false;
    protected $keyType     = 'string';

    protected $fillable = ['code','nm_ruas'];

    public function segments()
    {
        return $this->hasMany(Segment::class, 'ruas_code', 'code');
    }
}
