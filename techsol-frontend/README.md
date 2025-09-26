# Frontend - Plataforma de Onboarding TechnologySolutions

![Angular](https://img.shields.io/badge/Angular-DD0031?style=for-the-badge&logo=angular&logoColor=white)
![Angular Material](https://img.shields.io/badge/Angular_Material-4285F4?style=for-the-badge&logo=angular&logoColor=white)

## 📖 Sobre o Frontend

Este repositório contém a interface de usuário (Single-Page Application - SPA) para a plataforma de onboarding da TechnologySolutions. Desenvolvido em **Angular 18**, ele é responsável por toda a experiência do usuário, desde a landing page pública até o painel administrativo restrito.

Esta aplicação consome os dados da API em Laravel desenvolvida para este projeto.

> **O repositório do Backend (API em Laravel) pode ser encontrado aqui:** [Link para o Repositório da API](https://github.com/Kaeffea/techsol-frontend)

## ✨ Funcionalidades

- ✅ **Arquitetura Moderna:** Construído com Standalone Components, a abordagem mais recente do Angular.
- ✅ **Layout Responsivo:** Estrutura principal que se adapta a diferentes tamanhos de tela.
- ✅ **Componentização:** Interface dividida em componentes reutilizáveis (Header, Footer, Cards, etc.).
- ✅ **UI Profissional:** Uso da biblioteca de componentes **Angular Material**.
- ✅ **Páginas Implementadas:**
  - Landing Page com múltiplas seções.
  - Página de Login com validação e comunicação com a API.
  - Página de Cadastro com validação, máscara de campos (`ngx-mask`) e integração com a API externa **ViaCEP**.
  - Painel Administrativo protegido por **Route Guard** para segurança.
  - Dashboard com tabela de colaboradores, busca em tempo real e paginação.
  - Página para enviar novos convites.
  - Dialog (modal) para visualizar e editar o perfil de um colaborador.

## 🛠️ Tecnologias Utilizadas

- **Framework:** Angular 18
- **Linguagem:** TypeScript
- **Gerenciamento de Pacotes:** NPM
- **Bibliotecas Principais:**
  - `@angular/material` (para componentes de UI)
  - `ngx-mask` (para máscaras de input)

## ⚙️ Pré-requisitos

- [Git](https://git-scm.com/)
- [Node.js](https://nodejs.org/) (versão LTS recomendada)
- O **Backend (API)** precisa estar rodando para que as funcionalidades interativas funcionem. Siga as instruções no repositório da API.

## 🚀 Como Executar o Frontend

```bash
# 1. Clone este repositório
git clone https://github.com/Kaeffea/techsol-frontend
cd techsol-frontend

# 2. Instale as dependências com NPM
npm install

# 3. Inicie o servidor de desenvolvimento do Angular
ng serve

# 4. Acesse a aplicação!
# A aplicação estará rodando em: http://localhost:4200
```
