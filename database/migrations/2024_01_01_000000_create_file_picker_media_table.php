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
            $table->unsignedInteger('duration')->nullable();
            $table->string('hash', 64)->nullable()->index();
            $table->string('folder')->nullable()->index();
            $table->json('tags')->nullable();
            $table->boolean('is_favorite')->default(false)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('download_count')->default(0);
            $table->json('custom_properties')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
            $table->index('filename');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_picker_media');
    }
};
