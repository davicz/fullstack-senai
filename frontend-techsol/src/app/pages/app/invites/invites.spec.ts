import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Invites } from './invites';

describe('Invites', () => {
  let component: Invites;
  let fixture: ComponentFixture<Invites>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Invites]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Invites);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
