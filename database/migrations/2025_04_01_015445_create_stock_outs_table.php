<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/xxxx_xx_xx_create_stock_outs_table.php

Schema::create('stock_outs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')
          ->constrained()
          ->onDelete('cascade');
    $table->integer('quantity')->unsigned();
    $table->string('reason');
    $table->date('date')->nullable(); // Champ nullable
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_outs');
    }
};
