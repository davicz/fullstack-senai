<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\OperationalUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationEmail;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    /**
     * ETAPA 1: Inicia um novo convite com os dados pessoais.
     */
    public function start(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|string|size:11|unique:users,cpf',
        ]);

        // Cria o convite no primeiro estado
        $invitation = Invitation::create([
            'email' => $validatedData['email'],
            'cpf' => $validatedData['cpf'],
            'status' => 'step_1_pending_profiles',
        ]);

        return response()->json($invitation, 201);
    }

    /**
     * ETAPA 2: Associa os perfis selecionados ao convite.
     */
    public function assignRoles(Request $request, Invitation $invitation)
    {
        $validatedData = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id', // Garante que os IDs dos perfis são válidos
        ]);
        
        // Associa os perfis na tabela pivô (ainda sem contexto)
        $invitation->roles()->sync($validatedData['roles']);
        
        // Atualiza o status do convite
        $invitation->status = 'step_2_pending_context';
        $invitation->save();

        // Retorna o convite com os perfis associados para a próxima etapa
        return response()->json($invitation->load('roles'));
    }

    /**
     * ETAPA 3: Atribui os contextos (escolas/regionais) a cada perfil.
     */
    public function assignContext(Request $request, Invitation $invitation)
    {
        //dd('Cheguei no método assignContext!');
        $validatedData = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.role_id' => 'required|exists:roles,id',
            'assignments.*.operational_unit_id' => 'nullable|exists:operational_units,id',
            'assignments.*.regional_department_id' => 'nullable|exists:regional_departments,id',
        ]);

        foreach ($validatedData['assignments'] as $assignment) {
            $invitation->roles()->updateExistingPivot($assignment['role_id'], [
                'operational_unit_id' => $assignment['operational_unit_id'] ?? null,
                'regional_department_id' => $assignment['regional_department_id'] ?? null,
            ]);
        }
        
        $invitation->status = 'step_3_pending_confirmation';
        $invitation->save();

        return response()->json($invitation->load('roles'));
    }

    /**
     * ETAPA 4: Confirma e envia o convite.
     */
    public function send(Invitation $invitation)
    {
        //if ($invitation->status !== 'step_3_pending_confirmation') {
        //    return response()->json(['message' => 'Convite não está pronto para ser enviado.'], 422);
        //}

        // Gera o token final e data de expiração
        $invitation->token = Str::random(40);
        $invitation->expires_at = now()->addHours(24);
        $invitation->status = 'sent';
        $invitation->save();

        // Dispara o e-mail (atualmente configurado para log)
        Mail::to($invitation->email)->send(new InvitationEmail($invitation));

        return response()->json(['message' => 'Convite enviado com sucesso!']);
    }
}