<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class TaskDashboard extends Component
{
    use WithPagination;

    // Propiedades para filtros
    public $statusFilter = '';
    public $priorityFilter = '';
    public $assignedUserFilter = '';
    public $departmentFilter = '';
    public $viewFilter = 'my_tasks'; // Nueva propiedad para el filtro de vista

    // Propiedades para el modal
    public $showTaskModal = false;
    public $selectedTask = null;
    public $newComment = '';

    // Propiedades para ordenamiento
    public $sortBy = 'priority';
    public $sortDirection = 'desc';

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
        'assignedUserFilter' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'viewFilter' => ['except' => 'my_tasks'],
        'sortBy' => ['except' => 'priority'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        // Por defecto, mostrar solo tareas que el usuario puede ver
        $this->viewFilter = 'my_tasks';
    }

    public function updatedViewFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter()
    {
        $this->resetPage();
    }

    public function updatedAssignedUserFilter()
    {
        $this->resetPage();
    }

    public function updatedDepartmentFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->reset([
            'statusFilter',
            'priorityFilter',
            'assignedUserFilter',
            'departmentFilter',
            'viewFilter'
        ]);
        $this->resetPage();
    }

    public function openTaskModal($taskId)
    {
        $this->selectedTask = Task::with(['assignedUser', 'department', 'creator', 'comments.user'])
            ->findOrFail($taskId);
        $this->showTaskModal = true;
    }

    public function closeTaskModal()
    {
        $this->showTaskModal = false;
        $this->selectedTask = null;
        $this->newComment = '';
    }

    public function addComment()
    {
        $this->validate([
            'newComment' => 'required|string|max:1000',
        ]);

        $this->selectedTask->comments()->create([
            'content' => $this->newComment,
            'user_id' => Auth::id(),
            'status' => 'active',
        ]);

        $this->newComment = '';
        $this->selectedTask->refresh();
    }

    public function updatedNewComment()
    {
        $this->resetValidation('newComment');
    }

    public function render()
    {
        $user = Auth::user();
        
        // Query base
        $query = Task::with(['assignedUser', 'department', 'creator']);

        // Filtros de visibilidad según rol
        if ($user->roles->contains('name', 'jefe')) {
            // El jefe ve todas las tareas
        } elseif ($user->roles->contains('name', 'subjefe')) {
            // El subjefe ve tareas de su departamento y subordinados
            $query->where(function ($q) use ($user) {
                $q->where('department_id', $user->department_id)
                  ->orWhereHas('department', function ($deptQuery) use ($user) {
                      $deptQuery->where('parent_id', $user->department_id);
                  });
            });
        } else {
            // Empleados ven solo sus tareas asignadas
            $query->where('assigned_user_id', $user->id);
        }

        // Filtro de vista (se aplica después de los filtros de visibilidad)
        if ($this->viewFilter === 'my_tasks') {
            $query->where('assigned_user_id', $user->id);
        } elseif ($this->viewFilter === 'unassigned') {
            $query->whereNull('assigned_user_id');
        }
        // Si viewFilter es 'all', no se aplica filtro adicional

        // Filtros adicionales
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        if ($this->assignedUserFilter) {
            $query->where('assigned_user_id', $this->assignedUserFilter);
        }

        if ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        }

        // Remover los filtros antiguos que ya no se usan
        // if ($this->showUnassigned) {
        //     $query->whereNull('assigned_user_id');
        // }

        // if ($this->showMyTasks) {
        //     $query->where('assigned_user_id', $user->id);
        // }

        // Ordenamiento
        if ($this->sortBy === 'priority') {
            // Ordenamiento por prioridad compatible con SQLite
            $query->orderByRaw("CASE 
                WHEN priority = 'urgent' THEN 1 
                WHEN priority = 'high' THEN 2 
                WHEN priority = 'medium' THEN 3 
                WHEN priority = 'low' THEN 4 
                ELSE 5 END");
        } elseif ($this->sortBy === 'due_date') {
            $query->orderBy('due_date', $this->sortDirection);
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        // Ordenamiento secundario por fecha límite
        if ($this->sortBy !== 'due_date') {
            $query->orderBy('due_date', 'asc');
        }

        $tasks = $query->paginate(10);

        // Datos para los filtros según permisos del usuario
        $filteredUsers = $this->getFilteredUsers($user);
        $filteredDepartments = $this->getFilteredDepartments($user);
        $statuses = ['pending', 'in_progress', 'blocked', 'completed', 'cancelled'];
        $priorities = ['urgent', 'high', 'medium', 'low'];

        return view('livewire.task-dashboard', [
            'tasks' => $tasks,
            'filteredUsers' => $filteredUsers,
            'filteredDepartments' => $filteredDepartments,
            'statuses' => $statuses,
            'priorities' => $priorities,
        ]);
    }

    private function getFilteredUsers($user)
    {
        // Verificar roles usando el método del modelo User
        if ($user->roles->contains('name', 'jefe')) {
            return User::orderBy('name')->get();
        } elseif ($user->roles->contains('name', 'subjefe')) {
            // Subjefes ven usuarios de su departamento y subordinados directos
            return User::where('department_id', $user->department_id)
                ->orWhereHas('department', function ($query) use ($user) {
                    $query->where('parent_id', $user->department_id);
                })
                ->orderBy('name')
                ->get();
        } else {
            // Empleados solo ven su propio usuario y superiores jerárquicos
            $visibleUsers = collect([$user]);
            
            // Agregar superiores jerárquicos (jefes de departamento)
            if ($user->department) {
                $superiors = User::where('department_id', $user->department_id)
                    ->whereHas('roles', function ($query) {
                        $query->whereIn('name', ['jefe', 'subjefe']);
                    })
                    ->get();
                $visibleUsers = $visibleUsers->merge($superiors);
            }
            
            return $visibleUsers->unique('id')->sortBy('name');
        }
    }

    private function getFilteredDepartments($user)
    {
        // Verificar roles usando el método del modelo User
        if ($user->roles->contains('name', 'jefe')) {
            return Department::orderBy('name')->get();
        } elseif ($user->roles->contains('name', 'subjefe')) {
            // Subjefes ven su departamento y subordinados
            return Department::where('id', $user->department_id)
                ->orWhere('parent_id', $user->department_id)
                ->orderBy('name')
                ->get();
        } else {
            // Empleados solo ven su departamento
            return Department::where('id', $user->department_id)->get();
        }
    }
}
