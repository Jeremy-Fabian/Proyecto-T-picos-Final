<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\Routezone;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $routes = Route::all();

        if ($request->ajax()) {

            return DataTables::of($routes)
                ->addColumn('status', function ($route) {
                    return $route->status == 1 ? '<div style="color: green"><i class="fas fa-check"></i> Activo</div>' : '<div style="color: red"><i class="fas fa-times"></i> Inactivo</div>';
                })
                ->addColumn('actions', function ($route) {
                    return '
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <button class="dropdown-item btnEditar" id="' . $route->id . '"><i class="fas fa-edit"></i>  Editar</button>
                            <form action="' . route('admin.routes.destroy', $route->id) . '" method="POST" class="frmEliminar d-inline">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                            </form>
                        </div>
                    </div>';
                })
                ->addColumn('coords', function ($route) {
                    return '<button class="btn btn-danger btn-sm btnMap" id='.$route->id.'><i class="fas fa-map-marked-alt"></i></button>';
                })
                ->rawColumns(['status', 'actions','coords'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.routes.index', compact('routes'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $route = Route::select(
            'id as id',
            'name as name',
            'status as status',
            'latitud_start as latStart',
            'longitude_start as lngStart',
            'latitude_end as latEnd',
            'longitude_end as lngEnd'
        )
        ->where('id', 0)
        ->first();

        $isEdit = false;

        $zones = Zone::pluck('name', 'id');

        $zonescoords = collect(DB::select("SELECT * FROM sp_zonecoords()"));
        $groupedSectors= $zonescoords->groupBy("sector");

        $perimeter = $groupedSectors->map(function($sectorGroup) {
            // Agrupar las coordenadas por zona dentro de cada sector
            $zones = $sectorGroup->groupBy("zone")->map(function($zoneGroup) {
                $coords = $zoneGroup->map(function($item) {
                    return [
                        'lat' => (float) $item->latitude,
                        'lng' => (float) $item->longitude,
                    ];
                })->toArray();

                return [
                    'zone' => $zoneGroup[0]->zone, // Tomamos el nombre de la zona
                    'coords' => $coords,
                ];
            })->values();

            return [
                'sector' => $sectorGroup[0]->sector,
                'zones' => $zones,
            ];
        })->values();

        $routecoord = Route::select(
            'id as id',
            'name as name',
            'status as status',
            'latitud_start as latStart',
            'longitude_start as lngStart',
            'latitude_end as latEnd',
            'longitude_end as lngEnd'
        )
        ->get()
        ->map(function ($coord) {
            // Asegúrate de que lat y lng sean numéricos
            $coord->latStart = (float) $coord->latStart;
            $coord->lngStart = (float) $coord->lngStart;
            $coord->latEnd = (float) $coord->latEnd;
            $coord->lngEnd = (float) $coord->lngEnd;
            return $coord;
        });


        $routevertice = Route::select(
            'id as id',
            'name as name',
            'status as status',
            'latitud_start as latStart',
            'longitude_start as lngStart',
            'latitude_end as latEnd',
            'longitude_end as lngEnd'
        )
        ->latest() // Ordena por la columna created_at de manera descendente
        ->first();

        return view('admin.routes.create',compact('route', 'zones', 'perimeter', 'isEdit', 'routecoord', 'routevertice'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                "name" => "unique:routes"
            ]);

            $route_ = Route::create($request->except("zone_id"));
            // Obtener el zone_id del formulario
            $zone_id = $request->input('zone_id');

            // Crear el registro en Routezone con el id de la ruta y el zone_id
            Routezone::create([
                'route_id' => $route_->id, // Usar el id de la ruta recién creada
                'zone_id' => $zone_id,     // El zone_id recibido del formulario
            ]);
            return response()->json(['message' => 'Ruta registrada'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al registrar la ruta'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $route = Route::select(
            'name as name',
            'latitud_start as latStart',
            'longitude_start as lngStart',
            'latitude_end as latEnd',
            'longitude_end as lngEnd'
        )
        ->where('id', $id)
        ->first();  // Solo un resultado, no colección

        // Convertimos las coordenadas a float
        $route->latStart = (float) $route->latStart;
        $route->lngStart = (float) $route->lngStart;
        $route->latEnd = (float) $route->latEnd;
        $route->lngEnd = (float) $route->lngEnd;

        $zonescoords = collect(DB::select("SELECT * FROM sp_zonecoords()"));
        $groupedSectors= $zonescoords->groupBy("sector");

        $perimeter = $groupedSectors->map(function($sectorGroup) {
            // Agrupar las coordenadas por zona dentro de cada sector
            $zones = $sectorGroup->groupBy("zone")->map(function($zoneGroup) {
                $coords = $zoneGroup->map(function($item) {
                    return [
                        'lat' => (float) $item->latitude,
                        'lng' => (float) $item->longitude,
                    ];
                })->toArray();

                return [
                    'zone' => $zoneGroup[0]->zone, // Tomamos el nombre de la zona
                    'coords' => $coords,
                ];
            })->values();

            return [
                'sector' => $sectorGroup[0]->sector,
                'zones' => $zones,
            ];
        })->values();

        $routecoord = Route::select(
            'id as id',
            'name as name',
            'status as status',
            'latitud_start as latStart',
            'longitude_start as lngStart',
            'latitude_end as latEnd',
            'longitude_end as lngEnd'
        )
        ->get()
        ->map(function ($coord) {
            // Asegúrate de que lat y lng sean numéricos
            $coord->latStart = (float) $coord->latStart;
            $coord->lngStart = (float) $coord->lngStart;
            $coord->latEnd = (float) $coord->latEnd;
            $coord->lngEnd = (float) $coord->lngEnd;
            return $coord;
        });

        return view('admin.routes.show',compact('route', 'perimeter', 'routecoord'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $route = Route::select(
            'id as id',
            'name as name',
            'status as status',
            'latitud_start as latStart',
            'longitude_start as lngStart',
            'latitude_end as latEnd',
            'longitude_end as lngEnd'
        )
        ->where('id', $id)
        ->first();  // Solo un resultado, no colección

        // Convertimos las coordenadas a float
        $route->latStart = (float) $route->latStart;
        $route->lngStart = (float) $route->lngStart;
        $route->latEnd = (float) $route->latEnd;
        $route->lngEnd = (float) $route->lngEnd;

        $isEdit = true;


        $routezone = Routezone::where('route_id', $id)->first();
        // Obtén todas las zonas disponibles para el selector
        $zones = Zone::pluck('name', 'id'); // Obtiene las zonas en formato ['id' => 'nombre']

        // Obtén el zone_id actual de la ruta que se está editando (si existe)
        $currentZoneId = $routezone ? $routezone->zone_id : null;


        $zonescoords = collect(DB::select("SELECT * FROM sp_zonecoords()"));
        $groupedSectors= $zonescoords->groupBy("sector");

        $perimeter = $groupedSectors->map(function($sectorGroup) {
            // Agrupar las coordenadas por zona dentro de cada sector
            $zones = $sectorGroup->groupBy("zone")->map(function($zoneGroup) {
                $coords = $zoneGroup->map(function($item) {
                    return [
                        'lat' => (float) $item->latitude,
                        'lng' => (float) $item->longitude,
                    ];
                })->toArray();

                return [
                    'zone' => $zoneGroup[0]->zone, // Tomamos el nombre de la zona
                    'coords' => $coords,
                ];
            })->values();

            return [
                'sector' => $sectorGroup[0]->sector,
                'zones' => $zones,
            ];
        })->values();

        $routecoord = Route::select(
            'id as id',
            'name as name',
            'status as status',
            'latitud_start as latStart',
            'longitude_start as lngStart',
            'latitude_end as latEnd',
            'longitude_end as lngEnd'
        )
        ->get()
        ->map(function ($coord) {
            // Asegúrate de que lat y lng sean numéricos
            $coord->latStart = (float) $coord->latStart;
            $coord->lngStart = (float) $coord->lngStart;
            $coord->latEnd = (float) $coord->latEnd;
            $coord->lngEnd = (float) $coord->lngEnd;
            return $coord;
        });


        $routevertice = Route::select(
            'id as id',
            'name as name',
            'status as status',
            'latitud_start as latStart',
            'longitude_start as lngStart',
            'latitude_end as latEnd',
            'longitude_end as lngEnd'
        )
        ->latest() // Ordena por la columna created_at de manera descendente
        ->first();



        return view('admin.routes.edit',compact('route', 'zones', 'currentZoneId', 'isEdit', 'perimeter', 'routecoord', 'routevertice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                "name" => "unique:routes,name," . $id
            ]);

            $route = Route::find($id);
            $route->update($request->except("zone_id"));

            // Verificar y actualizar la relación con la zona solo si ha cambiado
            $routezone = Routezone::where('route_id', $id)->first(); // Buscar la relación actual
            $zone_id = $request->input('zone_id'); // Nuevo valor recibido del formulario

            if ($routezone && $routezone->zone_id != $zone_id) {
                // Si el valor ha cambiado, actualizarlo
                $routezone->update([
                    'zone_id' => $zone_id,
                ]);
            }

            return response()->json(['message' => 'Ruta actualizada correctamente'], 200);
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
            // Buscar la Routezone asociada con el route_id igual al $id
            $routezone = Routezone::where('route_id', $id)->first();
            
            // Verificar si existe una Routezone con ese route_id
            if ($routezone) {
                // Eliminar la Routezone si existe
                $routezone->delete();
            }

            $route = Route::find($id);
            $route->delete();
            return response()->json(['message' => 'Ruta eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error la eliminacións: ' . $th->getMessage()], 500);
        }
    }
}
