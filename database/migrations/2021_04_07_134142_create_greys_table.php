<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGreysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('greys', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();

            $table->integer('ip1')->index();
            $table->integer('ip2')->index();
            $table->integer('ip3')->index();
            $table->integer('ip4')->index();

            $table->integer('iplong', false, true);

            $table->integer('mask')->default(32)->index();

            $table->string('inetnum')->nullable();
            $table->string('netname')->nullable();
            $table->string('country')->nullable();
            $table->string('orgname')->nullable();
            $table->string('geoipcountry')->nullable();

            $table->tinyInteger('delete')->default(0)->index();
            $table->tinyInteger('active')->default(0)->index();

            $table->timestamp('date_added')->useCurrent();
            $table->timestamp('last_check')->nullable()->index();

            $table->tinyInteger('checked')->default(0)->index();

            $table->unique(['iplong', 'mask']);

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
        Schema::dropIfExists('greys');
    }
}
