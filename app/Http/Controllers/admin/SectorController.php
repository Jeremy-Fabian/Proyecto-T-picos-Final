<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sectors = Sector::all();

        if ($request->ajax()) {

            return DataTables::of($sectors)
                ->addColumn('actions', function ($sector) {
                    return '
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bars"></i>                        
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <button class="dropdown-item btnEditar" id="' . $sector->id . '"><i class="fas fa-edit"></i>  Editar</button>
                                <form action="' . route('admin.sectors.destroy', $sector->id) . '" method="POST" class="frmEliminar d-inline">
                                    ' . csrf_field() . method_field('DELETE') . '
                                    <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                                </form>
                            </div>
                        </div>';
                })
                ->addColumn('coords', function ($sector) {
                    return '<button class="btn btn-danger btn-sm btnMap" id='.$sector->id.'><i class="fas fa-map-marked-alt"></i></button>';
                })
                ->rawColumns(['actions', 'coords'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.sectors.index', compact('sectors'));
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $districts = District::pluck('name', 'id');
        return view('admin.sectors.create', compact('districts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                "name" => "unique:sectors"
            ]);
            Sector::create($request->all());

            return response()->json(['message' => 'Sector registrado'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la actualización: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $zones = collect(DB::select("SELECT * FROM sp_sectors(".$id.")"));

        $groupedZones= $zones->groupBy("zone");

        $perimeter = $groupedZones->map(function($zone){
            
            $coords = $zone->map(function($item){
                return[
                    'lat'=>$item->latitude,
                    'lng'=>$item->longitude,
                ];

            })->toArray();

            return [
                'name'=>$zone[0]->zone,
                'coords'=>$coords
            ];
        })->values();

        return view('admin.sectors.show',compact('perimeter'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sector = Sector::find($id);
        $districts = District::pluck('name', 'id');
        return view('admin.sectors.edit', compact('sector', 'districts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                "name" => "unique:sectors,name," . $id
            ]);
            $sector = Sector::find($id);
            $sector->update($request->all());
            return response()->json(['message' => 'Sector actualizado correctamente'], 200);
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
            $sector = Sector::find($id);
            $sector->delete();
            return response()->json(['message' => 'Sector eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
