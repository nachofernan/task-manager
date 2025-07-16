<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserProfileHeader extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        // Estadísticas del usuario
        $myTasks = Task::where('assigned_user_id', $user->id)->count();
        
        // Tareas por estado
        $myPendingTasks = Task::where('assigned_user_id', $user->id)
            ->where('status', 'pending')
            ->count();
            
        $myInProgressTasks = Task::where('assigned_user_id', $user->id)
            ->where('status', 'in_progress')
            ->count();

        return view('livewire.user-profile-header', [
            'user' => $user,
            'myTasks' => $myTasks,
            'myPendingTasks' => $myPendingTasks,
            'myInProgressTasks' => $myInProgressTasks,
        ]);
    }
}
