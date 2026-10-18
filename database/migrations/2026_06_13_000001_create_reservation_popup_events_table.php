<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_popup_events', function (Blueprint $table) {
            $table->id()->comment('팝업 이벤트 ID');
            $table->string('slug', 120)->unique()->comment('공개 식별자');
            $table->json('title')->comment('다국어 이벤트명');
            $table->json('summary')->nullable()->comment('다국어 요약');
            $table->json('description')->nullable()->comment('다국어 설명');
            $table->string('status', 20)->default('draft')->comment('상태');
            $table->string('visibility', 20)->default('public')->comment('공개 범위');
            $table->string('timezone', 64)->default('Asia/Seoul')->comment('이벤트 시간대');
            $table->timestamp('reservation_opens_at')->nullable()->comment('예약 시작 일시');
            $table->timestamp('reservation_closes_at')->nullable()->comment('예약 종료 일시');
            $table->timestamp('starts_at')->nullable()->comment('이벤트 시작 일시');
            $table->timestamp('ends_at')->nullable()->comment('이벤트 종료 일시');
            $table->unsignedInteger('capacity')->nullable()->comment('전체 정원');
            $table->unsignedInteger('max_reservations_per_user')->default(1)->comment('사용자별 최대 예약 수');
            $table->json('settings')->nullable()->comment('이벤트 설정');
            $table->json('metadata')->nullable()->comment('확장 메타데이터');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('생성자 사용자 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('수정자 사용자 ID');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'visibility'], 'reservation_popup_events_status_visibility_index');
            $table->index(['reservation_opens_at', 'reservation_closes_at'], 'reservation_popup_events_reservation_window_index');
            $table->index(['starts_at', 'ends_at'], 'reservation_popup_events_event_window_index');
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('reservation_popup_events', function (Blueprint $table) {
                $table->comment('예약 팝업 이벤트');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_popup_events');
    }
};
