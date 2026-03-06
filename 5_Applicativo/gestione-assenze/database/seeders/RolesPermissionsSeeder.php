<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            ['name' => 'STUDENT', 'label' => 'Studente', 'amministratore' => 0],
            ['name' => 'TEACHER', 'label' => 'Docente', 'amministratore' => 0],
            ['name' => 'GUARDIAN', 'label' => 'Tutore', 'amministratore' => 0],
            ['name' => 'CAPOLAB', 'label' => 'Capolaboratorio', 'amministratore' => 0],
            ['name' => 'DIREZIONE', 'label' => 'Direzione', 'amministratore' => 0],
            ['name' => 'ADMIN', 'label' => 'Amministratore', 'amministratore' => 1],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'label' => $role['label'],
                    'amministratore' => $role['amministratore'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $permissions = [
            ['name' => 'teacher.classes.access', 'label' => 'Accesso classi docente', 'amministratore' => 0],
            ['name' => 'teacher.students.access', 'label' => 'Accesso studenti docente', 'amministratore' => 0],
            ['name' => 'student.absences.access', 'label' => 'Accesso assenze studente', 'amministratore' => 0],
            ['name' => 'student.delays.access', 'label' => 'Accesso ritardi studente', 'amministratore' => 0],
            ['name' => 'student.signatures.access', 'label' => 'Accesso firme studente', 'amministratore' => 0],
            ['name' => 'student.certificates.access', 'label' => 'Accesso certificati studente', 'amministratore' => 0],
            ['name' => 'student.reports.access', 'label' => 'Accesso report studente', 'amministratore' => 0],
            ['name' => 'guardian.absences.access', 'label' => 'Accesso assenze tutore', 'amministratore' => 0],
            ['name' => 'capolab.absence_approvals.access', 'label' => 'Approvazione assenze capolab', 'amministratore' => 0],
            ['name' => 'direzione.absence_approvals.access', 'label' => 'Approvazione assenze direzione', 'amministratore' => 0],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [
                    'label' => $permission['label'],
                    'amministratore' => $permission['amministratore'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $roleIds = DB::table('roles')->pluck('id', 'name');
        $permissionIds = DB::table('permissions')->pluck('id', 'name');

        $rolePermissions = [
            'TEACHER' => [
                'teacher.classes.access',
                'teacher.students.access',
            ],
            'STUDENT' => [
                'student.absences.access',
                'student.delays.access',
                'student.signatures.access',
                'student.certificates.access',
                'student.reports.access',
            ],
            'GUARDIAN' => [
                'guardian.absences.access',
            ],
            'CAPOLAB' => [
                'teacher.classes.access',
                'teacher.students.access',
                'capolab.absence_approvals.access',
            ],
            'DIREZIONE' => [
                'teacher.classes.access',
                'teacher.students.access',
                'direzione.absence_approvals.access',
            ],
            'ADMIN' => [
                'teacher.classes.access',
                'teacher.students.access',
                'student.absences.access',
                'student.delays.access',
                'student.signatures.access',
                'student.certificates.access',
                'student.reports.access',
                'guardian.absences.access',
                'capolab.absence_approvals.access',
                'direzione.absence_approvals.access',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            if (!isset($roleIds[$roleName])) {
                continue;
            }

            $roleId = $roleIds[$roleName];
            foreach ($permissionNames as $permissionName) {
                if (!isset($permissionIds[$permissionName])) {
                    continue;
                }

                DB::table('permission_role')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'permission_id' => $permissionIds[$permissionName],
                    ],
                    []
                );
            }
        }

        $fkTarget = DB::table('information_schema.key_column_usage')
            ->select('referenced_table_name')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'role_user')
            ->where('column_name', 'user_id')
            ->whereNotNull('referenced_table_name')
            ->value('referenced_table_name');

        if ($fkTarget === 'users') {
            if ($this->command) {
                $this->command->warn('role_user.user_id punta a users: assegnazione automatica dei ruoli saltata (il modello usa la tabella user).');
            }
            return;
        }

        $users = DB::table('user')->select('id', 'role')->get();
        foreach ($users as $user) {
            if (!$user->role || !isset($roleIds[$user->role])) {
                continue;
            }

            DB::table('role_user')->updateOrInsert(
                [
                    'role_id' => $roleIds[$user->role],
                    'user_id' => $user->id,
                ],
                []
            );
        }
    }
}
