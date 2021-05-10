<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefineListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('define_lists', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();

            $table->string('name')->unique();

            $table->string('email');

            $table->string('expire');

            $table->string('host');

            $table->string('list');

            $table->string('minttl');

            $table->string('nss');

            $table->string('primaryns');

            $table->string('refresh');

            $table->string('retry');

            $table->string('soansttl');

            $table->integer('currentsn')->default(1);
            $table->integer('lastsn')->default(0);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('define_lists');
    }
}
