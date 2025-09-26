import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, ActivatedRoute, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { HttpClient } from '@angular/common/http'; // Para buscar o CEP
import { NgxMaskDirective } from 'ngx-mask';

import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatProgressSpinnerModule,
    NgxMaskDirective
  ],
  templateUrl: './register.html',
  styleUrl: './register.scss'
})
export class Register implements OnInit {
  registerForm: FormGroup;
  token: string | null = null;
  isLoading = false;
  errorMessage: string | null = null;
  successMessage: string | null = null;

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private authService: AuthService,
    private http: HttpClient // Injectando o HttpClient
  ) {
    this.registerForm = this.fb.group({
      token: ['', Validators.required],
      name: ['', Validators.required],
      cpf: ['', {
        validators: [Validators.required, Validators.minLength(11), Validators.maxLength(11)],
        updateOn: 'blur'
      }],
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required],
      cep: ['', [Validators.required, Validators.minLength(8), Validators.maxLength(8)]], // <-- AQUI ESTÁ A MUDANÇA
      address: ['', Validators.required],
      city: ['', Validators.required],
      state: ['', Validators.required]
    }, { validators: this.checkPasswords });
  }

  ngOnInit(): void {
    // Pegando o token da URL
    this.route.params.subscribe(params => {
      this.token = params['token'];
      this.registerForm.patchValue({ token: this.token }); // Preenche o campo hidden
    });
  }

  checkPasswords(group: FormGroup) { // Validação cruzada para as senhas
    const pass = group.controls['password'].value;
    const confirmPass = group.controls['password_confirmation'].value;
    return pass === confirmPass ? null : { notSame: true }
  }

  searchCep() {
    const cep = this.registerForm.get('cep')?.value.replace(/\D/g, ''); // Remove caracteres não numéricos
    if (cep && cep.length === 8) {
      console.log(`Tentando buscar CEP na URL: https://viacep.com.br/ws/${cep}/json/`);
      this.isLoading = true;
      this.http.get(`https://viacep.com.br/ws/${cep}/json/`).subscribe({
        next: (data: any) => {
          if (!data.erro) {
            this.registerForm.patchValue({
              address: data.logradouro,
              city: data.localidade,
              state: data.uf
            });
          } else {
            this.errorMessage = 'CEP não encontrado.';
            this.registerForm.patchValue({
              address: '',
              city: '',
              state: ''
            });
          }
          this.isLoading = false;
        },
        error: () => {
          this.errorMessage = 'Erro ao buscar CEP.';
          this.isLoading = false;
        }
      });
    } else if (cep) {
      this.registerForm.patchValue({
        address: '',
        city: '',
        state: ''
      });
    }
  }

  onSubmit() {
    if (this.registerForm.invalid) {
      return;
    }

    this.isLoading = true;
    this.errorMessage = null;

    this.authService.register(this.registerForm.value).subscribe({
      next: (response) => {
        console.log('Cadastro bem-sucedido!', response);
        this.isLoading = false;
        this.router.navigate(['/login'], { queryParams: { registered: true } });
      },
      error: (err) => {
        console.error('Erro no cadastro', err);
        this.errorMessage = 'Ocorreu um erro ao realizar o cadastro. Verifique o token ou tente novamente mais tarde.';
        this.isLoading = false;
      }
    });
  }
}
