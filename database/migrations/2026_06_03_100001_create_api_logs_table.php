<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_client_id')->nullable();
            $table->foreign('api_client_id')->references('id')->on('api_clients')->onDelete('set null');
            $table->string('endpoint');
            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('status', 20)->default('success');
            $table->integer('http_status_code')->default(200);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_logs');
    }
};
