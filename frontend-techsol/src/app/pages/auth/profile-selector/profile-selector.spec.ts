import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ProfileSelector } from './profile-selector';

describe('ProfileSelector', () => {
  let component: ProfileSelector;
  let fixture: ComponentFixture<ProfileSelector>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ProfileSelector]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ProfileSelector);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
