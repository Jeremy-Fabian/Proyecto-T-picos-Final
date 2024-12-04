<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use App\Models\Vehiclecolor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VehiclecolorController extends Controller
{

    public function index(Request $request)
    {
        $colors = Vehiclecolor::all();

        if ($request->ajax()) {
            return DataTables::of($colors)
                ->addColumn('color_display', function ($color) {
                    return '<span class="badge" style="background-color:' . $color->color . '; color:' . ($color->color == '#ffffff' ? '#000000' : '#fff') . ';">' . mb_strtoupper($color->color) . '</span>';
                })
                ->addColumn('actions', function ($color) {
                    return '
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>                        
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <button class="dropdown-item btnEditar" id="' . $color->id . '"><i class="fas fa-edit"></i>  Editar</button>
                            <form action="' . route('admin.colors.destroy', $color->id) . '" method="POST" class="frmEliminar d-inline">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                            </form>
                        </div>
                    </div>';
                })
                ->rawColumns(['color_display', 'actions'])
                ->make(true);
        } else {
            return view('admin.colors.index', compact('colors'));
        }
    }

    public function create()
    {
        return view('admin.colors.create');
    }

    public function store(Request $request)
    {
        try {
            Vehiclecolor::create([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
            ]);

            return response()->json(['message' => 'Color registra correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el registro: ' . $th->getMessage()], 500);
        }

        /*return redirect()->route('admin.colors.index')
            ->with('success', 'Color registrada correctamente');*/
    }



    public function edit(string $id)
    {
        $color = Vehiclecolor::find($id);
        return view('admin.colors.edit', compact('color'));
    }


    public function update(Request $request, string $id)
    {

        try {
            $color = Vehiclecolor::find($id);
            $color->update([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
            ]);

            return response()->json(['message' => 'Color actualizada correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error actualizar: ' . $th->getMessage()], 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            $color = Vehiclecolor::findOrFail($id);

            $color->delete();

            return response()->json(['message' => 'Color eliminada'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Color no encontrada'], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23503') {
                return response()->json(['message' => 'No se puede eliminar el Color porque está asociada a uno o más vehiculos'], 409);
            }
            return response()->json(['message' => 'Error en la base de datos: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error inesperado: ' . $e->getMessage()], 500);
        }
    }
}
