<?php

namespace App\Http\Controllers;

use App\Models\Kerusakan;
use App\Models\Ruas;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class KerusakanController extends Controller
{
    /* -----------------------------------------------------------------
       List page (stub) – we’ll build the Inertia view later
    ----------------------------------------------------------------- */
    public function index()
    {
        return 'kerusakan index placeholder';
    }

    /* -----------------------------------------------------------------
       Show the “add marker” form (stub)
    ----------------------------------------------------------------- */
    public function create()
    {
        return 'kerusakan create form placeholder';
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
        $data['point'] = new Point($data['lon'], $data['lat'], 4326); // x = lon, y = lat

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
        return "edit marker {$id} placeholder";
    }

    /* -----------------------------------------------------------------
       Update (to be implemented later)
    ----------------------------------------------------------------- */
    public function update(Request $request, int $id)
    {
        // TODO
        Cache::forget('kerusakan_geojson');
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
