<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotacaos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('oferta', 100);
            $table->string('cnpj', 20);
            $table->string('logotipo');
            $table->string('nome', 100);
            $table->string('servico', 100);
            $table->string('prazo_entrega', 100);
            $table->date('entrega_estimada');
            $table->date('validade');
            $table->decimal('custo_frete', 8, 2);
            $table->decimal('preco_frete', 8, 2);
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
        Schema::dropIfExists('cotacaos');
    }
}
