<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activityschedules', function (Blueprint $table) {
            $table->id();
            $table->string('day', 50);
            $table->string('type', 20);
            $table->time("time_start");
            $table->time("time_end");
            $table->unsignedBigInteger("vehicle_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("activity_id");
            $table->foreign("vehicle_id")->references("id")->on("vehicles");
            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("activity_id")->references("id")->on("activities");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activityschedules');
    }
};
