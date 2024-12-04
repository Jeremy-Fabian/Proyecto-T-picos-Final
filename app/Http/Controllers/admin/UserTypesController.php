<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Usertype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UserTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $usertypes = DB::select('SELECT * FROM usertypes');


        if ($request->ajax()) {

            return DataTables::of($usertypes)
                ->addColumn('actions', function ($usertype) {
                    return '
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>                        
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <button class="dropdown-item btnEditar" id="' . $usertype->id . '"><i class="fas fa-edit"></i>  Editar</button>
                            <form action="' . route('admin.usertypes.destroy', $usertype->id) . '" method="POST" class="frmEliminar d-inline">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                            </form>
                        </div>
                    </div>';
                })
                ->rawColumns(['actions'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.usertypes.index', compact('usertypes'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.usertypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Usertype::create([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Tipo de Persona registrada correctamente'], 200);
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
    public function edit(string $id)
    {
        $usertypes = Usertype::find($id);
        return view('admin.usertypes.edit', compact('usertypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $usertypes = Usertype::find($id);
            $usertypes->update($request->all());

            return response()->json(['message' => 'Tipo actualizado'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar el tipo'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $usertypes = Usertype::find($id);
            $usertypes->delete();
            return response()->json(['message' => 'Tipo eliminado'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al eliminar el tipo, ya esta asignado a usuarios'], 500);
        }
    }
}
