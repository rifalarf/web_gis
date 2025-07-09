<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\GeojsonUploadRequest;
use App\Models\{Ruas, Segment};
use Clickbar\Magellan\IO\Parser\Geojson\GeoJsonParser;
use Clickbar\Magellan\Data\Geometries\MultiLineString;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GeojsonController extends Controller
{
    public function upload(GeojsonUploadRequest $request)
    {
        // 1) decode the file
        $data = json_decode(file_get_contents($request->file('file')->path()), true);

        // 2) group features by CODE
        $groups = collect($data['features'])
            ->groupBy(fn ($f) => $f['properties']['CODE']);

        DB::transaction(function () use ($groups, $request) {
            Cache::forget('segments_geojson');

            foreach ($groups as $code => $features) {

                $first = $features->first()['properties'];
                $ruasExists = Ruas::where('code', $code)->exists();
                $mode       = $request->mode;           // insert | update

                if ($mode === 'insert' && $ruasExists) {
                    continue;                           // skip existing
                }
                if ($mode === 'update' && ! $ruasExists) {
                    continue;                           // skip unknown
                }

                // 3) create ruas row when needed
                if (! $ruasExists) {
                    $ruas = Ruas::firstOrCreate(
                        ['code' => $code],
                        ['nm_ruas' => $first['Nm_Ruas'] ?? 'Tanpa Nama']
                    );
                    $ruas->fill([
                        'kon_baik'     => $first['Kon_Baik'],
                        'kon_sdg'      => $first['Kon_Sdg'],
                        'kon_rgn'      => $first['Kon_Rgn'],
                        'kon_rusak'    => $first['Kon_Rusak'],
                        'kon_mntp'     => $first['Kon_Mntp']    ?? ($first['Kon_Baik'] + $first['Kon_Sdg']),
                        'kon_t_mntp'   => $first['Kon_T_Mntp']  ?? ($first['Kon_Rgn']  + $first['Kon_Rusak']),
                        'panjang'      => $first['Panjang'],
                        'kecamatan'    => $first['Kecamatan'],
                    ])->save();


                }

                // 4) update mode wipes old segments
                if ($mode === 'update') {
                    Segment::where('ruas_code', $code)->delete();
                }

                // 5) insert all segments
                foreach ($features as $f) {
                    $p = $f['properties'];
                    $rawGeometry = json_encode($f['geometry']);        // just the geometry block!
                    $geometry    = app(GeoJsonParser::class)->parse($rawGeometry);
                    $rawKondisi = $p['Kondisi'] ?? null;
                    /** @var MultiLineString $geometry */

                    $kondisi = match (strtolower(trim($rawKondisi))) {
                        'rusak ringan' => 'rusak_ringan',
                        'rusak berat'  => 'rusak_berat',
                        default        => $rawKondisi,               // "baik", "sedang", or null
                    };

                    Segment::create([
                        'ruas_code' => $code,
                        'sta'       => $p['STA']       ?? null,
                        'jens_perm' => $p['Jens_Perm'] ?? null,
                        'kondisi'   => $kondisi,
                        'geometry'  => $geometry,
                    ]);
                }
            }
        });

        return back()->with('success', 'GeoJSON processed.');
    }

    public function destroy(string $code)
    {
        Ruas::where('code', $code)->delete();   // segments auto-cascade
        Cache::forget('segments_geojson');
        return back()->with('success', "Ruas $code deleted.");
    }
}
