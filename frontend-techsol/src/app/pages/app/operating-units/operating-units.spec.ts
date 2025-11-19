import { ComponentFixture, TestBed } from '@angular/core/testing';

import { OperatingUnits } from './operating-units';

describe('OperatingUnits', () => {
  let component: OperatingUnits;
  let fixture: ComponentFixture<OperatingUnits>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [OperatingUnits]
    })
    .compileComponents();

    fixture = TestBed.createComponent(OperatingUnits);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
