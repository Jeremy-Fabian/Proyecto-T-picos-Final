<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usertype;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::select(
            'users.id',
            'users.name',
            'users.profile_photo_path',
            'users.dni',
            'users.address',
            'users.email',
            'usertypes.name as typename',
            'users.license', #añado el campo licencia
            'zones.name as zname'
        )
            #leftjoin para listar hasta los que no tengan tipo o zona
            ->leftjoin('usertypes', 'users.usertype_id', '=', 'usertypes.id')
            ->leftjoin('zones', 'users.zone_id', '=', 'zones.id')
            ->get();


        if ($request->ajax()) {

            return DataTables::of($users)
                // Agregar columna de imagen
                ->addColumn('image', function ($user) {
                    $imagePath = $user->profile_photo_path ?: Storage::url('public/profile_photos/no_image.png');
                    return '<img src="' . $imagePath . '" width="100px" height="70px" class="rounded">';
                })
                ->addColumn('actions', function ($user) {
                    return '
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bars"></i>                        
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <button class="dropdown-item btnEditar" id="' . $user->id . '"><i class="fas fa-edit"></i>  Editar</button>
                                <form action="' . route('admin.users.destroy', $user->id) . '" method="POST" class="frmEliminar d-inline">
                                    ' . csrf_field() . method_field('DELETE') . '
                                    <button type="submit" class="dropdown-item"><i class="fas fa-trash"></i> Eliminar</button>
                                </form>
                            </div>
                        </div>';
                })
                ->rawColumns(['actions', 'image'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.users.index', compact('users'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = null;
        $userTypes = Usertype::pluck('name', 'id');
        $zones = Zone::pluck('name', 'id'); // Obtén las zonas como array [id => name]
        return view('admin.users.create', compact('user', 'userTypes', 'zones')); //AÑADI USER AL FINAL
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validación de los datos
            $request->validate([
                'name' => 'required|string|max:255',
                'dni' => 'required|string|max:8|unique:users,dni',
                'birthdate' => 'required|date',
                'address' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'usertype_id' => 'required|integer|exists:usertypes,id',
                'zone_id' => 'nullable|integer|exists:zones,id',
                'profile_photo_path' => 'nullable|image|max:2048',
                'license' => 'nullable|string|max:20|unique:users,license', // Agregamos validación para 'license'
            ]);
            // Validación adicional para licencia si el tipo de usuario es 'Conductor'
            if ($request->usertype_id == $this->getConductorTypeId()) { // Reemplaza con tu lógica para obtener el ID de conductor
                $request->validate([
                    'license' => 'required|string|max:20|regex:/^[A-Z0-9]{7,20}$/', // Formato de licencia
                ]);
            }

            // Manejo de la foto de perfil
            $profilePhotoPath = '';
            if ($request->hasFile('profile_photo_path')) {
                $image = $request->file('profile_photo_path')->store('public/profile_photos');
                $profilePhotoPath = Storage::url($image);
            }

            // Creación del usuario
            User::create([
                'name' => $request->name,
                'dni' => $request->dni,
                'birthdate' => $request->birthdate,
                'address' => $request->address,
                'email' => $request->email,
                'password' => bcrypt($request->password), // Encripta la contraseña
                'usertype_id' => $request->usertype_id,
                'zone_id' => $request->zone_id,
                'license' => $request->license ?? null, // Almacena la licencia solo si está presente
                'profile_photo_path' => $profilePhotoPath,
            ]);

            return response()->json(['message' => 'Personal registrado correctamente'], 200);
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
        $user = User::find($id);
        $userTypes = Usertype::pluck('name', 'id');
        $zones = Zone::pluck('name', 'id'); // Obtén las zonas como array [id => name]
        return view('admin.users.edit', compact('user', 'userTypes', 'zones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Busca al usuario
            $user = User::findOrFail($id);

            // Validación de los datos
            $request->validate([
                'name' => 'required|string|max:255',
                'dni' => 'required|string|max:8|unique:users,dni,' . $id, // Ignorar el actual para evitar conflictos
                'birthdate' => 'required|date',
                'address' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8', // Opcional para no siempre cambiarla
                'usertype_id' => 'required|integer|exists:usertypes,id',
                'zone_id' => 'nullable|integer|exists:zones,id',
                'profile_photo_path' => 'nullable|image|max:2048',
                'license' => 'nullable|string|max:20|unique:users,license' . $id, // Validación base para 'license'
            ]);

            // Validación adicional para licencia si el tipo de usuario es 'Conductor'
            if ($request->usertype_id == $this->getConductorTypeId()) { // Reemplaza con tu lógica para obtener el ID de conductor
                $request->validate([
                    'license' => 'required|string|max:20|regex:/^[A-Z0-9]{7,20}$/', // Formato de licencia
                ]);
            } else {
                // Si el tipo de usuario no es "Conductor", asegura que no se guarde licencia
                $request->merge(['license' => null]);
            }


            // Manejo de la foto de perfil (si se envía una nueva)
            if ($request->hasFile('profile_photo_path')) {
                // Borra la foto anterior si existe
                if ($user->profile_photo_path && Storage::exists(str_replace('/storage', 'public', $user->profile_photo_path))) {
                    Storage::delete(str_replace('/storage', 'public', $user->profile_photo_path));
                }

                // Guarda la nueva foto
                $image = $request->file('profile_photo_path')->store('public/profile_photos');
                $user->profile_photo_path = Storage::url($image);
            }

            // Actualiza los demás campos
            $user->name = $request->name;
            $user->dni = $request->dni;
            $user->birthdate = $request->birthdate;
            $user->address = $request->address;
            $user->email = $request->email;
            if ($request->password) { // Solo actualiza la contraseña si se envía
                $user->password = bcrypt($request->password);
            }
            $user->usertype_id = $request->usertype_id;
            $user->zone_id = $request->zone_id;
            $user->license = $request->license; // Actualiza el campo 'license'

            // Guarda los cambios
            $user->save();

            return response()->json(['message' => 'Personal actualizado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar: ' . $th->getMessage()], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Verificar si el usuario en sesión intenta eliminarse a sí mismo
            if (auth()->id() == $id) {
                return response()->json(['message' => 'No puedes eliminar tu propia cuenta'], 403);
            }

            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'Personal no encontrado'], 404);
            }

            $user->delete();

            return response()->json(['message' => 'Personal eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error de eliminación: ' . $th->getMessage()], 500);
        }
    }
    private function getConductorTypeId()
    {
        // Asume que tienes un método para obtener el ID del tipo de usuario "Conductor".
        return UserType::where('name', 'Conductor')->value('id');
    }
}
