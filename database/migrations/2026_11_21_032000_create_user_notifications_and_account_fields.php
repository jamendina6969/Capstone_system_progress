<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'avatar_data')) {
                $table->longText('avatar_data')->nullable()->after('theme');
            }
            if (!Schema::hasColumn('users', 'avatar_mime')) {
                $table->string('avatar_mime', 50)->nullable()->after('avatar_data');
            }
            if (!Schema::hasColumn('users', 'preferences')) {
                $table->json('preferences')->nullable()->after('avatar_mime');
            }
        });

        if (!Schema::hasTable('user_notifications')) {
            Schema::create('user_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('type', 64)->index();
                $table->string('category', 40)->default('system')->index();
                $table->string('severity', 20)->default('info');
                $table->string('title', 180);
                $table->text('message');
                $table->string('action_url', 255)->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('read_at')->nullable()->index();
                $table->timestamps();

                $table->index(['user_id', 'read_at', 'created_at'], 'user_notif_unread_idx');
                $table->index(['user_id', 'category', 'created_at'], 'user_notif_cat_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');

        Schema::table('users', function (Blueprint $table) {
            foreach (['preferences', 'avatar_mime', 'avatar_data'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
