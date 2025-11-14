<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationEmail;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class InviteController extends Controller
{
    /**
     * ETAPA 1: cria o convite básico (email + cpf + status)
     */
    public function start(Request $request)
    {   
    $request->validate([
        'email' => 'required|email|unique:users,email',
        'cpf'   => 'required|string|size:11|unique:users,cpf',
    ]);

    $invitation = Invitation::create([
        'email' => $request->email,
        'cpf' => $request->cpf,
        'status' => 'step_1_pending_profiles',
    ]);

    return response()->json($invitation, 201);
    }

    /**
     * ETAPA 2: associa perfis (roles) ao convite
     */
    public function assignRoles(Request $request, Invitation $invitation)
    {
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id'
        ]);

        // anexa os roles (pivot criado sem contexto ainda)
        $invitation->roles()->sync($request->roles);

        $invitation->status = 'step_2_pending_context';
        $invitation->save();

        return response()->json($invitation->load('roles'));
    }

    /**
     * ETAPA 3: atribui contexto (operational_unit_id / regional_department_id) nos pivôs
     * recebe assignments: [{ role_id: X, operational_unit_id: Y?, regional_department_id: Z? }, ...]
     */
    public function assignContext(Request $request, Invitation $invitation)
    {
        $request->validate([
            'assignments' => 'required|array',
            'assignments.*.role_id' => 'required|exists:roles,id',
            'assignments.*.operational_unit_id' => 'nullable|exists:operational_units,id',
            'assignments.*.regional_department_id' => 'nullable|exists:regional_departments,id',
        ]);

        foreach ($request->assignments as $assignment) {
            // updateExistingPivot falhará se o role não estiver anexado — protegemos isso
            if (! $invitation->roles()->where('role_id', $assignment['role_id'])->exists()) {
                continue; // ou colecione e retorne erro; aqui ignoramos silenciosamente
            }

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
     * ETAPA 4: confirma e envia o convite (gera token + envia e-mail).
     * Retorna mensagens claras e evita 500 bruto.
     */
    public function send(Invitation $invitation)
    {
        // Verifica estado mínimo
        if (! $invitation->roles()->exists()) {
            return response()->json(['message' => 'Convite sem perfis atribuídos. Execute as etapas anteriores.'], 422);
        }

        // Se já enviado, prevenimos duplicação
        if ($invitation->status === 'sent') {
            return response()->json(['message' => 'Convite já foi enviado.'], 409);
        }

        // Gera token e data de expiração
        $invitation->token = Str::random(40);
        $invitation->expires_at = now()->addHours(24);

        // marca status
        $invitation->status = 'sent';

        try {
            $invitation->save();

            // tentar enviar e-mail — captura problemas e não explode a API
            try {
                Mail::to($invitation->email)->send(new InvitationEmail($invitation));
            } catch (Exception $mailEx) {
                // registra no log (se quiser) e retornar aviso
                \Log::error('Falha ao enviar e-mail de convite: ' . $mailEx->getMessage(), [
                    'invitation_id' => $invitation->id
                ]);

                // ainda retornamos sucesso parcial: convite criado e token gerado, mas e-mail falhou
                return response()->json([
                    'message' => 'Convite gerado com sucesso, mas falha ao enviar e-mail. Verifique logs.',
                    'invitation' => $invitation
                ], 201);
            }

            return response()->json(['message' => 'Convite enviado com sucesso!', 'invitation' => $invitation], 200);

        } catch (Exception $ex) {
            \Log::error('Erro ao salvar convite: ' . $ex->getMessage(), ['invitation_id' => $invitation->id ?? null]);
            return response()->json(['message' => 'Erro interno ao processar convite.'], 500);
        }
    }

    /**
     * ETAPA 5 (Nova): Lista os convites enviados.
     */
    public function index()
    {
        $invites = Invitation::with(['roles'])->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'data' => $invites
        ]);
    }
}
