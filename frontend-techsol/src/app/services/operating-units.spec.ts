import { TestBed } from '@angular/core/testing';

import { OperatingUnits } from './operating-units';

describe('OperatingUnits', () => {
  let service: OperatingUnits;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(OperatingUnits);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
