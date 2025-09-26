import { ComponentFixture, TestBed } from '@angular/core/testing';

import { UserDetailedDialog } from './user-detailed-dialog';

describe('UserDetailedDialog', () => {
  let component: UserDetailedDialog;
  let fixture: ComponentFixture<UserDetailedDialog>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [UserDetailedDialog]
    })
    .compileComponents();

    fixture = TestBed.createComponent(UserDetailedDialog);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
