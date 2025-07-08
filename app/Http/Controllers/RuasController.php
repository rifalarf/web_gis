<?php

namespace App\Http\Controllers;

use App\Models\Ruas;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        $markerFeatures = \App\Models\Kerusakan::where('ruas_code', $code)
            ->get()
            ->map->toGeoJsonFeature()
            ->all();

        $markersGeojson = [
            'type'     => 'FeatureCollection',
            'features' => $markerFeatures,
        ];

        /* ── inertia view ─────────────────────────────────────────── */
        return Inertia::render('ruas/show', [
            'ruas'            => $ruas->only('code', 'nm_ruas'),
            'geojson'         => $geojson,
            'markersGeojson'  => $markersGeojson,   // ← NEW PROP
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
}
