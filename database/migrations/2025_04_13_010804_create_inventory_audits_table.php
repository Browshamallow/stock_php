<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('current_stock');
        });
        
        Schema::table('supplies', function (Blueprint $table) {
            $table->index('created_at');
        });
        
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['current_stock']);
        });
        
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
        
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};