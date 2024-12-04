<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /*public function index()
    {
        $brands = Brand::all();
        return view('admin.brands.index', compact('brands'));
    }*/

    /*public function index(Request $request)
    {
        $brands = Brand::all();

        if ($request->ajax()) {


            return DataTables::of($brands)
                ->addColumn('logo', function ($brand) {
                    return '<img src="' . ($brand->logo == '' ? asset('storage/brand_logo/no_image.png') : asset($brand->logo)) . '" width="100" height="50">';
                })
                ->addColumn('edit', function ($brand) {
                    return '<button class="btnEditar btn btn-primary btn-sm" id=' . $brand->id . '><i class="fas fa-edit"></i></button>';
                })
                ->addColumn('delete', function ($brand) {
                    return '
                <form action="' . route('admin.brands.destroy', $brand->id) . '" method="POST" class="frmEliminar" style="display:inline;">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>';
                })
                ->rawColumns(['logo', 'edit', 'delete'])  // Indica que estas columnas contienen HTML
                ->make(true);  // Retorna el JSON esperado por DataTables
        } else {
            return view('admin.brands.index', compact('brands'));
        }
    }*/

    public function index(Request $request)
    {
        $brands = Brand::all();

        if ($request->ajax()) {

            return DataTables::of($brands)
                ->addColumn('logo', function ($brand) {
                    return '<img src="' . ($brand->logo == '' ? asset('storage/brand_logo/no_image.png') : asset($brand->logo)) . '" width="100px" height="70px" class="card">';
                })
                ->addColumn('actions', function ($brand) {
                    return '
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bars"></i>                        
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <button class="dropdown-item btnEditar" id="' . $brand->id . '"><i class="fas fa-edit"></i>  Editar</button>
                            <form action="' . route('admin.brands.destroy', $brand->id) . '" method="POST" class="frmEliminar d-inline">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                            </form>
                        </div>
                    </div>';
                })
                ->rawColumns(['logo', 'actions'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.brands.index', compact('brands'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $logo = '';
            if ($request->logo != '') {
                $image = $request->file('logo')->store('public/brand_logo');
                $logo = Storage::url($image);
            }

            Brand::create([
                'name' => $request->name,
                'logo' => $logo,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Marca registra correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el registro: ' . $th->getMessage()], 500);
        }

        /*return redirect()->route('admin.brands.index')
            ->with('success', 'Marca registrada correctamente');*/
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
        $brand = Brand::find($id);
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        try {
            $brand = Brand::find($id);
            /*$brand->update($request->all());*/

            $logo = '';
            if ($request->logo != '') {
                $image = $request->file('logo')->store('public/brand_logo');
                $logo = Storage::url($image);

                $brand->update([
                    'name' => $request->name,
                    'logo' => $logo,
                    'description' => $request->description
                ]);
            } else {
                $brand->update([
                    'name' => $request->name,
                    'description' => $request->description
                ]);
            }

            return response()->json(['message' => 'Marca actualizada correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error actualizar: ' . $th->getMessage()], 500);
        }



        /*return redirect()->route('admin.brands.index')
            ->with('success', "Marca actualizada correctamente");*/
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $brand = Brand::findOrFail($id); // Verifica que la marca exista

            $brand->delete(); // Intenta eliminar la marca

            return response()->json(['message' => 'Marca eliminada'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Marca no encontrada'], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23503') {
                return response()->json(['message' => 'No se puede eliminar la marca porque está asociada a uno o más modelos'], 409);
            }
            return response()->json(['message' => 'Error en la base de datos: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error inesperado: ' . $e->getMessage()], 500);
        }
    }
}
