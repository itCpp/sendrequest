<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFailedSendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('failed_sends')) {

            Schema::create('failed_sends', function (Blueprint $table) {
                $table->id();
                $table->integer('request_count')->default(0)->comment("Количество попыток отправки запроса");
                $table->json('request_data')->nullable()->comment("Исходящий запрос");
                $table->integer('response_code')->nullable()->comment("Код ответа отправки запроса");
                $table->json('response_data')->nullable()->comment("Тело ответа");
                $table->dateTime('fail_at')->nullable()->comment("Время последней неудачной попытки");
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failed_sends');
    }
}
