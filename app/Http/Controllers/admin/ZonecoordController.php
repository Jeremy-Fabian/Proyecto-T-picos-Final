<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Zonecoord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZonecoordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        try {
             // Validación de los datos recibidos
        $validated = $request->validate([
            'markers.*.latitude' => 'required|numeric',
            'markers.*.longitude' => 'required|numeric',
            'markers.*.zone_id' => 'required|integer',
        ]);

        if (isset($request->markers) && is_array($request->markers)) {
            foreach ($request->markers as $marker) {
                // Crear o guardar las coordenadas en la base de datos
                Zonecoord::create([
                    'latitude' => $marker['latitude'],
                    'longitude' => $marker['longitude'],
                    'zone_id' => (int) $marker['zone_id'],  // Asegúrate de que zone_id es un entero
                ]);
            }

            return response()->json(['message' => 'Coordenadas registradas con éxito'], 200);
        } else {
            return response()->json(['message' => 'No se encontraron datos de marcadores válidos'], 400);
        }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el registro: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // $vertice = Zonecoord::select(
        //     'latitude as lat',
        //     'longitude as lng'
        // )->where('zone_id', $id)->get();

        $vertice = Zonecoord::select(
            'latitude as lat',
            'longitude as lng'
        )
        ->where('zone_id', $id)
        ->get()
        ->map(function ($coord) {
            // Asegúrate de que lat y lng sean numéricos
            $coord->lat = (float) $coord->lat;
            $coord->lng = (float) $coord->lng;
            return $coord;
        });

        return view('admin.zonecoords.show',compact('vertice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // $lastCoords = Zonecoord::select(
        //     'latitude as lat',
        //     'longitude as lng'
        // )->where('zone_id', $id)->latest()->first();

        $lastCoords = Zonecoord::select('latitude as lat', 'longitude as lng')
        ->where('zone_id', $id)
        ->orderBy('id', 'desc')  // Ordenar por el ID en orden descendente (el más alto será el último)
        ->first();

        // $vertice = Zonecoord::select(
        //     'latitude as lat',
        //     'longitude as lng'
        // )->where('zone_id', $id)->get();

        $vertice = Zonecoord::select(
            'latitude as lat',
            'longitude as lng'
        )
        ->where('zone_id', $id)
        ->get()
        ->map(function ($coord) {
            $coord->lat = (float) $coord->lat;
            $coord->lng = (float) $coord->lng;
            return $coord;
        });


        $zonescoords = collect(DB::select("SELECT * FROM sp_zonecoords_id($id);"));

        $groupedZones= $zonescoords->groupBy("zone");

        $perimeter = $groupedZones->map(function($zone){
            
            $coords = $zone->map(function($item){
                return[
                    'lat'=> (float) $item->latitude,
                    'lng'=> (float) $item->longitude,
                ];

            })->toArray();

            return [
                'name'=>$zone[0]->zone,
                'coords'=>$coords
            ];
        })->values();

        return view('admin.zonecoords.create', compact('lastCoords','vertice', 'perimeter'))->with('zone_id', $id);
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
        try {
            $zonecoord = Zonecoord::find($id);
            $zonecoord->delete();
            return response()->json(['message' => 'Coordenada eliminada'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
