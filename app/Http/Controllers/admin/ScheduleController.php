<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schedules = Schedule::all();
        if ($request->ajax()) {

            return DataTables::of($schedules)
                ->addColumn('actions', function ($schedule) {
                    return '
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <button class="dropdown-item btnEditar" id="' . $schedule->id . '"><i class="fas fa-edit"></i>  Editar</button>
                            <form action="' . route('admin.schedules.destroy', $schedule->id) . '" method="POST" class="frmEliminar d-inline">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                            </form>
                        </div>
                    </div>';
                })
                ->rawColumns(['actions'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.schedules.index', compact('schedules'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.schedules.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                "name" => "unique:schedules"
            ]);

            $time_start = $request->input('time_start');
            $time_end = $request->input('time_end');

            $overlap = Schedule::where(function($query) use ($time_start, $time_end) {
                $query->whereBetween('time_start', [$time_start, $time_end])
                    ->orWhereBetween('time_end', [$time_start, $time_end]);
            })->exists();

            if ($overlap) {
                return response()->json([
                    'message' => 'El horario se solapa con otro horario existente.'
                ], 400);
            }
            Schedule::create($request->all());

            return response()->json(['message' => 'Horario registrado'], 200);
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
        $schedule = Schedule::find($id);
        return view('admin.schedules.edit', compact('schedule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                "name" => "unique:schedules,name," . $id
            ]);
            $schedule = Schedule::find($id);

            // Obtener el horario actualizado de la solicitud
            $time_start = $request->input('time_start');
            $time_end = $request->input('time_end');
            
            // Validar si el horario se solapa con otro horario existente, excluyendo el horario que estamos actualizando
            $overlap = Schedule::where(function($query) use ($time_start, $time_end, $id) {
                $query->whereBetween('time_start', [$time_start, $time_end])
                    ->orWhereBetween('time_end', [$time_start, $time_end]);
            })
            ->where('id', '!=', $id)  // Excluir el horario que se está editando
            ->exists();

            if ($overlap) {
                return response()->json([
                    'message' => 'El horario se solapa con otro horario existente.'
                ], 400);
            }

            $schedule->update($request->all());
            return response()->json(['message' => 'Horario actualizado correctamente'], 200);
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
            $schedule = Schedule::find($id);
            $schedule->delete();
            return response()->json(['message' => 'Horario eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
