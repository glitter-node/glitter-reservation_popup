import { describe, expect, it } from 'vitest';

import adminIndex from '../../../layouts/admin/index.json';
import eventsIndex from '../../../layouts/admin/events/index.json';
import reservationsIndex from '../../../layouts/admin/reservations/index.json';
import checkinsIndex from '../../../layouts/admin/checkins/index.json';
import eventsCreate from '../../../layouts/admin/events/create.json';
import eventsShow from '../../../layouts/admin/events/show.json';
import reservationsShow from '../../../layouts/admin/reservations/show.json';

const layouts = [
  adminIndex,
  eventsIndex,
  eventsCreate,
  eventsShow,
  reservationsIndex,
  reservationsShow,
  checkinsIndex,
] as any[];

function walk(node: any, visit: (node: any) => void): void {
  if (!node || typeof node !== 'object') return;
  visit(node);
  for (const child of node.children ?? []) walk(child, visit);
  if (node.slots) {
    for (const slot of Object.values(node.slots)) {
      if (Array.isArray(slot)) slot.forEach((child) => walk(child, visit));
    }
  }
}

function actionsOf(layout: any): any[] {
  const actions: any[] = [];
  walk(layout, (node) => {
    for (const action of node.actions ?? []) actions.push(action);
  });
  return actions;
}

describe('reservation popup admin layouts', () => {
  it('관리자 메인 렌더링에 필요한 기본 구조가 있다', () => {
    expect(adminIndex.extends).toBe('_admin_base');
    expect(adminIndex.layout_name).toBe('admin/index');
    expect(JSON.stringify(adminIndex)).toContain('glitter-reservation_popup.admin.summary.total_events');
  });

  it('이벤트 목록 렌더링에 필요한 data source가 있다', () => {
    const events = (eventsIndex as any).data_sources.find((source: any) => source.id === 'events');
    expect(events.endpoint).toBe('/api/modules/glitter-reservation_popup/admin/events');
    expect(JSON.stringify(eventsIndex)).toContain('{{events?.data?.data ?? []}}');
  });

  it('예약 목록은 관리자 전체 예약 API와 필터 계약을 사용한다', () => {
    const reservations = (reservationsIndex as any).data_sources.find((source: any) => source.id === 'reservations');
    expect(reservations.endpoint).toBe('/api/modules/glitter-reservation_popup/admin/reservations');
    expect(reservations.params).toMatchObject({
      event_id: '{{query.event_id}}',
      status: '{{query.status}}',
      date_from: '{{query.date_from}}',
      date_to: '{{query.date_to}}',
      keyword: '{{query.keyword}}',
    });
    expect(reservations.if).toBeUndefined();
    expect(JSON.stringify(reservationsIndex)).toContain('{{reservations?.data?.data ?? []}}');
  });

  it('체크인 폼 렌더링에 필요한 입력과 실행 액션이 있다', () => {
    expect(JSON.stringify(checkinsIndex)).toContain('reservation_code');
    const apiActions = actionsOf(checkinsIndex).filter((action) => action.handler === 'apiCall');
    expect(apiActions.some((action) => action.target === '/api/modules/glitter-reservation_popup/admin/checkins')).toBe(true);
  });

  it('모든 모듈 API data_source는 Accept application/json 헤더를 포함한다', () => {
    for (const layout of layouts) {
      for (const source of layout.data_sources ?? []) {
        if (source.endpoint?.includes('/api/modules/glitter-reservation_popup/')) {
          expect(source.headers?.Accept).toBe('application/json');
        }
      }
    }
  });

  it('apiCall의 target/onSuccess/onError는 top-level에 둔다', () => {
    for (const layout of layouts) {
      for (const action of actionsOf(layout).filter((item) => item.handler === 'apiCall')) {
        expect(action.target).toBeDefined();
        expect(action.params?.target).toBeUndefined();
        expect(action.params?.onSuccess).toBeUndefined();
        expect(action.params?.onError).toBeUndefined();
      }
    }
  });
});
