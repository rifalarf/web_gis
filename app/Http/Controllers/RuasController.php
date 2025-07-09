<?php

namespace App\Http\Controllers;

use App\Models\Ruas;
use App\Models\Segment;
use App\Models\Kerusakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;   // add
use Illuminate\Validation\ValidationException;

class RuasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return Inertia::render('ruas/index', [
            'ruas' => Ruas::orderBy('code')->get(['code','nm_ruas']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $code)
    {
        $ruas = Ruas::with('segments')->where('code', $code)->firstOrFail();

        /* ── line features for THIS ruas ─────────────────────────── */
        $features = $ruas->segments->map(fn ($s) => [
            'type'       => 'Feature',
            'properties' => [
                'code'      => $s->ruas_code,
                'nm_ruas'   => $s->ruas->nm_ruas,
                'sta'       => $s->sta,
                'jens_perm' => $s->jens_perm,
                'kondisi'   => $s->kondisi,
            ],
            'geometry'   => $s->geometry->jsonSerialize(),
        ]);

        $geojson = [
            'type'     => 'FeatureCollection',
            'features' => $features,
        ];

        /* ── gather markers that belong to this ruas ─────────────── */
        $markerFeatures = Kerusakan::where('ruas_code', $code)
            ->get()
            ->map->toGeoJsonFeature()
            ->all();

        $markersGeojson = [
            'type'     => 'FeatureCollection',
            'features' => $markerFeatures,
        ];

        /* ── table rows (id, sta, lat, lon) ───────────────────── */
        $markerRows = Kerusakan::where('ruas_code', $code)
            ->orderBy('sta')
            ->get()
            ->map(fn ($k) => [
                'id'  => $k->id,
                'sta' => $k->sta ?? '—',
                'lat' => $k->point->getY(),
                'lon' => $k->point->getX(),
            ]);


        /* ── inertia view ─────────────────────────────────────────── */
        return Inertia::render('ruas/show', [
            'ruas'            => $ruas->only('code', 'nm_ruas'),
            'geojson'         => $geojson,
            'markersGeojson'  => $markersGeojson,
            'markerRows'      => $markerRows,    // ← NEW PROP
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function purge(Request $request)
    {
        /* 1. validate that a password is present */
        $request->validate(['password' => ['required', 'string']]);

        /* 2. verify it matches the logged-in user *//** @var string $hashed */    // help the analyser
        $hashed = $request->user()->password;
        if (! Hash::check($request->password, $hashed)) {
            throw ValidationException::withMessages([
                'password' => 'Password salah.',
            ]);
        }

        /* 3. wipe data only when password correct */
        Ruas::truncate();
        Segment::truncate();
        Kerusakan::truncate();   // if markers should vanish
        Cache::forget('segments_geojson');
        Cache::forget('kerusakan_geojson');

        return redirect()
            ->route('dashboard')
            ->with('success', 'Semua ruas berhasil dihapus.');
    }

}
