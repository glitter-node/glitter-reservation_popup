<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_popup_reservations', function (Blueprint $table) {
            $table->id()->comment('팝업 예약 ID');
            $table->foreignId('reservation_popup_event_id')->comment('팝업 이벤트 ID');
            $table->foreignId('reservation_popup_slot_id')->nullable()->comment('예약 슬롯 ID');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('회원 ID');
            $table->string('reservation_code', 40)->unique()->comment('예약 코드');
            $table->string('qr_token_hash', 128)->unique()->comment('QR 토큰 해시');
            $table->string('customer_name', 100)->comment('예약자 이름');
            $table->string('customer_phone', 30)->comment('예약자 연락처');
            $table->string('customer_email', 150)->nullable()->comment('예약자 이메일');
            $table->unsignedInteger('guest_count')->default(1)->comment('예약 인원 수');
            $table->string('status', 20)->default('pending')->comment('예약 상태');
            $table->text('request_memo')->nullable()->comment('예약 요청 메모');
            $table->text('admin_memo')->nullable()->comment('관리자 메모');
            $table->timestamp('confirmed_at')->nullable()->comment('확정 일시');
            $table->timestamp('cancelled_at')->nullable()->comment('취소 일시');
            $table->timestamp('expires_at')->nullable()->comment('예약 만료 일시');
            $table->string('ip_address', 45)->nullable()->comment('신청 IP');
            $table->string('user_agent', 500)->nullable()->comment('신청 User-Agent');
            $table->json('metadata')->nullable()->comment('확장 메타데이터');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('생성자 사용자 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('수정자 사용자 ID');

            $table->foreign('reservation_popup_event_id', 'rpp_res_event_fk')
                ->references('id')->on('reservation_popup_events')
                ->restrictOnDelete();
            $table->foreign('reservation_popup_slot_id', 'rpp_res_slot_fk')
                ->references('id')->on('reservation_popup_slots')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reservation_popup_event_id', 'status'], 'reservation_popup_reservations_event_status_index');
            $table->index(['reservation_popup_slot_id', 'status'], 'reservation_popup_reservations_slot_status_index');
            $table->index(['customer_phone', 'reservation_popup_event_id'], 'reservation_popup_reservations_phone_event_index');
            $table->index(['customer_email', 'reservation_popup_event_id'], 'reservation_popup_reservations_email_event_index');
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('reservation_popup_reservations', function (Blueprint $table) {
                $table->comment('예약 팝업 예약');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_popup_reservations');
    }
};
