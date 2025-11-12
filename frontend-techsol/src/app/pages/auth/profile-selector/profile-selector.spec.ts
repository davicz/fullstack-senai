import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ProfileSelector } from './profile-selector';
import { HttpClientTestingModule } from '@angular/common/http/testing'; // Importe o Módulo de Teste HTTP
import { RouterTestingModule } from '@angular/router/testing'; // Importe o Módulo de Teste de Rota

describe('ProfileSelector', () => {
  let component: ProfileSelector;
  let fixture: ComponentFixture<ProfileSelector>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [
        ProfileSelector,
        HttpClientTestingModule, // Adicione aqui
        RouterTestingModule     // Adicione aqui
      ]
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