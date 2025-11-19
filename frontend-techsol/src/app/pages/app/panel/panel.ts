import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-panel',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './panel.html',
})
export class Panel {

  // Mock de dados baseado no Print
  students = [
    {
      dr: 'TO',
      escola: 'CFP-Paraiso-Centro de Formação Profissional',
      nome: 'SILVANO DE JESUS SOARES DOS SANTOS',
      cpf: '873.322.721-72',
      curso: 'Técnico em Refrigeração e Climatização',
      
      // Status: success, warning, error
      status_cadastro_data: '29/06/2021 às 17:36',
      status_cadastro: 'success',

      status_avaliacao_label: 'Desistente',
      status_avaliacao: 'error',

      status_matricula_label: 'Desistente',
      status_matricula: 'error',

      status_envio_label: 'Desistente',
      status_envio: 'error',

      status_conclusao_label: 'Desistente',
      status_conclusao: 'error',
    },
    {
      dr: 'PE',
      escola: 'Escola Técnica SENAI Araripina',
      nome: 'Lívia Lima Oliveira Andrade',
      cpf: '130.178.334-02',
      curso: 'Modelista de Calçados',
      
      status_cadastro_data: '09/09/2022 às 21:48',
      status_cadastro: 'success',

      status_avaliacao_label: 'Atrasado',
      status_avaliacao: 'error', // No print aparece vermelho (atrasado)

      status_matricula_label: 'Atrasado',
      status_matricula: 'error',

      status_envio_label: 'Atrasado',
      status_envio: 'error',

      status_conclusao_label: 'Atrasado',
      status_conclusao: 'error',
    },
    {
        dr: 'PE',
        escola: 'Escola Técnica SENAI Araripina',
        nome: 'Lucas de Jesus Da Silva Pereira',
        cpf: '083.537.304-56',
        curso: 'Modelista de Calçados',
        
        status_cadastro_data: '09/09/2022 às 21:57',
        status_cadastro: 'success',
  
        status_avaliacao_label: 'Atrasado',
        status_avaliacao: 'error',
  
        status_matricula_label: 'Atrasado',
        status_matricula: 'error',
  
        status_envio_label: 'Atrasado',
        status_envio: 'error',
  
        status_conclusao_label: 'Atrasado',
        status_conclusao: 'error',
    },
    {
        dr: 'PE',
        escola: 'Escola Técnica SENAI Araripina',
        nome: 'Fernando de Sousa Modesto',
        cpf: '289.231.138-16',
        curso: 'MECÂNICO DE MÁQUINAS INDUSTRIAIS',
        
        status_cadastro_data: '09/09/2022 às 22:59',
        status_cadastro: 'success',
  
        status_avaliacao_label: 'Atrasado',
        status_avaliacao: 'error',
  
        status_matricula_label: 'Atrasado',
        status_matricula: 'error',
  
        status_envio_label: 'Atrasado',
        status_envio: 'error',
  
        status_conclusao_label: 'Atrasado',
        status_conclusao: 'error',
    },
    {
        dr: 'PE',
        escola: 'Escola Técnica SENAI Araripina',
        nome: 'Fernando de Sousa Modesto',
        cpf: '289.231.138-16',
        curso: 'ELETRICISTA INDUSTRIAL',
        
        status_cadastro_data: '09/09/2022 às 22:59',
        status_cadastro: 'success',
  
        status_avaliacao_label: 'Atrasado',
        status_avaliacao: 'error',
  
        status_matricula_label: 'Atrasado',
        status_matricula: 'error',
  
        status_envio_label: 'Atrasado',
        status_envio: 'error',
  
        status_conclusao_label: 'Atrasado',
        status_conclusao: 'error',
    }
  ];

  getStatusColor(status: string): string {
    switch(status) {
        case 'success': return 'bg-green-500 border-2 border-white shadow'; // Verde
        case 'error': return 'bg-red-500 border-2 border-white shadow';   // Vermelho (Desistente/Atrasado)
        case 'warning': return 'bg-blue-400 border-2 border-white shadow'; // Azul (Aguardando)
        default: return 'bg-gray-300';
    }
  }

}