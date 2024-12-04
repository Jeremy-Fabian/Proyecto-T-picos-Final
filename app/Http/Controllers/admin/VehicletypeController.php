<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use App\Models\Vehicletype;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VehicletypeController extends Controller
{

    public function index(Request $request)
    {
        $types = Vehicletype::all();

        if ($request->ajax()) {
            return DataTables::of($types)
                ->addColumn('actions', function ($type) {
                    return '
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>                        
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <button class="dropdown-item btnEditar" id="' . $type->id . '"><i class="fas fa-edit"></i>  Editar</button>
                            <form action="' . route('admin.types.destroy', $type->id) . '" method="POST" class="frmEliminar d-inline">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                            </form>
                        </div>
                    </div>';
                })
                ->rawColumns(['logo', 'actions'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.types.index', compact('types'));
        }
    }

    public function create()
    {
        return view('admin.types.create');
    }

    public function store(Request $request)
    {
        try {
            Vehicletype::create([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Tipo registrado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el registro: ' . $th->getMessage()], 500);
        }

        /*return redirect()->route('admin.types.index')
            ->with('success', 'Tipo registrada correctamente');*/
    }



    public function edit(string $id)
    {
        $type = Vehicletype::find($id);
        return view('admin.types.edit', compact('type'));
    }


    public function update(Request $request, string $id)
    {

        try {
            $type = Vehicletype::find($id);
            $type->update([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Tipo actualizado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error actualizar: ' . $th->getMessage()], 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            $type = Vehicletype::findOrFail($id);

            $type->delete();

            return response()->json(['message' => 'Tipo eliminada'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Tipo no encontrada'], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23503') {
                return response()->json(['message' => 'No se puede eliminar el Tipo porque está asociada a uno o más vehiculos'], 409);
            }
            return response()->json(['message' => 'Error en la base de datos: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error inesperado: ' . $e->getMessage()], 500);
        }
    }
}
