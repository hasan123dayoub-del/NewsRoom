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
        Schema::create("articles", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("category_id")->constrained("categories")->cascadeOnDelete();
            $table->string("title")->unique();
            $table->text("content");
            $table->enum("status", ["draft", "pending_review", "published", "rejected"])->default("draft");
            $table->timestamp("published_at")->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("articles");
    }
};
