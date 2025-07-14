<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DepartmentUsersAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            'view_tasks',
            'create_tasks',
            'edit_tasks',
            'delete_tasks',
            'assign_tasks',
            'complete_tasks',
            'view_all_tasks',
            'manage_departments',
            'view_reports',
            'manage_users'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles
        $roles = [
            'jefe' => [
                'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks', 
                'assign_tasks', 'complete_tasks', 'view_all_tasks', 
                'manage_departments', 'view_reports', 'manage_users'
            ],
            'subjefe' => [
                'view_tasks', 'create_tasks', 'edit_tasks', 'assign_tasks', 
                'complete_tasks', 'view_reports'
            ],
            'empleado' => [
                'view_tasks', 'create_tasks', 'edit_tasks', 'complete_tasks'
            ]
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create(['name' => $roleName]);
            $role->givePermissionTo($rolePermissions);
        }

        // Crear estructura de departamentos
        $departments = [
            'Desarrollo' => [
                'Diseño' => [],
                'Programación' => []
            ],
            'Ventas' => []
        ];

        $createdDepartments = [];

        foreach ($departments as $deptName => $children) {
            $parent = Department::create([
                'name' => $deptName,
                'description' => "Departamento de {$deptName}",
                'code' => strtoupper(substr($deptName, 0, 3)),
                'is_active' => true,
                'level' => 0
            ]);
            
            $createdDepartments[$deptName] = $parent;

            foreach ($children as $childName => $grandChildren) {
                $child = Department::create([
                    'name' => $childName,
                    'description' => "Subdepartamento de {$childName}",
                    'code' => strtoupper(substr($childName, 0, 3)),
                    'is_active' => true,
                    'parent_id' => $parent->id,
                    'level' => 1
                ]);
                
                $createdDepartments[$childName] = $child;
            }
        }

        // Crear usuarios
        $users = [
            // Jefes
            [
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos.rodriguez@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Desarrollo']->id,
                'role' => 'jefe'
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana.martinez@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Ventas']->id,
                'role' => 'jefe'
            ],
            
            // Subjefes
            [
                'name' => 'Luis García',
                'email' => 'luis.garcia@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Diseño']->id,
                'role' => 'subjefe'
            ],
            [
                'name' => 'María López',
                'email' => 'maria.lopez@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Programación']->id,
                'role' => 'subjefe'
            ],
            
            // Empleados de Diseño
            [
                'name' => 'Pedro Sánchez',
                'email' => 'pedro.sanchez@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Diseño']->id,
                'role' => 'empleado'
            ],
            [
                'name' => 'Carmen Ruiz',
                'email' => 'carmen.ruiz@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Diseño']->id,
                'role' => 'empleado'
            ],
            [
                'name' => 'Javier Torres',
                'email' => 'javier.torres@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Diseño']->id,
                'role' => 'empleado'
            ],
            
            // Empleados de Programación
            [
                'name' => 'Sofia Herrera',
                'email' => 'sofia.herrera@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Programación']->id,
                'role' => 'empleado'
            ],
            [
                'name' => 'Diego Morales',
                'email' => 'diego.morales@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Programación']->id,
                'role' => 'empleado'
            ],
            [
                'name' => 'Laura Jiménez',
                'email' => 'laura.jimenez@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Programación']->id,
                'role' => 'empleado'
            ],
            
            // Empleados de Ventas
            [
                'name' => 'Roberto Silva',
                'email' => 'roberto.silva@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Ventas']->id,
                'role' => 'empleado'
            ],
            [
                'name' => 'Patricia Vega',
                'email' => 'patricia.vega@empresa.com',
                'password' => Hash::make('password'),
                'department_id' => $createdDepartments['Ventas']->id,
                'role' => 'empleado'
            ]
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            
            $user = User::create($userData);
            $user->assignRole($role);
        }

        $this->command->info('✅ Se han creado exitosamente:');
        $this->command->info('   - 10 permisos');
        $this->command->info('   - 3 roles (jefe, subjefe, empleado)');
        $this->command->info('   - 3 departamentos (Desarrollo, Ventas)');
        $this->command->info('   - 2 subdepartamentos (Diseño, Programación)');
        $this->command->info('   - 12 usuarios con roles asignados');
    }
}
