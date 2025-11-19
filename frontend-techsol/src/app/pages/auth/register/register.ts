import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Auth } from '../../../services/auth'; // Ajuste o caminho conforme sua pasta

@Component({
  selector: 'app-register',
  standalone: true, // Se seu projeto for standalone (Angular 14+), senão remova e declare no module
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './register.html',
  styleUrls: ['./register.css']
})
export class RegisterComponent implements OnInit {
  currentStep = 1;
  registerForm: FormGroup;
  token: string = '';
  invitationData: any = null;
  isLoading = true;
  errorMessage = '';

  // Opções para os selects (pode vir do banco futuramente)
  educationLevels = ['Ensino Fundamental', 'Ensino Médio', 'Ensino Superior', 'Pós-graduação'];
  interestAreas = ['Tecnologia', 'Gestão', 'Industrial', 'Educação'];

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private auth: Auth,
    private http: HttpClient
  ) {
    this.registerForm = this.fb.group({
      // PASSO 1 - Pessoais
      name: ['', [Validators.required, Validators.minLength(3)]],
      password: ['', [Validators.required, Validators.minLength(8)]],
      confirmPassword: ['', Validators.required],
      cpf: [{value: '', disabled: true}], // Readonly
      email: [{value: '', disabled: true}], // Readonly

      // PASSO 2 - Contato
      phone: ['', Validators.required],
      cep: ['', Validators.required],
      address: ['', Validators.required],
      city: ['', Validators.required],
      uf: ['', [Validators.required, Validators.maxLength(2)]],

      // PASSO 3 - Acadêmico
      education_level: ['', Validators.required],
      interest_area: ['', Validators.required],
      interest_course: [''] // Opcional
    });
  }

  // Adicione essa função na classe
    consultarCep() {
    const cep = this.registerForm.get('cep')?.value;

    // Remove caracteres não numéricos
    const cepLimpo = cep?.replace(/\D/g, '');

    if (cepLimpo && cepLimpo.length === 8) {
        this.isLoading = true; // Opcional: mostrar loading rápido
        
        this.http.get<any>(`https://viacep.com.br/ws/${cepLimpo}/json/`).subscribe({
        next: (dados) => {
            this.isLoading = false;
            if (!dados.erro) {
            this.registerForm.patchValue({
                address: dados.logradouro, // Rua
                city: dados.localidade,    // Cidade
                uf: dados.uf               // Estado
                // Se quiser bairro: neighborhood: dados.bairro
            });
            } else {
            alert('CEP não encontrado.');
            }
        },
        error: () => {
            this.isLoading = false;
            console.error('Erro ao consultar CEP');
        }
        });
    }
    }

  ngOnInit(): void {
    // Pega o token da URL: ex: /register?token=xyz123
    this.token = this.route.snapshot.queryParamMap.get('token') || '';

    if (!this.token) {
      this.errorMessage = 'Token de convite não encontrado.';
      this.isLoading = false;
      return;
    }

    this.auth.checkInvitationToken(this.token).subscribe({
      next: (data) => {
        this.invitationData = data;
        // Preenche os campos travados com o que veio do convite
        this.registerForm.patchValue({
          email: data.email,
          cpf: data.cpf
        });
        this.isLoading = false;
      },
      error: (err) => {
        console.error(err);
        this.errorMessage = 'Este convite é inválido ou já expirou.';
        this.isLoading = false;
      }
    });
  }

  nextStep() {
    // Validação simples por etapa antes de avançar
    if (this.currentStep === 1) {
      const p1Valid = this.registerForm.get('name')?.valid && 
                      this.registerForm.get('password')?.valid && 
                      this.passwordsMatch();
      if (!p1Valid) {
        this.registerForm.markAllAsTouched();
        return;
      }
    }
    
    // Adicione validações para step 2 e 3 se quiser ser rigoroso antes de avançar
    if (this.currentStep < 4) {
      this.currentStep++;
    }
  }

  prevStep() {
    if (this.currentStep > 1) {
      this.currentStep--;
    }
  }

  submit() {
    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      return;
    }

    this.isLoading = true;

    // Monta o objeto final. .getRawValue() pega até os campos disabled (cpf/email)
    const formData = this.registerForm.getRawValue();
    
    const payload = {
      token: this.token,
      ...formData
    };

    this.auth.register(payload).subscribe({
      next: () => {
        alert('Cadastro realizado com sucesso! Você será redirecionado para o login.');
        this.router.navigate(['/auth/login']); // Ajuste para sua rota de login
      },
      error: (err) => {
        this.isLoading = false;
        alert('Erro ao cadastrar: ' + (err.error.message || 'Erro desconhecido.'));
      }
    });
  }

  passwordsMatch(): boolean {
    return this.registerForm.get('password')?.value === this.registerForm.get('confirmPassword')?.value;
  }
}