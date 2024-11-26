<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Brandmodel;
use App\Models\Vehicle;
use App\Models\Vehiclecolor;
use App\Models\Vehicleimage;
use App\Models\Vehicletype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicles = DB::select('SELECT * FROM sp_vehicles();');

        if ($request->ajax()) {

            return DataTables::of($vehicles)
                ->addColumn('logo', function ($vehicle) {
                    return '<img src="' . ($vehicle->logo == '' ? asset('storage/brand_logo/no_image.png') : asset($vehicle->logo)) . '" width="100px" height="70px" class="card">';
                })
                ->addColumn('status', function ($vehicle) {
                    return $vehicle->status == 1 ? '<div style="color: green"><i class="fas fa-check"></i> Activo</div>' : '<div style="color: red"><i class="fas fa-times"></i> Inactivo</div>';
                })
                ->addColumn('actions', function ($vehicle) {
                    return '
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>                        
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <button class="dropdown-item btnEditar" id="' . $vehicle->id . '"><i class="fas fa-edit"></i>  Editar</button>
                            <button class="dropdown-item btnImagenes" id="' . $vehicle->id . '"><i class="fas fa-image"></i>  Imágenes</button>
                            <form action="' . route('admin.vehicles.destroy', $vehicle->id) . '" method="POST" class="frmEliminar d-inline">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                            </form>
                        </div>
                    </div>';
                })
                ->addColumn('occupants', function () {
                    return '<button class="btn btn-success btn-sm"><i class="fas fa-people-arrows"></i>&nbsp;&nbsp;(0)</button>';
                })
                ->rawColumns(['logo', 'status', 'occupants', 'actions'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.vehicles.index', compact('vehicles'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $brandsSQL = Brand::whereRaw("id IN (SELECT brand_id FROM brandmodels)");
        $brands = $brandsSQL->pluck("name", "id");
        $models = Brandmodel::where("brand_id", $brandsSQL->first()->id)->pluck("name", "id");
        $types = Vehicletype::pluck("name", "id");
        $colors = Vehiclecolor::pluck("name", "id");
        return view("admin.vehicles.create", compact("brands", "models", "types", "colors"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                "name" => "unique:vehicles",
                "code" => "unique:vehicles",
                "plate" => "unique:vehicles"
            ]);

            if (!isset($request->status)) {
                $status = 0;
            } else {
                $status = 1;
            }

            $vehicle = Vehicle::create($request->except("image") + ["status" => $status]);

            if ($request->image != "") {
                $image = $request->file("image")->store("public/vehicles_images/" . $vehicle->id);
                $urlImage = Storage::url($image);
                Vehicleimage::create([
                    "image" => $urlImage,
                    "profile" => 1,
                    "vehicle_id" => $vehicle->id
                ]);
            }

            return response()->json(['message' => 'Vehículo registrado correctamente'], 200);
        } catch (\Throwable $th) {

            return response()->json(['message' => 'Error en el registro: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $images = Vehicleimage::where("vehicle_id", $id)->get();
        return view("admin.vehicles.show", compact("images"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $vehicle = Vehicle::find($id);

        $brandsSQL = Brand::whereRaw("id IN (SELECT brand_id FROM brandmodels)");
        $brands = $brandsSQL->pluck("name", "id");
        $models = Brandmodel::where("brand_id", $vehicle->brand_id)->pluck("name", "id");
        $types = Vehicletype::pluck("name", "id");
        $colors = Vehiclecolor::pluck("name", "id");
        return view("admin.vehicles.edit", compact("brands", "models", "types", "colors", "vehicle"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $request->validate([
                "name" => "unique:vehicles,name," . $id,
                "code" => "unique:vehicles,code," . $id,
                "plate" => "unique:vehicles,plate," . $id
            ]);

            if (!isset($request->status)) {
                $status = 0;
            } else {
                $status = 1;
            }

            $vehicle = Vehicle::find($id);

            $vehicle->update($request->except("image") + ["status" => $status]);

            if ($request->image != "") {
                $image = $request->file("image")->store("public/vehicles_images/" . $vehicle->id);
                $urlImage = Storage::url($image);
                DB::select("UPDATE vehicleimages SET profile=0 WHERE vehicle_id=$id");
                Vehicleimage::create([
                    "image" => $urlImage,
                    "profile" => 1,
                    "vehicle_id" => $vehicle->id
                ]);
            }

            return response()->json(['message' => 'Vehículo actualizado correctamente'], 200);
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
            $vehicle = Vehicle::find($id);
            $vehicle->delete();
            return response()->json(['message' => 'Vehículo eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
