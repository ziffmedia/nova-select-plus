<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('state_born_in')->nullable();
            $table->json('state_parents_born_in')->nullable();
            $table->foreignId('favorite_state_id')->nullable();
            $table->json('favorite_coffee')->nullable();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('state_id');
            $table->timestamps();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capital_city_id')->nullable();
            $table->string('name');
            $table->string('code');
            $table->timestamps();
        });

        Schema::create('state_user_lived_in', function (Blueprint $table) {
            $table->bigInteger('state_id');
            $table->bigInteger('person_id');
            $table->timestamps();
        });

        Schema::create('state_user_visited', function (Blueprint $table) {
            $table->bigInteger('state_id');
            $table->bigInteger('person_id');
            $table->integer('order');
            $table->timestamps();
        });
    }
};
