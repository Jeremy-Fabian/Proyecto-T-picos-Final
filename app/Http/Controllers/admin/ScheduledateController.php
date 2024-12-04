<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Scheduledate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ScheduledateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            
            $selectedDate = $request->input('date');
            $dayOfWeekEnglish  = date('l', strtotime($selectedDate));

            $daysMap = [
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado',
                'Sunday' => 'Domingo',
            ];

            $dayOfWeek = $daysMap[$dayOfWeekEnglish] ?? 'Día desconocido';

            $activitySchedule = DB::table('activityschedules')
                ->where('id', $request->input('activityschedule_id'))
                ->first();


            if (strtolower($dayOfWeek) !== strtolower($activitySchedule->day)) {
                return response()->json([
                    'message' => "El día seleccionado ($dayOfWeek) no coincide con el día registrado ({$activitySchedule->day})."
                ], 400);
            }else{
                $isDateValid = DB::select('SELECT validate_date_within_activity_range(?, ?)', [
                    $request->input('activityschedule_id'),
                    $request->input('date')
                ]);

                if ($isDateValid[0]->validate_date_within_activity_range) {
                    if ($request->image != "") {
                        $image = $request->file("image")->store("public/horario_images/");
                        $urlImage = Storage::url($image);
                        Scheduledate::create([
                            "date" => $request->input('date'),
                            "description" => $request->input('description'),
                            "image" => $urlImage,
                            "activityschedule_id" => $request->input('activityschedule_id'),
                        ]);
                    }else{
                        Scheduledate::create([
                            "date" => $request->input('date'),
                            "description" => $request->input('description'),
                            "activityschedule_id" => $request->input('activityschedule_id'),
                        ]);
                    }
                    return response()->json(['message' => 'Fecha registrada correctamente.'], 200);
                } else {
                    return response()->json(['message' => 'La fecha no está dentro del rango de la actividad.'], 400);
                }
            }

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al registrar la fecha: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {

        $scheduledates = Scheduledate::where('activityschedule_id', $id)->get();

        if ($request->ajax()) {

            return DataTables::of($scheduledates)
                ->addColumn('img', function ($scheduledate) {
                    return '<img src="' . ($scheduledate->image == '' ? asset('storage/horario_images/no_image.png') : asset($scheduledate->image)) . '" width="100px" height="70px" class="card">';
                })
                ->rawColumns(['img'])  // Declarar columnas que contienen HTML
                ->make(true);
        } else {
            return view('admin.scheduledates.create', compact('scheduledates'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin.scheduledates.create')->with('activityschedule_id', $id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
