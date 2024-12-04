<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Activityschedule;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityscheduleController extends Controller
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
            $isValid = DB::select('SELECT validate_schedule_overlap(?, ?, ?, ?)', [
                $request->input('activity_id'),
                $request->input('day'),
                $request->input('time_start'),
                $request->input('time_end')
            ]);

            if ($isValid[0]->validate_schedule_overlap) {
                Activityschedule::create($request->all());
                return response()->json(['message' => 'Horario registrado'], 200);
            } else {
                return response()->json(['message' => 'El horario está solapado con otro existente.'], 400);
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function register(string $id)
    {
        // Obtenemos los vehículos y conductores
        $vehicles = Vehicle::pluck('name', 'id');
        $drivers = User::pluck('name', 'id');

        // Retornamos la vista con los datos y el ID de la actividad
        return view('admin.activityschedules.create', compact('vehicles', 'drivers'))->with('activity_id', $id);
    }

    public function userdriver(string $id)
    {
        $userdrive = DB::select('SELECT * FROM sp_userdrive(' . $id . ')');;
        return $userdrive;
    }
    public function edit(string $id)
    {
        $activityschedule = Activityschedule::find($id);
        $vehicles = Vehicle::pluck('name', 'id');
        $drivers = User::pluck('name', 'id');
        return view('admin.activityschedules.edit', compact('activityschedule', 'vehicles', 'drivers'))->with('activity_id', $id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $isValid = DB::select('SELECT validate_schedule_overlap(?, ?, ?, ?)', [
                $request->input('activity_id'),
                $request->input('day'),
                $request->input('time_start'),
                $request->input('time_end')
            ]);

            if ($isValid[0]->validate_schedule_overlap) {
                $activityschedule = Activityschedule::find($id);
                $activityschedule->update($request->except("activity_id"));
                return response()->json(['message' => 'Horario actualizado'], 200);
            } else {
                return response()->json(['message' => 'El horario está solapado con otro existente.'], 400);
            }

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la actualizacion: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $activityschedule = Activityschedule::find($id);
            $activityschedule->delete();
            return response()->json(['message' => 'Horario eliminado'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la eliminación: '], 500);
        }
    }
}
