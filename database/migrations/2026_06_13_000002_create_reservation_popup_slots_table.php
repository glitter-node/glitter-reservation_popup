<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_popup_slots', function (Blueprint $table) {
            $table->id()->comment('예약 슬롯 ID');
            $table->foreignId('reservation_popup_event_id')->comment('팝업 이벤트 ID');
            $table->string('code', 80)->comment('슬롯 코드');
            $table->json('title')->nullable()->comment('다국어 슬롯명');
            $table->string('status', 20)->default('open')->comment('상태');
            $table->timestamp('starts_at')->comment('슬롯 시작 일시');
            $table->timestamp('ends_at')->comment('슬롯 종료 일시');
            $table->unsignedInteger('capacity')->comment('슬롯 정원');
            $table->unsignedInteger('reserved_count')->default(0)->comment('예약 수');
            $table->unsignedInteger('checked_in_count')->default(0)->comment('체크인 수');
            $table->unsignedInteger('sort_order')->default(0)->comment('정렬 순서');
            $table->json('settings')->nullable()->comment('슬롯 설정');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('생성자 사용자 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('수정자 사용자 ID');

            $table->foreign('reservation_popup_event_id', 'rpp_slots_event_fk')
                ->references('id')->on('reservation_popup_events')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['reservation_popup_event_id', 'code'], 'reservation_popup_slots_event_code_unique');
            $table->index(['reservation_popup_event_id', 'status', 'starts_at'], 'reservation_popup_slots_event_status_start_index');
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('reservation_popup_slots', function (Blueprint $table) {
                $table->comment('예약 팝업 슬롯');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_popup_slots');
    }
};
