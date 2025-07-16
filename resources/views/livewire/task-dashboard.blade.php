<div class="p-6">
    <!-- Filtros -->
    <div class="bg-white shadow-sm rounded-lg mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <!-- Filtro por Estado -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Estado</label>
                <select wire:model.live="statusFilter" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Prioridad -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Prioridad</label>
                <select wire:model.live="priorityFilter" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">Todas las prioridades</option>
                    @foreach($priorities as $priority)
                        <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Usuario Asignado -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Asignado a</label>
                <select wire:model.live="assignedUserFilter" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">Todos los usuarios</option>
                    @foreach($filteredUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Departamento -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Departamento</label>
                <select wire:model.live="departmentFilter" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">Todos los departamentos</option>
                    @foreach($filteredDepartments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro de Vista -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Vista</label>
                <select wire:model.live="viewFilter" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="all">Todas las tareas</option>
                    <option value="my_tasks">Mis tareas</option>
                    <option value="unassigned">Sin asignar</option>
                </select>
            </div>

            <!-- Botón limpiar filtros -->
            <div class="flex items-end">
                <button wire:click="clearFilters" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tarea
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('assigned_user_id')" class="flex items-center hover:text-gray-700">
                                Asignado
                                @if($sortBy === 'assigned_user_id')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('department_id')" class="flex items-center hover:text-gray-700">
                                Departamento
                                @if($sortBy === 'department_id')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('status')" class="flex items-center hover:text-gray-700">
                                Estado
                                @if($sortBy === 'status')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('priority')" class="flex items-center hover:text-gray-700">
                                Prioridad
                                @if($sortBy === 'priority')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button wire:click="sortBy('due_date')" class="flex items-center hover:text-gray-700">
                                Fecha Límite
                                @if($sortBy === 'due_date')
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tasks as $task)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $task->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ Str::limit($task->description, 50) }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $task->assignedUser ? $task->assignedUser->name : 'Sin asignar' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $task->department ? $task->department->name : 'Sin departamento' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($task->status === 'completed') bg-green-100 text-green-800
                                    @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($task->status === 'blocked') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($task->priority === 'urgent') bg-red-100 text-red-800
                                    @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                                    @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $task->due_date ? $task->due_date->format('d/m/Y') : 'Sin fecha' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="openTaskModal({{ $task->id }})" class="text-blue-600 hover:text-blue-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No hay tareas disponibles con los filtros seleccionados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tasks->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de Detalles de Tarea -->
    @if($showTaskModal && $selectedTask)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeTaskModal">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white" wire:click.stop>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ $selectedTask->name }}</h3>
                <button wire:click="closeTaskModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex gap-6">
                <!-- Panel de Información (1/3) -->
                <div class="w-1/3">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 mb-4">Información de la Tarea</h4>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-medium text-gray-500">Descripción</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $selectedTask->description ?: 'Sin descripción' }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500">Estado</label>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1
                                    @if($selectedTask->status === 'completed') bg-green-100 text-green-800
                                    @elseif($selectedTask->status === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($selectedTask->status === 'blocked') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $selectedTask->status)) }}
                                </span>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500">Prioridad</label>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1
                                    @if($selectedTask->priority === 'urgent') bg-red-100 text-red-800
                                    @elseif($selectedTask->priority === 'high') bg-orange-100 text-orange-800
                                    @elseif($selectedTask->priority === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ ucfirst($selectedTask->priority) }}
                                </span>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500">Asignado a</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    {{ $selectedTask->assignedUser ? $selectedTask->assignedUser->name : 'Sin asignar' }}
                                </p>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500">Departamento</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    {{ $selectedTask->department ? $selectedTask->department->name : 'Sin departamento' }}
                                </p>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500">Creado por</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    {{ $selectedTask->creator ? $selectedTask->creator->name : 'Usuario desconocido' }}
                                </p>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500">Fecha de creación</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    {{ $selectedTask->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            @if($selectedTask->due_date)
                            <div>
                                <label class="text-xs font-medium text-gray-500">Fecha límite</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    {{ $selectedTask->due_date->format('d/m/Y') }}
                                </p>
                            </div>
                            @endif

                            @if($selectedTask->updated_at != $selectedTask->created_at)
                            <div>
                                <label class="text-xs font-medium text-gray-500">Última actualización</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    {{ $selectedTask->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Panel de Comentarios (2/3) -->
                <div class="w-2/3">
                    <div class="bg-white border rounded-lg">
                        <div class="p-4 border-b border-gray-200">
                            <h4 class="font-semibold text-gray-900">Comentarios ({{ $selectedTask->comments->count() }})</h4>
                        </div>

                        <!-- Lista de comentarios -->
                        <div class="p-4 max-h-96 overflow-y-auto">
                            @forelse($selectedTask->comments as $comment)
                                <div class="mb-4 pb-4 border-b border-gray-100 last:border-b-0">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                                <span class="text-white text-xs font-medium">
                                                    {{ substr($comment->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <p class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</p>
                                                <span class="text-xs text-gray-500">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <p class="text-sm text-gray-700 mt-1">{{ $comment->content }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">No hay comentarios aún</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Formulario para nuevo comentario -->
                        <div class="p-4 border-t border-gray-200">
                            <form wire:submit.prevent="addComment">
                                <div class="flex space-x-3">
                                    <div class="flex-1">
                                        <textarea 
                                            wire:model="newComment" 
                                            rows="3" 
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm @error('newComment') border-red-300 @enderror"
                                            placeholder="Escribe un comentario..."
                                        ></textarea>
                                        @error('newComment')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex-shrink-0">
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                            wire:loading.attr="disabled"
                                        >
                                            <span wire:loading.remove>Comentar</span>
                                            <span wire:loading>Enviando...</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
