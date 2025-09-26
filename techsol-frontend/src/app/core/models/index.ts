export type UserProfile = 'Administrador' | 'Gente e Cultura' | 'Colaborador Comum';
export type InviteStatus = 'Finalizado' | 'Pendente' | 'Vencido';
export type AppView = 'landing' | 'login' | 'dashboard';
export type DashboardPage = 'welcome' | 'invite' | 'management';

export interface User {
  id: number;
  name: string;
  email: string;
  cpf: string;
  phone?: string;
  address?: Address;
  profile: UserProfile;
  password?: string;
  status?: InviteStatus;
}

export interface Address {
  cep: string;
  uf: string;
  city: string;
  neighborhood: string;
  street: string;
}

export interface JobPosition {
  title: string;
  location: string;
  type: string;
}
