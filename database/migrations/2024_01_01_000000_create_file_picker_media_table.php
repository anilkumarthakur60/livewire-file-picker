<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_picker_media', function (Blueprint $table): void {
            $table->id();
            $table->string('filename');
            $table->string('disk', 50)->default('public');
            $table->string('directory')->default('media');
            $table->string('path');
            $table->string('extension', 20)->index();
            $table->string('mime_type', 100)->index();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('alt')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('custom_properties')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('filename');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_picker_media');
    }
};
