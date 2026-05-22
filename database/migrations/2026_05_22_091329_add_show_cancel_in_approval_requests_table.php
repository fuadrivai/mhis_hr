<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowCancelInApprovalRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('approval_requests', function (Blueprint $table) {
                $table->boolean('show_cancel')->default(true)->after('note')->comment('Indicates whether the cancel option should be shown for this request');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropColumn('show_cancel');
        });
    }
}
