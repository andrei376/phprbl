<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhite6sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('white6s', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();

            $table->string('ip1')->index();
            $table->string('ip2')->index();
            $table->string('ip3')->index();
            $table->string('ip4')->index();
            $table->string('ip5')->index();
            $table->string('ip6')->index();
            $table->string('ip7')->index();
            $table->string('ip8')->index();

            $table->char('iplong', 16)->charset('binary');

            $table->integer('mask')->default(128)->index();

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
        Schema::dropIfExists('white6s');
    }
}
