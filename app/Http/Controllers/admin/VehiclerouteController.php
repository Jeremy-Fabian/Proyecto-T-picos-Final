<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Models\Vehicleroute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class VehiclerouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicleroutes = DB::select('SELECT * FROM sp_vehicleroutes()');

        if ($request->ajax()) {

            return DataTables::of($vehicleroutes)
                ->addColumn('actions', function ($vehicleroute) {
                    return '
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bars"></i>                        
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <button class="dropdown-item btnEditar" id="' . $vehicleroute->id . '"><i class="fas fa-edit"></i>  Editar</button>
                                <form action="' . route('admin.vehicleroutes.destroy', $vehicleroute->id) . '" method="POST" class="frmEliminar d-inline">
                                    ' . csrf_field() . method_field('DELETE') . '
                                    <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                                </form>
                            </div>
                        </div>';
                })
                ->rawColumns(['actions'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.vehicleroutes.index', compact('vehicleroutes'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vehicles = Vehicle::pluck('name', 'id');
        $routes = Route::pluck('name', 'id');
        $schedules = Schedule::pluck('name', 'id');
        return view('admin.vehicleroutes.create', compact('vehicles', 'routes', 'schedules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Vehicleroute::create($request->all());

            return response()->json(['message' => 'Programación ruta registrada'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la actualización: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vehicleroute = Vehicleroute::find($id);
        $vehicles = Vehicle::pluck('name', 'id');
        $routes = Route::pluck('name', 'id');
        $schedules = Schedule::pluck('name', 'id');
        return view('admin.vehicleroutes.edit', compact('vehicleroute' ,'vehicles', 'routes', 'schedules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vehicleroute = Vehicleroute::find($id);
            $vehicleroute->update($request->all());
            return response()->json(['message' => 'Programación ruta actualizada correctamente'], 200);
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
            $vehicleroute = Vehicleroute::find($id);
            $vehicleroute->delete();
            return response()->json(['message' => 'Programación ruta eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
