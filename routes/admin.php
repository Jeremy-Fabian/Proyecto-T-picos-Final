<?php

use App\Http\Controllers\admin\ActivityController;
use App\Http\Controllers\admin\ActivityscheduleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\BrandmodelController;
use App\Http\Controllers\admin\RouteController;
use App\Http\Controllers\admin\ScheduleController;
use App\Http\Controllers\admin\ScheduledateController;
use App\Http\Controllers\admin\SectorController;
use App\Http\Controllers\admin\VehicleController;
use App\Http\Controllers\admin\VehicleimagesController;
use App\Http\Controllers\admin\VehiclerouteController;
use App\Http\Controllers\admin\ZoneController;
use App\Http\Controllers\admin\ZonecoordController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\UserTypesController;
use Laravel\Jetstream\Rules\Role;

Route::resource('brands', BrandController::class)->names('admin.brands');
Route::resource('models', BrandmodelController::class)->names('admin.models');
Route::resource('vehicles', VehicleController::class)->names('admin.vehicles');
Route::resource('vehicleimages', VehicleimagesController::class)->names('admin.vehicleimages');
Route::get('modelsbybrand/{id}', [BrandmodelController::class, 'modelsbybrand'])->name('admin.modelsbybrand');
Route::get('imageprofile/{id}/{vehicle_id}', [VehicleimagesController::class, 'profile'])->name('admin.imageprofile');
Route::resource('zones', ZoneController::class)->names('admin.zones');


Route::resource('zonecoords', ZonecoordController::class)->names('admin.zonecoords');
Route::resource('sectors', SectorController::class)->names('admin.sectors');

Route::resource('routes', RouteController::class)->names('admin.routes');
Route::resource('schedules', ScheduleController::class)->names('admin.schedules');
Route::resource('vehicleroutes', VehiclerouteController::class)->names('admin.vehicleroutes');

Route::get('editAllPrograms', [VehiclerouteController::class, 'editAllProgram'])->name('admin.editAllPrograms');
Route::get('getVehicleDates/{id}', [VehiclerouteController::class, 'getVehicleDates'])->name('admin.getVehicleDates');
Route::post('updateAllPrograms', [VehiclerouteController::class, 'updateAllProgram'])->name('admin.updateAllPrograms');

Route::resource('usertypes',UserTypesController::class)->names('admin.usertypes');

Route::resource('users',UserController::class)->names('admin.users');










Route::resource('activities', ActivityController::class)->names('admin.activities');

Route::get('admin/activityschedules/register/{id}', [ActivityscheduleController::class, 'register'])->name('admin.activityschedules.register');
Route::resource('activityschedules', ActivityscheduleController::class)->names('admin.activityschedules');

Route::resource('scheduledates', ScheduledateController::class)->names('admin.scheduledates');


Route::get('userdriver/{id}', [ActivityscheduleController::class, 'userdriver'])->name('admin.userdriver');