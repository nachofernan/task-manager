<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Department;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Tareas
            'tasks.view_all',      // Ver todas las tareas (cross-department)
            'tasks.view_own',      // Ver tareas propias
            'tasks.view_team',     // Ver tareas del equipo/departamento
            'tasks.create',        // Crear tareas
            'tasks.edit_all',      // Editar cualquier tarea
            'tasks.edit_own',      // Editar tareas propias
            'tasks.edit_team',     // Editar tareas del equipo
            'tasks.delete',        // Eliminar tareas
            'tasks.assign',        // Asignar tareas a otros
            
            // Usuarios
            'users.view_all',      // Ver todos los usuarios
            'users.view_team',     // Ver usuarios del equipo
            'users.manage',        // Gestionar usuarios
            
            // Departamentos
            'departments.view',    // Ver departamentos
            'departments.manage', // Gestionar departamentos
            
            // Reportes
            'reports.view_all',    // Ver reportes generales
            'reports.view_team',   // Ver reportes del equipo
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        
        // 🔵 ROL: JEFE
        $jefeRole = Role::create(['name' => 'jefe']);
        $jefeRole->givePermissionTo([
            'tasks.view_all',
            'tasks.create',
            'tasks.edit_all',
            'tasks.delete',
            'tasks.assign',
            'users.view_all',
            'users.manage',
            'departments.view',
            'departments.manage',
            'reports.view_all',
        ]);

        // 🟡 ROL: SUBJEFE
        $subjefeRole = Role::create(['name' => 'subjefe']);
        $subjefeRole->givePermissionTo([
            'tasks.view_team',
            'tasks.view_own',
            'tasks.create',
            'tasks.edit_team',
            'tasks.edit_own',
            'tasks.assign',
            'users.view_team',
            'departments.view',
            'reports.view_team',
        ]);

        // 🟢 ROL: EMPLEADO
        $empleadoRole = Role::create(['name' => 'empleado']);
        $empleadoRole->givePermissionTo([
            'tasks.view_own',
            'tasks.edit_own',
            'users.view_team',
            'departments.view',
        ]);

        // Crear departamentos de ejemplo
        $departments = [
            [
                'name' => 'Desarrollo',
                'description' => 'Equipo de desarrollo de software',
                'color' => '#3B82F6'
            ],
            [
                'name' => 'Ventas',
                'description' => 'Equipo comercial y ventas',
                'color' => '#EF4444'
            ],
            [
                'name' => 'Marketing',
                'description' => 'Equipo de marketing y comunicación',
                'color' => '#8B5CF6'
            ],
            [
                'name' => 'Administración',
                'description' => 'Equipo administrativo y RRHH',
                'color' => '#F59E0B'
            ],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Crear usuarios de ejemplo (solo si no existen)
        $this->createExampleUsers();
    }

    private function createExampleUsers(): void
    {
        $desarrollo = Department::where('name', 'Desarrollo')->first();
        $ventas = Department::where('name', 'Ventas')->first();
        $marketing = Department::where('name', 'Marketing')->first();

        // Crear JEFE general
        if (!User::where('email', 'jefe@empresa.com')->exists()) {
            $jefe = User::create([
                'name' => 'Juan Pérez',
                'email' => 'jefe@empresa.com',
                'password' => bcrypt('password'),
                'department_id' => $desarrollo->id,
                'email_verified_at' => now(),
            ]);
            $jefe->assignRole('jefe');
        }

        // Crear SUBJEFES
        if (!User::where('email', 'subjefe.dev@empresa.com')->exists()) {
            $subjefeDesarrollo = User::create([
                'name' => 'María García',
                'email' => 'subjefe.dev@empresa.com',
                'password' => bcrypt('password'),
                'department_id' => $desarrollo->id,
                'email_verified_at' => now(),
            ]);
            $subjefeDesarrollo->assignRole('subjefe');
        }

        if (!User::where('email', 'subjefe.ventas@empresa.com')->exists()) {
            $subjefeVentas = User::create([
                'name' => 'Carlos López',
                'email' => 'subjefe.ventas@empresa.com',
                'password' => bcrypt('password'),
                'department_id' => $ventas->id,
                'email_verified_at' => now(),
            ]);
            $subjefeVentas->assignRole('subjefe');
        }

        // Crear EMPLEADOS
        $empleados = [
            [
                'name' => 'Ana Rodríguez',
                'email' => 'ana@empresa.com',
                'department_id' => $desarrollo->id,
            ],
            [
                'name' => 'Luis Martínez',
                'email' => 'luis@empresa.com',
                'department_id' => $desarrollo->id,
            ],
            [
                'name' => 'Carmen Silva',
                'email' => 'carmen@empresa.com',
                'department_id' => $ventas->id,
            ],
            [
                'name' => 'Pedro Gómez',
                'email' => 'pedro@empresa.com',
                'department_id' => $marketing->id,
            ],
        ];

        foreach ($empleados as $empleadoData) {
            if (!User::where('email', $empleadoData['email'])->exists()) {
                $empleado = User::create([
                    'name' => $empleadoData['name'],
                    'email' => $empleadoData['email'],
                    'password' => bcrypt('password'),
                    'department_id' => $empleadoData['department_id'],
                    'email_verified_at' => now(),
                ]);
                $empleado->assignRole('empleado');
            }
        }
    }
}