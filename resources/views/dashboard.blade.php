<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Cabecera con información del usuario -->
            @livewire('user-profile-header')
            
            <!-- Dashboard de tareas -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                @livewire('task-dashboard')
            </div>
        </div>
    </div>
</x-app-layout>
