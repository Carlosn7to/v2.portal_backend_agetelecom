<?php

namespace App\Http\Controllers\Portal\AgeReport\Management\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\AgeReport\UserRoleRequest;
use App\Models\Portal\AgeReport\Management\Report;
use App\Models\Portal\AgeReport\Management\UserRole;
use Illuminate\Http\Request;

class UsersRolesController extends Controller
{
    public function defineUserRoles(UserRoleRequest $request)
    {
        $command = $request->command;
        $userId = $request->userId;
        $level = $request->level;
        $report = $request->report ?? [];

        if (!in_array($command, ['insert', 'remove'])) {
            return response()->json(['error' => 'Invalid command'], 400);
        }

        return $this->{$command . 'Role'}($userId, $level, $report);
    }

    private function insertRole($userId, $level, $report)
    {
        $role = UserRole::firstOrNew(['usuario_id' => $userId]);

        if ($role->exists) {
            $oldReports = json_decode($role->relatorios_liberados, true) ?? [];
            $newReports = array_unique(array_merge($oldReports, $report));
        } else {
            $newReports = $report;
        }

        $role->fill([
            'relatorios_liberados' => json_encode($newReports),
            'liberado_por' => auth('portal')->id(),
        ])->save();

        $message = $role->wasRecentlyCreated
            ? 'Relatório(s) adicionado(s) com sucesso.'
            : 'Relatório(s) atualizado(s) com sucesso.';

        return response()->json(['success' => $message], 200);
    }

    private function removeRole($userId, $level, $report)
    {
        $role = UserRole::where('usuario_id', $userId)->first();

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $oldReports = json_decode($role->relatorios_liberados, true) ?? [];
        $remainingReports = array_diff($oldReports, $report);

        $role->update([
            'relatorios_liberados' => json_encode($remainingReports),
        ]);

        return response()->json(['success' => 'Relatório(s) removido(s) com sucesso.'], 200);
    }


    public function getReports()
    {
        $reports = Report::get(['id', 'nome', 'area', 'tipo']);

        return response()->json($reports, 200);


    }

}
