<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_popup_cancellations', function (Blueprint $table) {
            $table->id()->comment('팝업 예약 취소 ID');
            $table->foreignId('reservation_popup_reservation_id')->comment('팝업 예약 ID');
            $table->string('cancel_code', 40)->unique()->comment('취소 코드');
            $table->string('cancelled_by_type', 20)->default('customer')->comment('취소자 유형');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->comment('취소 처리자 사용자 ID');
            $table->string('reason_code', 50)->nullable()->comment('취소 사유 코드');
            $table->text('reason_text')->nullable()->comment('취소 사유');
            $table->timestamp('cancelled_at')->comment('취소 일시');
            $table->string('ip_address', 45)->nullable()->comment('취소 IP');
            $table->string('user_agent', 500)->nullable()->comment('취소 User-Agent');
            $table->json('metadata')->nullable()->comment('확장 메타데이터');

            $table->foreign('reservation_popup_reservation_id', 'rpp_cancel_reservation_fk')
                ->references('id')->on('reservation_popup_reservations')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique('reservation_popup_reservation_id', 'reservation_popup_cancellations_reservation_unique');
            $table->index(['cancelled_by_type', 'cancelled_at'], 'reservation_popup_cancellations_actor_time_index');
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('reservation_popup_cancellations', function (Blueprint $table) {
                $table->comment('예약 팝업 취소 이력');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_popup_cancellations');
    }
};
