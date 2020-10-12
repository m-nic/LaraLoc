<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModelTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->increments('id');

            $table->string('column_name');
            $table->string('locale');
            $table->longText('value');

            $table->integer('translatable_id')->unsigned();
            $table->string('translatable_type');

            $table->unique([
                'column_name',
                'locale',
                'translatable_id',
                'translatable_type'
            ], 'uniq_translation');

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
        Schema::dropIfExists('translations');
    }
}
