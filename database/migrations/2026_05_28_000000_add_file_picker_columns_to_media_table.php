<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('media')) {
            return;
        }

        Schema::table('media', function (Blueprint $table): void {
            if (! Schema::hasColumn('media', 'folder')) {
                $table->string('folder')->nullable()->index();
            }

            if (! Schema::hasColumn('media', 'tags')) {
                $table->json('tags')->nullable();
            }

            if (! Schema::hasColumn('media', 'is_favorite')) {
                $table->boolean('is_favorite')->default(false)->index();
            }

            if (! Schema::hasColumn('media', 'alt')) {
                $table->string('alt')->nullable();
            }

            if (! Schema::hasColumn('media', 'hash')) {
                $table->string('hash', 64)->nullable()->index();
            }

            if (! Schema::hasColumn('media', 'width')) {
                $table->unsignedInteger('width')->nullable();
            }

            if (! Schema::hasColumn('media', 'height')) {
                $table->unsignedInteger('height')->nullable();
            }

            if (! Schema::hasColumn('media', 'duration')) {
                $table->unsignedInteger('duration')->nullable();
            }

            if (! Schema::hasColumn('media', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index();
            }

            if (! Schema::hasColumn('media', 'download_count')) {
                $table->unsignedBigInteger('download_count')->default(0);
            }

            if (! Schema::hasColumn('media', 'custom_properties')) {
                $table->json('custom_properties')->nullable();
            }

            if (! Schema::hasColumn('media', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('media')) {
            return;
        }

        $indexedColumns = ['folder', 'is_favorite', 'hash', 'user_id'];

        foreach ($indexedColumns as $column) {
            if (Schema::hasColumn('media', $column)) {
                try {
                    Schema::table('media', function (Blueprint $table) use ($column): void {
                        $table->dropIndex('media_'.$column.'_index');
                    });
                } catch (Throwable) {
                    // Index may not exist (idempotent down)
                }
            }
        }

        Schema::table('media', function (Blueprint $table): void {
            $columns = [
                'folder',
                'tags',
                'is_favorite',
                'hash',
                'width',
                'height',
                'duration',
                'user_id',
                'download_count',
                'custom_properties',
                'deleted_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('media', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
