<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('link')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->dateTime('publish_at')->default(now());
            $table->boolean('all_employees')->default(true);
            $table->boolean('send_email')->default(false);
            $table->boolean('send_push_notification')->default(true);
            $table->foreignId('created_by')->constrained('employees');
            $table->foreignId('updated_by')->nullable()->constrained('employees');
            $table->string('status')->default('published');
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
        Schema::dropIfExists('announcements');
    }
}
