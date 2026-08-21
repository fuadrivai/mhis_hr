<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalRequestAttendances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('approval_request_attendances', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('approval_request_id');

        $table->foreign('approval_request_id')
            ->references('id')
            ->on('approval_requests')
            ->onDelete('cascade');

        $table->unsignedBigInteger('attendance_id');

        $table->foreign('attendance_id')
            ->references('id')
            ->on('attendances')
            ->onDelete('cascade');

        $table->timestamps();

        $table->unique(
            ['approval_request_id', 'attendance_id'],
            'req_attendance_unique'
        );
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_request_attendances');
    }
}
