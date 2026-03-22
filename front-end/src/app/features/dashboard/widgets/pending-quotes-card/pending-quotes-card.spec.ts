import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PendingQuotesCard } from './pending-quotes-card';

describe('PendingQuotesCard', () => {
  let component: PendingQuotesCard;
  let fixture: ComponentFixture<PendingQuotesCard>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PendingQuotesCard],
    }).compileComponents();

    fixture = TestBed.createComponent(PendingQuotesCard);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
