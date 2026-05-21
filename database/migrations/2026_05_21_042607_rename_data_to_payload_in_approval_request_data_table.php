<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameDataToPayloadInApprovalRequestDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('approval_request_data', function (Blueprint $table) {
                $table->renameColumn('data', 'payload');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('approval_request_data', function (Blueprint $table) {
            $table->renameColumn('payload', 'data');
        });
    }
}
