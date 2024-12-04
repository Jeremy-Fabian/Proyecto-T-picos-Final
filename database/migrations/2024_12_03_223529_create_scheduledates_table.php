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
        Schema::create('scheduledates', function (Blueprint $table) {
            $table->id();
            $table->date("date");
            $table->text("description")->nullable();
            $table->string("image")->nullable();
            $table->unsignedBigInteger("activityschedule_id");
            $table->foreign("activityschedule_id")->references("id")->on("activityschedules");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduledates');
    }
};
