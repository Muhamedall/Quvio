import { ComponentFixture, TestBed } from '@angular/core/testing';

import { UnpaidInvoicesCard } from './unpaid-invoices-card';

describe('UnpaidInvoicesCard', () => {
  let component: UnpaidInvoicesCard;
  let fixture: ComponentFixture<UnpaidInvoicesCard>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [UnpaidInvoicesCard],
    }).compileComponents();

    fixture = TestBed.createComponent(UnpaidInvoicesCard);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
