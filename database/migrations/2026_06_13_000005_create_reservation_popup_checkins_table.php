<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_popup_checkins', function (Blueprint $table) {
            $table->id()->comment('팝업 예약 체크인 ID');
            $table->foreignId('reservation_popup_reservation_id')->comment('팝업 예약 ID');
            $table->foreignId('reservation_popup_event_id')->comment('팝업 이벤트 ID');
            $table->foreignId('reservation_popup_slot_id')->nullable()->comment('예약 슬롯 ID');
            $table->string('checkin_code', 40)->unique()->comment('체크인 코드');
            $table->string('method', 20)->default('qr')->comment('체크인 방식');
            $table->string('status', 20)->default('checked_in')->comment('체크인 상태');
            $table->timestamp('checked_in_at')->comment('체크인 일시');
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete()->comment('체크인 처리자 사용자 ID');
            $table->string('device_label', 100)->nullable()->comment('체크인 장치명');
            $table->string('ip_address', 45)->nullable()->comment('체크인 IP');
            $table->string('user_agent', 500)->nullable()->comment('체크인 User-Agent');
            $table->json('metadata')->nullable()->comment('확장 메타데이터');

            $table->foreign('reservation_popup_reservation_id', 'rpp_checkin_reservation_fk')
                ->references('id')->on('reservation_popup_reservations')
                ->cascadeOnDelete();
            $table->foreign('reservation_popup_event_id', 'rpp_checkin_event_fk')
                ->references('id')->on('reservation_popup_events')
                ->restrictOnDelete();
            $table->foreign('reservation_popup_slot_id', 'rpp_checkin_slot_fk')
                ->references('id')->on('reservation_popup_slots')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique('reservation_popup_reservation_id', 'reservation_popup_checkins_reservation_unique');
            $table->index(['reservation_popup_event_id', 'checked_in_at'], 'reservation_popup_checkins_event_time_index');
            $table->index(['reservation_popup_slot_id', 'checked_in_at'], 'reservation_popup_checkins_slot_time_index');
            $table->index(['method', 'status'], 'reservation_popup_checkins_method_status_index');
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('reservation_popup_checkins', function (Blueprint $table) {
                $table->comment('예약 팝업 QR 체크인 이력');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_popup_checkins');
    }
};
