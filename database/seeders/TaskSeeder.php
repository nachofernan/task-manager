<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios y departamentos existentes
        $users = User::all();
        $departments = Department::all();

        if ($users->isEmpty() || $departments->isEmpty()) {
            $this->command->info('No hay usuarios o departamentos. Creando datos básicos...');
            return;
        }

        // Crear tareas de ejemplo
        $tasks = [
            [
                'name' => 'Implementar sistema de autenticación',
                'description' => 'Desarrollar un sistema completo de autenticación con roles y permisos',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => now()->addDays(7),
            ],
            [
                'name' => 'Diseñar interfaz de usuario',
                'description' => 'Crear mockups y prototipos de la interfaz principal',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(14),
            ],
            [
                'name' => 'Configurar base de datos',
                'description' => 'Migrar y configurar la estructura de la base de datos',
                'priority' => 'urgent',
                'status' => 'completed',
                'due_date' => now()->subDays(2),
            ],
            [
                'name' => 'Escribir documentación técnica',
                'description' => 'Documentar APIs y procesos del sistema',
                'priority' => 'low',
                'status' => 'pending',
                'due_date' => now()->addDays(21),
            ],
            [
                'name' => 'Realizar pruebas de integración',
                'description' => 'Ejecutar suite completa de pruebas automatizadas',
                'priority' => 'high',
                'status' => 'blocked',
                'due_date' => now()->addDays(5),
            ],
            [
                'name' => 'Optimizar rendimiento',
                'description' => 'Identificar y resolver cuellos de botella en el sistema',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(30),
            ],
            [
                'name' => 'Actualizar dependencias del proyecto',
                'description' => 'Revisar y actualizar los paquetes y librerías a sus últimas versiones.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(10),
            ],
            [
                'name' => 'Crear manual de usuario',
                'description' => 'Redactar un manual detallado para usuarios finales.',
                'priority' => 'low',
                'status' => 'pending',
                'due_date' => now()->addDays(20),
            ],
            [
                'name' => 'Implementar notificaciones internas',
                'description' => 'Desarrollar sistema de notificaciones para cambios en tareas.',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => now()->addDays(8),
            ],
            [
                'name' => 'Integrar subida de archivos',
                'description' => 'Permitir adjuntar documentos e imágenes a las tareas.',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(12),
            ],
            [
                'name' => 'Revisar seguridad de la aplicación',
                'description' => 'Auditar permisos y roles para evitar accesos indebidos.',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'due_date' => now()->addDays(3),
            ],
            [
                'name' => 'Configurar backups automáticos',
                'description' => 'Establecer copias de seguridad diarias de la base de datos.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(15),
            ],
            [
                'name' => 'Capacitación interna sobre el sistema',
                'description' => 'Organizar una sesión de capacitación para los empleados.',
                'priority' => 'low',
                'status' => 'pending',
                'due_date' => now()->addDays(18),
            ],
            [
                'name' => 'Implementar dashboard de métricas',
                'description' => 'Desarrollar panel con estadísticas de tareas y productividad.',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(14),
            ],
            [
                'name' => 'Optimizar consultas a la base de datos',
                'description' => 'Mejorar el rendimiento de las consultas más utilizadas.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'due_date' => now()->addDays(9),
            ],
            [
                'name' => 'Diseñar logo del sistema',
                'description' => 'Crear un logo representativo para la aplicación.',
                'priority' => 'low',
                'status' => 'completed',
                'due_date' => now()->subDays(1),
            ],
            [
                'name' => 'Implementar autenticación de dos factores',
                'description' => 'Agregar opción de 2FA para mayor seguridad.',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(11),
            ],
            [
                'name' => 'Revisar feedback de usuarios',
                'description' => 'Analizar sugerencias y reportes recibidos.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(7),
            ],
            [
                'name' => 'Configurar integración con Google Calendar',
                'description' => 'Permitir sincronizar tareas con calendarios externos.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(25),
            ],
            [
                'name' => 'Crear sistema de comentarios en tareas',
                'description' => 'Permitir a los usuarios dejar comentarios en cada tarea.',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => now()->addDays(6),
            ],
            [
                'name' => 'Implementar control de versiones en archivos',
                'description' => 'Guardar historial de cambios en los documentos adjuntos.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(17),
            ],
            [
                'name' => 'Realizar pruebas de usabilidad',
                'description' => 'Obtener feedback sobre la experiencia de usuario.',
                'priority' => 'low',
                'status' => 'pending',
                'due_date' => now()->addDays(22),
            ],
            [
                'name' => 'Configurar entorno de staging',
                'description' => 'Preparar un entorno de pruebas para despliegues previos.',
                'priority' => 'medium',
                'status' => 'completed',
                'due_date' => now()->subDays(3),
            ],
            [
                'name' => 'Actualizar políticas de privacidad',
                'description' => 'Revisar y actualizar los textos legales del sistema.',
                'priority' => 'low',
                'status' => 'pending',
                'due_date' => now()->addDays(28),
            ],
            [
                'name' => 'Implementar filtros avanzados de tareas',
                'description' => 'Permitir filtrar tareas por múltiples criterios.',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(13),
            ],
            [
                'name' => 'Revisar logs de actividad',
                'description' => 'Verificar que todas las acciones importantes se registren.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'due_date' => now()->addDays(4),
            ],
            [
                'name' => 'Crear sistema de subtareas',
                'description' => 'Permitir dividir tareas grandes en subtareas manejables.',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(16),
            ],
            [
                'name' => 'Integrar notificaciones por email',
                'description' => 'Enviar correos automáticos ante cambios relevantes.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(19),
            ],
            [
                'name' => 'Revisar compatibilidad móvil',
                'description' => 'Asegurar que la aplicación funcione correctamente en móviles.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(21),
            ],
            [
                'name' => 'Implementar exportación de reportes',
                'description' => 'Permitir descargar reportes de tareas en PDF o Excel.',
                'priority' => 'low',
                'status' => 'pending',
                'due_date' => now()->addDays(27),
            ],
            [
                'name' => 'Configurar integración con Slack',
                'description' => 'Enviar notificaciones de tareas a canales de Slack.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(23),
            ],
            [
                'name' => 'Revisar accesibilidad',
                'description' => 'Asegurar que la aplicación sea accesible para todos los usuarios.',
                'priority' => 'low',
                'status' => 'pending',
                'due_date' => now()->addDays(29),
            ],
            [
                'name' => 'Implementar tareas recurrentes',
                'description' => 'Permitir crear tareas que se repitan automáticamente.',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(24),
            ],
            [
                'name' => 'Actualizar landing page',
                'description' => 'Mejorar la página de inicio del sistema.',
                'priority' => 'low',
                'status' => 'completed',
                'due_date' => now()->subDays(5),
            ],
            [
                'name' => 'Revisar integración con API externa',
                'description' => 'Verificar que la comunicación con servicios externos funcione correctamente.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'due_date' => now()->addDays(5),
            ],
            [
                'name' => 'Crear sistema de evaluación de desempeño',
                'description' => 'Desarrollar módulo para evaluar productividad basada en tareas.',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(26),
            ],
            [
                'name' => 'Revisar documentación técnica',
                'description' => 'Actualizar y corregir la documentación existente.',
                'priority' => 'medium',
                'status' => 'pending',
                'due_date' => now()->addDays(20),
            ],
        ];

        foreach ($tasks as $taskData) {
            // Elegir aleatoriamente si la tarea tendrá usuario asignado o no (80% de probabilidad)
            $asignarUsuario = rand(0, 10) > 2 && $users->count() > 0;
            $assignedUser = $asignarUsuario ? $users->random() : null;

            // El departamento es el del usuario si hay usuario asignado, si no, uno aleatorio
            if ($assignedUser) {
                $departmentId = $assignedUser->department_id ?? $departments->random()->id;
            } else {
                $departmentId = $departments->random()->id;
            }

            Task::create([
                'name' => $taskData['name'],
                'description' => $taskData['description'],
                'priority' => $taskData['priority'],
                'status' => $taskData['status'],
                'due_date' => $taskData['due_date'],
                'assigned_user_id' => $assignedUser ? $assignedUser->id : null,
                'department_id' => $departmentId,
                'created_by' => $users->random()->id,
                'completed_at' => $taskData['status'] === 'completed' ? now() : null,
            ]);
        }

        $this->command->info('Tareas de ejemplo creadas exitosamente.');
    }
}
