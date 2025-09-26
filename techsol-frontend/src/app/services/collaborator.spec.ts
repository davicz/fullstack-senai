import { TestBed } from '@angular/core/testing';

import { Collaborator } from './collaborator';

describe('Collaborator', () => {
  let service: Collaborator;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(Collaborator);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
