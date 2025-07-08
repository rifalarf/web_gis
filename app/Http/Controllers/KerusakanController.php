<?php

namespace App\Http\Controllers;

use App\Models\Kerusakan;
use App\Models\Ruas;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class KerusakanController extends Controller
{
    /* -----------------------------------------------------------------
       List page (stub) – we’ll build the Inertia view later
    ----------------------------------------------------------------- */
    public function index()
{
    $rows = Kerusakan::with('ruas')->get()->map(fn ($k) => [
        'id'       => $k->id,
        'sta'      => $k->sta,
        'nm_ruas'  => $k->ruas->nm_ruas,
        'lat'      => $k->point->getY(),   // Magellan helpers
        'lon'      => $k->point->getX(),
    ]);

    return Inertia::render('kerusakan/index', [
        'markers' => $rows,
    ]);
}

    /* -----------------------------------------------------------------
       Show the “add marker” form (stub)
    ----------------------------------------------------------------- */
    public function create()
    {
        return Inertia::render('kerusakan/form', [
        'mode'   => 'create',
        'ruasOptions' => Ruas::orderBy('nm_ruas')
                         ->get(['code AS value', 'nm_ruas AS label']),
        ]);
    }

    /* -----------------------------------------------------------------
       Store
    ----------------------------------------------------------------- */
    public function store(Request $request)
    {
        $data = $request->validate([
            'ruas_code' => 'required|exists:ruas,code',
            'sta'       => 'nullable|string|max:50',
            'lat'       => 'required|numeric|between:-90,90',
            'lon'       => 'required|numeric|between:-180,180',
            'photo'     => 'nullable|image|max:2048', // 2 MB
        ]);

        /* handle photo upload ------------------------------------------------ */
        if ($request->file('photo')) {
            $data['image_path'] = $request
                ->file('photo')
                ->store('kerusakan', 'public'); // storage/kerusakan/…
        }

        /* make Point geometry (lon, lat, srid) ------------------------------ */
        $point = Point::make(
            $data['lon'],   // x = longitude
            $data['lat'],   // y = latitude
            null,           // z
            null,           // m
            4326            // SRID  ← put it here
        );

        $data['point'] = $point;

        Kerusakan::create($data);

        Cache::forget('kerusakan_geojson');

        return to_route('kerusakan.index')
            ->with('success', 'Marker ditambahkan');
    }

    /* -----------------------------------------------------------------
       Show single marker (stub)
    ----------------------------------------------------------------- */
    public function show(int $id)
    {
        return "detail marker {$id} placeholder";
    }

    /* -----------------------------------------------------------------
       Edit form (stub)
    ----------------------------------------------------------------- */
    public function edit(int $id)
    {
        $k = Kerusakan::with('ruas')->findOrFail($id);

        return Inertia::render('kerusakan/form', [
            'mode'   => 'edit',
            'marker' => [
                'id'        => $k->id,
                'ruas_code' => $k->ruas_code,
                'sta'       => $k->sta,
                'lat'       => $k->point->getY(),
                'lon'       => $k->point->getX(),
                'image'     => $k->image_path ? asset('storage/'.$k->image_path) : null,
            ],
            'ruasOptions' => Ruas::orderBy('nm_ruas')
                            ->get(['code AS value', 'nm_ruas AS label']),
        ]);
    }

    /* -----------------------------------------------------------------
       Update (to be implemented later)
    ----------------------------------------------------------------- */
    public function update(Request $request, Kerusakan $kerusakan)
    {
        /* 1. validate ---------------------------------------------------------- */
        $data = $request->validate([
            'ruas_code' => 'required|exists:ruas,code',
            'sta'       => 'nullable|string|max:50',
            'lat'       => 'required|numeric|between:-90,90',
            'lon'       => 'required|numeric|between:-180,180',
            'photo'     => 'nullable|image|max:2048',
        ]);

        /* 2. replace photo if new uploaded ------------------------------------ */
        if ($request->file('photo')) {
            // delete old
            if ($kerusakan->image_path) {
                Storage::disk('public')->delete($kerusakan->image_path);
            }
            $data['image_path'] = $request
                ->file('photo')
                ->store('kerusakan', 'public');
        }

        /* 3. geometry ---------------------------------------------------------- */
        $data['point'] = Point::make(
            $data['lon'],     // x = lon
            $data['lat'],     // y = lat
            null, null, 4326  // z, m, SRID
        );

        /* 4. update ------------------------------------------------------------ */
        $kerusakan->update($data);
        Cache::forget('kerusakan_geojson');

        return to_route('kerusakan.index')
            ->with('success', 'Marker diperbarui');
    }

    /* -----------------------------------------------------------------
       Delete
    ----------------------------------------------------------------- */
    public function destroy(int $id)
    {
        $k = Kerusakan::findOrFail($id);

        /* remove file if exists */
        if ($k->image_path) {
            Storage::disk('public')->delete($k->image_path);
        }

        $k->delete();
        Cache::forget('kerusakan_geojson');

        return back()->with('success', 'Marker dihapus');
    }
}
