<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('sets', static function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('dess', static function(Blueprint $table) {
           $table->id();
           $table->string('name');
           $table->timestamps();
        });

        Schema::create('brands', static function(Blueprint $table) {
           $table->id();
           $table->string('name');
           $table->timestamps();
        });

        Schema::create('calibrations', static function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
         });

         Schema::create('locations', static function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
         });


        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('item')->nullable();
            $table->string('set_id')->nullable()->constrained();;
            $table->foreignId('des_id')->constrained();
            $table->foreignId('brand_id')->constrained();
            $table->foreignId('calibration_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->decimal('quantity');
            $table->string('measurement');
            $table->string('serial_number')->unique()->nullable();
            $table->string('spect');
            $table->string('model')->nullable();
            $table->string('shelf_localization')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tools');
        Schema::dropIfExists('sets');
        Schema::dropIfExists('dess');
        Schema::dropIfExists('calibrations');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('brands');
    }
}
