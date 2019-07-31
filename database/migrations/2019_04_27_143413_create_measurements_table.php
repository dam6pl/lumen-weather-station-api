<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeasurementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('measurements', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('station_id');
            $table->float('temperature')
                ->nullable();
            $table->float('pressure')
                ->nullable();
            $table->float('humidity')
                ->nullable();
            $table->float('illuminance')
                ->nullable();
            $table->boolean('is_active')
                ->default(true);
            $table->timestamps();

            $table->foreign('station_id')->references('id')->on('stations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
}
