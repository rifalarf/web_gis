<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\GeojsonUploadRequest;
use App\Models\{Ruas, Segment};
use Clickbar\Magellan\IO\Parser\Geojson\GeoJsonParser;
use Clickbar\Magellan\Data\Geometries\MultiLineString;
use Illuminate\Support\Facades\DB;

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

            foreach ($groups as $code => $features) {

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
                    Ruas::create([
                        'code'    => $code,
                        'nm_ruas' => $features->first()['properties']['Nm_Ruas'] ?? 'Tanpa Nama',
                    ]);
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
                    /** @var MultiLineString $geometry */

                    Segment::create([
                        'ruas_code' => $code,
                        'sta'       => $p['STA']       ?? null,
                        'jens_perm' => $p['Jens_Perm'] ?? null,
                        'kondisi'   => $p['Kondisi']   ?? null,
                        'geometry'  => $geometry,      // works with the cast we set earlier
                    ]);
                }
            }
        });

        return back()->with('success', 'GeoJSON processed.');
    }

    public function destroy(string $code)
    {
        Ruas::where('code', $code)->delete();   // segments auto-cascade
        return back()->with('success', "Ruas $code deleted.");
    }
}
