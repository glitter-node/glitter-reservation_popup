<?php

namespace Modules\Glitter\ReservationPopup;

use App\Extension\AbstractModule;

class Module extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            'api' => $this->getModulePath().'/src/routes/api.php',
        ];
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getPermissions(): array
    {
        return [
            'name' => [
                'ko' => '예약 팝업',
                'en' => 'Reservation Popup',
            ],
            'description' => [
                'ko' => '팝업 이벤트 예약 모듈 권한',
                'en' => 'Popup event reservation module permissions',
            ],
            'categories' => [
                [
                    'identifier' => 'events',
                    'resource_route_key' => 'reservation_popup_event',
                    'owner_key' => 'created_by',
                    'name' => ['ko' => '팝업 이벤트', 'en' => 'Popup Events'],
                    'description' => ['ko' => '팝업 이벤트 관리 권한', 'en' => 'Popup event management permissions'],
                    'permissions' => [
                        [
                            'action' => 'view',
                            'name' => ['ko' => '이벤트 조회', 'en' => 'View Events'],
                            'description' => ['ko' => '팝업 이벤트 조회', 'en' => 'View popup events'],
                            'type' => 'admin',
                            'roles' => ['admin', 'manager'],
                        ],
                        [
                            'action' => 'create',
                            'name' => ['ko' => '이벤트 생성', 'en' => 'Create Events'],
                            'description' => ['ko' => '팝업 이벤트 생성', 'en' => 'Create popup events'],
                            'type' => 'admin',
                            'roles' => ['admin', 'manager'],
                        ],
                        [
                            'action' => 'update',
                            'name' => ['ko' => '이벤트 수정', 'en' => 'Update Events'],
                            'description' => ['ko' => '팝업 이벤트 수정', 'en' => 'Update popup events'],
                            'type' => 'admin',
                            'roles' => ['admin', 'manager'],
                        ],
                        [
                            'action' => 'delete',
                            'name' => ['ko' => '이벤트 삭제', 'en' => 'Delete Events'],
                            'description' => ['ko' => '팝업 이벤트 삭제', 'en' => 'Delete popup events'],
                            'type' => 'admin',
                            'roles' => ['admin'],
                        ],
                    ],
                ],
                [
                    'identifier' => 'slots',
                    'resource_route_key' => 'reservation_popup_slot',
                    'owner_key' => 'created_by',
                    'name' => ['ko' => '예약 슬롯', 'en' => 'Reservation Slots'],
                    'description' => ['ko' => '예약 슬롯 관리 권한', 'en' => 'Reservation slot management permissions'],
                    'permissions' => [
                        [
                            'action' => 'manage',
                            'name' => ['ko' => '슬롯 관리', 'en' => 'Manage Slots'],
                            'description' => ['ko' => '예약 슬롯 생성, 수정, 삭제', 'en' => 'Create, update, and delete reservation slots'],
                            'type' => 'admin',
                            'roles' => ['admin', 'manager'],
                        ],
                    ],
                ],
                [
                    'identifier' => 'reservations',
                    'resource_route_key' => 'reservation_popup_reservation',
                    'owner_key' => 'created_by',
                    'name' => ['ko' => '예약', 'en' => 'Reservations'],
                    'description' => ['ko' => '팝업 예약 관리 권한', 'en' => 'Popup reservation management permissions'],
                    'permissions' => [
                        [
                            'action' => 'view',
                            'name' => ['ko' => '예약 조회', 'en' => 'View Reservations'],
                            'description' => ['ko' => '팝업 예약 조회', 'en' => 'View popup reservations'],
                            'type' => 'admin',
                            'roles' => ['admin', 'manager'],
                        ],
                        [
                            'action' => 'update',
                            'name' => ['ko' => '예약 수정', 'en' => 'Update Reservations'],
                            'description' => ['ko' => '팝업 예약 상태 및 메모 수정', 'en' => 'Update popup reservation status and notes'],
                            'type' => 'admin',
                            'roles' => ['admin', 'manager'],
                        ],
                    ],
                ],
                [
                    'identifier' => 'checkins',
                    'resource_route_key' => 'reservation_popup_checkin',
                    'owner_key' => 'checked_in_by',
                    'name' => ['ko' => 'QR 체크인', 'en' => 'QR Check-ins'],
                    'description' => ['ko' => 'QR 체크인 처리 권한', 'en' => 'QR check-in processing permissions'],
                    'permissions' => [
                        [
                            'action' => 'manage',
                            'name' => ['ko' => '체크인 관리', 'en' => 'Manage Check-ins'],
                            'description' => ['ko' => 'QR 체크인 처리 및 조회', 'en' => 'Process and view QR check-ins'],
                            'type' => 'admin',
                            'roles' => ['admin', 'manager'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getConfig(): array
    {
        return [
            'reservation_popup' => $this->getModulePath().'/config/reservation_popup.php',
        ];
    }

    public function getHookListeners(): array
    {
        return [];
    }

    public function getAdminMenus(): array
    {
        return [
            [
                'name' => [
                    'ko' => '반짝이 예약 팝업 관리',
                    'en' => 'Glitter Reservation Popup Management',
                ],
                'slug' => 'reservation-popup',
                'url' => (string) config('reservation_popup.admin_page_path', '/admin/reservation-popups'),
                'icon' => 'fas fa-calendar-check',
                'order' => 36,
                'permission' => 'glitter-reservation_popup.events.view',
            ],
        ];
    }
}
