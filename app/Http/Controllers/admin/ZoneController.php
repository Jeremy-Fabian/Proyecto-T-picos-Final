<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Sector;
use App\Models\Zone;
use App\Models\Zonecoord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $zones = DB::select('SELECT * FROM sp_zones(1,0)');

        if ($request->ajax()) {

            return DataTables::of($zones)
                ->addColumn('actions', function ($zone) {
                    return '
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bars"></i>                        
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <button class="dropdown-item btnEditar" id="' . $zone->id . '"><i class="fas fa-edit"></i>  Editar</button>
                                <form action="' . route('admin.zones.destroy', $zone->id) . '" method="POST" class="frmEliminar d-inline">
                                    ' . csrf_field() . method_field('DELETE') . '
                                    <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                                </form>
                            </div>
                        </div>';
                })
                ->addColumn('coords', function ($zone) {
                    return '<a href="' . route('admin.zones.show', $zone->id) . '" class="btn btn-success btn-sm"><i class="fas fa-plus-circle"></i></a>
                    <button class="btn btn-danger btn-sm btnMap" id='.$zone->id.'><i class="fas fa-map-marked-alt"></i></button>';
                })
                ->rawColumns(['actions', 'coords'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.zones.index', compact('zones'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $districts = District::pluck('name', 'id');
        $sectors = Sector::pluck('name', 'id');
        return view('admin.zones.create', compact('districts', 'sectors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                "name" => "unique:zones"
            ]);
            Zone::create($request->all());

            return response()->json(['message' => 'Zona registrada'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la actualización: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $zone = DB::select('SELECT * FROM sp_zones(2,' . $id . ')')[0];

        $coords = Zonecoord::where('zone_id', $id)->get();

        if ($request->ajax()) {

            return DataTables::of($coords)
                ->addColumn('actions', function ($coord) {
                    return '      
                    <form action="' . route('admin.zonecoords.destroy', $coord->id) . '" method="POST" class="frmEliminar d-inline">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                    </form>';
                })
                ->rawColumns(['actions'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.zones.show', compact('zone', 'coords'));
        }

        //return view('admin.zones.show', compact('zone', 'coords'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $zone = Zone::find($id);
        $districts = District::pluck('name', 'id');
        $sectors = Sector::pluck('name', 'id');
        return view('admin.zones.edit', compact('zone', 'districts', 'sectors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                "name" => "unique:zones,name," . $id
            ]);
            $zone = Zone::find($id);
            $zone->update($request->all());
            return response()->json(['message' => 'Zona actualizada correctamente'], 200);
        } catch (\Throwable $th) {

            return response()->json(['message' => 'Error en la actualización: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $zone = Zone::find($id);
            $zone->delete();
            return response()->json(['message' => 'Zona eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
