<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Brandmodel;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vehiclecolor;
use App\Models\Vehicleimage;
use App\Models\Vehicleocuppant;
use App\Models\Vehicleroute;
use App\Models\Vehicletype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class VehicleocuppantController extends Controller
{
    public function datos(int $id)
    {
        $vehicle = Vehicle::find($id);
        $capacity = $vehicle->occupant_capacity;

        $ocuppantes = Vehicleocuppant::join('users', 'users.id', 'vehicleocuppants.user_id')->join('usertypes', 'usertypes.id', '=', 'users.usertype_id')
            ->where('vehicleocuppants.vehicle_id', $id)->where('vehicleocuppants.status', 0)->select('vehicleocuppants.*', 'users.name AS name_user', 'usertypes.name AS name_tipo')->get();

        $contC = $ocuppantes->where('name_tipo', 'CONDUCTOR')->count();
        $contNoC = $ocuppantes->where('name_tipo', '!=', 'CONDUCTOR')->count();

        $ocupantesUserIds = $ocuppantes->pluck('user_id')->toArray();

        if ($ocuppantes->count() < $capacity) {
            $listaSeletector = User::join('usertypes', 'usertypes.id', '=', 'users.usertype_id')
                ->select('users.id', DB::raw("CONCAT(users.name, ' - ', usertypes.name) as name_tipo"))
                ->where('usertypes.name', ($contC === 0 ? '=' : '!='), 'CONDUCTOR')
                ->whereNotIn('users.id', $ocupantesUserIds)
                ->pluck('name_tipo', 'id');
        } else {
            $listaSeletector = [];
        }

        return [
            'listaSeletector' => $listaSeletector,
            'vehicle' => $vehicle,
            'capacity' => $capacity,
            'contC' => $contC,
            'contNoC' => $contNoC,
            'ocuppantes' => $ocuppantes
        ];
    }
    public function cargar(int $id)
    {
        $datos = $this->datos($id);
        $vehicle = $datos['vehicle'];
        $capacity = $datos['capacity'];
        $contC = $datos['contC'];
        $contNoC = $datos['contNoC'];
        $ocuppantes = $datos['ocuppantes'];
        $listaSeletector = $datos['listaSeletector'];

        return view('admin.vehicleocuppants.cargar', compact("listaSeletector", "vehicle", "capacity", "contC", "contNoC", "ocuppantes"));
    }

    public function store(Request $request)
    {
        try {
            $user = User::find(!isset($request->no_conductor_id) ? $request->conductor_id : $request->no_conductor_id);
            $existingOccupant = Vehicleocuppant::where('vehicle_id', $request->vehicle_id)
                ->where('user_id', $user->id)->where('status', 0)
                ->first();

            if ($existingOccupant) {
                $existingOccupant->usertype_id = $user->usertype_id;
                $existingOccupant->save();
            } else {
                $data = [
                    'vehicle_id' => $request->vehicle_id,
                    'user_id' => $user->id,
                    'usertype_id' => $user->usertype_id
                ];
                Vehicleocuppant::create($data);
            }
            $datos = $this->datos($request->vehicle_id);
            return response()->json([
                'message' => 'Ocupante registrado correctamente',
                'datos' => $datos
            ], 200);
        } catch (\Throwable $th) {

            return response()->json(['message' => 'Error en el registro: ' . $th->getMessage()], 500);
        }
    }

    public function destroyer(string $id)
    {

        try {
            $occupante = Vehicleocuppant::findOrFail($id);
            $occupante->status = Vehicleocuppant::INACTIVO;
            $occupante->save();


            $datos = $this->datos($occupante->vehicle_id);

            return response()->json([
                'message' => 'Ocupante eliminado correctamente',
                'datos' => $datos
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
