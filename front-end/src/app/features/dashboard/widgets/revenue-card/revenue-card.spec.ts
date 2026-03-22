import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RevenueCard } from './revenue-card';

describe('RevenueCard', () => {
  let component: RevenueCard;
  let fixture: ComponentFixture<RevenueCard>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RevenueCard],
    }).compileComponents();

    fixture = TestBed.createComponent(RevenueCard);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
