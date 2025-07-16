<div class="bg-white shadow-sm rounded-lg p-4 mb-6">
    <div class="flex items-center justify-between">
        <!-- Información del Usuario -->
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                <p class="text-sm text-gray-600">{{ $user->email }}</p>
                <p class="text-xs text-gray-500">
                    {{ $user->department ? $user->department->name : 'Sin departamento' }}
                </p>
            </div>
        </div>

        <!-- Progreso de Tareas -->
        <div class="flex items-center space-x-6">
            <div class="text-center">
                <p class="text-xs text-gray-500">Pendientes</p>
                <p class="text-lg font-bold text-yellow-600">{{ $myPendingTasks }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500">En Progreso</p>
                <p class="text-lg font-bold text-blue-600">{{ $myInProgressTasks }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500">Total</p>
                <p class="text-lg font-bold text-gray-900">{{ $myTasks }}</p>
            </div>
        </div>

        <!-- Botón de Logout -->
        <div class="flex items-center space-x-4">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</div>
