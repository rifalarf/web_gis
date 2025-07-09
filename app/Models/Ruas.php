<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruas extends Model
{
    protected $table      = 'ruas';
    protected $primaryKey = 'code';   // use CODE as the natural key
    public    $incrementing = false;
    protected $keyType     = 'string';

    protected $fillable = ['code','nm_ruas',
    'kon_baik','kon_sdg','kon_rgn','kon_rusak',
    'kon_mntp','kon_t_mntp','panjang','kecamatan',];

    public function segments()
    {
        return $this->hasMany(Segment::class, 'ruas_code', 'code');
    }

    public function toGeoJson(): array
    {
        // make sure we have segments loaded
        $this->loadMissing('segments');   // segments() relation must exist

        $features = $this->segments->map(function ($s) {
            return [
                'type'       => 'Feature',
                'properties' => [
                    'code'      => $s->ruas_code,
                    'nm_ruas'   => $this->nm_ruas,
                    'sta'       => $s->sta,
                    'jens_perm' => $s->jens_perm,
                    'kondisi'   => $s->kondisi,
                ],
                'geometry'   => $s->geometry->jsonSerialize(),
            ];
        });

        return [
            'type'     => 'FeatureCollection',
            'features' => $features->all(),
        ];
    }
}
