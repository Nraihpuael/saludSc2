<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatesToMapasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mapas', function (Blueprint $table) {
            $table->date('fecha_ini')->nullable();
            $table->date('fecha_fin')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mapas', function (Blueprint $table) {
            $table->dropColumn('fecha_ini');
            $table->dropColumn('fecha_fin');
        });
    }
}
