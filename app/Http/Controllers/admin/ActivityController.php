<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $activities = Activity::all();

        if ($request->ajax()) {

            return DataTables::of($activities)
                ->addColumn('edit', function ($activity) {
                    return '<button class="btn btn-warning btn-sm btnEditar" id="' . $activity->id . '"><i class="fas fa-edit"></i></button>';
                })
                ->addColumn('schedule', function ($activity) {
                    return '<a href="' . route('admin.activities.show', $activity->id) . '" class="btn btn-success btn-sm"><i class="fas fa-calendar-alt"></i></a>';
                })
                ->addColumn('delete', function ($activity) {
                    return '<form action="' . route('admin.activities.destroy', $activity->id) . '" method="POST" class="frmEliminar d-inline">
                                    ' . csrf_field() . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>';
                })
                ->rawColumns(['edit', 'schedule', 'delete'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.activities.index', compact('activities'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.activities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $date_start = $request->input('date_start');
            $date_end = $request->input('date_end');

            $overlap = Activity::where(function($query) use ($date_start, $date_end) {
                $query->whereBetween('date_start', [$date_start, $date_end])
                    ->orWhereBetween('date_end', [$date_start, $date_end]);
            })->exists();

            if ($overlap) {
                return response()->json([
                    'message' => 'El fecha ingresada se solapa con otra fecha existente.'
                ], 400);
            }

            if ($request->date_end >= $request->date_start) {
                Activity::create($request->all());
                return response()->json(['message' => 'Mantenimiento registrado'], 200);
            }else{
                return response()->json(['message' => 'La fecha fin no debe ser menor a la fecha de inicio.'], 400);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el registro: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $activityschedules = DB::select('SELECT * FROM sp_activityschedules(' . $id . ')');
        $activity = Activity::find($id);

        // $activitysch= Activityschedule::where('activity_id', $id)->get();

        if ($request->ajax()) {

            return DataTables::of($activityschedules)
                ->addColumn('edit', function ($activityschedule) {
                    return '<button class="btn btn-warning btn-sm btnEditar" id="' . $activityschedule->id . '"><i class="fas fa-edit"></i></button>';
                })
                ->addColumn('activity', function ($activityschedule) {
                    return '<button class="btn btn-success btn-sm btnActivity" id="' . $activityschedule->id . '"><i class="fas fa-car"></i></button>';
                })
                ->addColumn('delete', function ($activityschedule) {
                    return '
                    <form action="' . route('admin.activityschedules.destroy', $activityschedule->id) . '" method="POST" class="frmEliminar d-inline">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                    </form>';
                })
                ->rawColumns(['edit', 'activity', 'delete'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.activities.show', compact('activityschedules', 'activity'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $activity = Activity::find($id);
        return view('admin.activities.edit', compact('activity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $activity = Activity::find($id);

            $date_start = $request->input('date_start');
            $date_end = $request->input('date_end');
            
            // Validar si el horario se solapa con otro horario existente, excluyendo el horario que estamos actualizando
            $overlap = Activity::where(function($query) use ($date_start, $date_end, $id) {
                $query->whereBetween('date_start', [$date_start, $date_end])
                    ->orWhereBetween('date_end', [$date_start, $date_end]);
            })
            ->where('id', '!=', $id)  // Excluir el horario que se está editando
            ->exists();

            if ($overlap) {
                return response()->json([
                    'message' => 'La fecha ingresada se solapa con otro fecha existente.'
                ], 400);
            }

            if ($request->date_end >= $request->date_start) {
                $activity->update($request->all());
                return response()->json(['message' => 'Mantenimiento registrado'], 200);
            }else{
                return response()->json(['message' => 'La fecha fin no debe ser menor a la fecha de inicio.'], 400);
            }

            return response()->json(['message' => 'Mantenimiento actualizado'], 200);
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
            $activity = Activity::find($id);
            $activity->delete();
            return response()->json(['message' => 'Mantenimiento eliminado'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
