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
        $vehicles = Vehicle::pluck('name', 'id')->prepend('Selecciona un vehículo', '');
        $routes = Route::pluck('name', 'id');
        $schedules = Schedule::all()->pluck('id')->mapWithKeys(function ($id) {
            $schedule = Schedule::find($id);
            return [
                $id => $schedule->name . ' (' . $schedule->time_start . ' - ' . $schedule->time_end . ')',
            ];
        });
        return view('admin.vehicleroutes.create', compact('vehicles', 'routes', 'schedules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Obtener las fechas como cadenas directamente del request
            $dateStart = $request->date_start;
            $dateEnd = $request->date_end;

            // Verificar si la fecha de fin es menor que la fecha de inicio
            if ($dateEnd < $dateStart) {
                return response()->json(['message' => 'La fecha fin no debe ser menor a la fecha de inicio.'], 400);
            }

            // Establecer la fecha actual como la fecha de inicio
            $currentDate = $dateStart;

            // Recorre cada día en el rango de fechas
            while ($currentDate <= $dateEnd) {
                // Obtener el día de la semana (1 = lunes, 7 = domingo)
                $dayOfWeek = date('N', strtotime($currentDate));

                // Omitir sábados (6) y domingos (7)
                if ($dayOfWeek != 6 && $dayOfWeek != 7) {
                    // Validar si ya existe un registro con la misma ruta y horario para la misma fecha
                    $existingRoute = Vehicleroute::where('date_route', $currentDate)  // Mismo día
                        ->where('route_id', $request->route_id)                         // Mismo route_id
                        ->where('schedule_id', $request->schedule_id)                   // Mismo schedule_id (hora)
                        ->exists();                                                     // Verifica si ya existe un registro

                    // Si el registro ya existe, devolver error
                    if ($existingRoute) {
                        return response()->json([
                            'message' => 'Ya existe una programación con la misma ruta y horario en la fecha: ' . $currentDate
                        ], 400);
                    }

                    // Crear el nuevo registro
                    Vehicleroute::create([
                        'date_route' => $currentDate,  // Guarda la fecha del día actual en date_route
                        'time_route' => $request->time_route,  // Mantener la hora como está en el request
                        'vehicle_id' => $request->vehicle_id,
                        'route_id' => $request->route_id,
                        'schedule_id' => $request->schedule_id,
                        'description' => $request->description,
                    ]);
                }

                // Incrementar la fecha en un día
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            }

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
        $vehicles = Vehicle::pluck('name', 'id')->prepend('Selecciona un vehículo', '');;
        $routes = Route::pluck('name', 'id');
        $schedules = Schedule::all()->pluck('id')->mapWithKeys(function ($id) {
            $schedule = Schedule::find($id);
            return [
                $id => $schedule->name . ' (' . $schedule->time_start . ' - ' . $schedule->time_end . ')',
            ];
        });
        return view('admin.vehicleroutes.edit', compact('vehicleroute' ,'vehicles', 'routes', 'schedules'));
    }
    // --------------------NUEVAS FUNCIONALIDADES---------------------------
    public function editAllProgram()
    {
        $vehicles = Vehicle::pluck('name', 'id')->prepend('Selecciona un vehículo', '');;
        $routes = Route::pluck('name', 'id');
        $schedules = Schedule::all()->pluck('id')->mapWithKeys(function ($id) {
            $schedule = Schedule::find($id);
            return [
                $id => $schedule->name . ' (' . $schedule->time_start . ' - ' . $schedule->time_end . ')',
            ];
        });
        // Pasar las variables a la vista
        return view('admin.vehicleroutes.editprogram', compact('vehicles','routes', 'schedules'));
    }
    public function getVehicleDates($vehicle_id)
    {
        // Obtener el primer y último registro de date_route para el vehículo seleccionado
        $firstRecord = Vehicleroute::where('vehicle_id', $vehicle_id)
                                   ->orderBy('date_route', 'asc')
                                   ->first();
        $lastRecord = Vehicleroute::where('vehicle_id', $vehicle_id)
                                  ->orderBy('date_route', 'desc')
                                  ->first();
        
        $time = $firstRecord->time_route;
        $rout = $firstRecord->route_id;
        $sched = $firstRecord->schedule_id;
        $descript = $firstRecord->description;
        // Inicializar las fechas
        $date_start = $firstRecord ? $firstRecord->date_route : null;
        $date_end = $lastRecord ? $lastRecord->date_route : null;

        // Retornar las fechas
        return ['date_start' => $date_start, 'date_end' => $date_end, 'time' => $time, 'rout' => $rout, 'sched' => $sched, 'descript' => $descript];
    }

    public function updateAllProgram(Request $request)
    {
        try {
            // Obtener las fechas como cadenas directamente del request
            $dateStart = $request->date_start;
            $dateEnd = $request->date_end;
    
            // Verificar si la fecha de fin es menor que la fecha de inicio
            if ($dateEnd < $dateStart) {
                return response()->json(['message' => 'La fecha fin no debe ser menor a la fecha de inicio.'], 400);
            }
    
            // Generar todas las fechas válidas en el rango, excluyendo sábados y domingos
            $validDates = [];
            $currentDate = $dateStart;
    
            while ($currentDate <= $dateEnd) {
                $dayOfWeek = date('N', strtotime($currentDate));
    
                if ($dayOfWeek != 6 && $dayOfWeek != 7) { // Excluir sábados y domingos
                    $validDates[] = $currentDate;
                }
    
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            }
    
            // Obtener los registros actuales del vehículo
            $existingRecords = Vehicleroute::where('vehicle_id', $request->vehicle_id)->get();
    
            // Almacenar las fechas actuales en la base de datos
            $existingDates = $existingRecords->pluck('date_route')->toArray();
    
            // Identificar las fechas que deben eliminarse (no están en $validDates)
            $datesToDelete = array_diff($existingDates, $validDates);
    
            // Identificar las fechas que deben crearse (no están en $existingDates)
            $datesToCreate = array_diff($validDates, $existingDates);
    
            // Actualizar los registros existentes dentro del rango
            foreach ($existingRecords as $record) {
                if (in_array($record->date_route, $validDates)) {
                    $record->update([
                        'time_route' => $request->time_route,
                        'route_id' => $request->route_id,
                        'schedule_id' => $request->schedule_id,
                        'description' => $request->description,
                    ]);
                }
            }
    
            // Eliminar registros fuera del rango
            Vehicleroute::where('vehicle_id', $request->vehicle_id)
                ->whereIn('date_route', $datesToDelete)
                ->delete();
    
            // Crear nuevos registros para las fechas que faltan
            foreach ($datesToCreate as $date) {
                Vehicleroute::create([
                    'date_route' => $date,
                    'time_route' => $request->time_route,
                    'vehicle_id' => $request->vehicle_id,
                    'route_id' => $request->route_id,
                    'schedule_id' => $request->schedule_id,
                    'description' => $request->description,
                ]);
            }
    
            return response()->json(['message' => 'Programación actualizada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la actualización: ' . $th->getMessage()], 500);
        }
    }
    // --------------------NUEVAS FUNCIONALIDADES---------------------------

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
