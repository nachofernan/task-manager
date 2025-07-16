<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $title = 'Dashboard';
    
    protected static ?string $slug = 'dashboard';

    protected static string $view = 'filament.pages.dashboard';
    
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            RecentTasks::class,
        ];
    }
}

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Cache las estadísticas por 5 minutos
        $stats = cache()->remember('dashboard_stats', 300, function () {
            return [
                'total_tasks' => Task::count(),
                'pending_tasks' => Task::where('status', 'pending')->count(),
                'completed_tasks' => Task::where('status', 'completed')->count(),
                'total_users' => User::count(),
            ];
        });

        return [
            Stat::make('Total de Tareas', $stats['total_tasks'])
                ->description('Tareas en el sistema')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Tareas Pendientes', $stats['pending_tasks'])
                ->description('Por completar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Tareas Completadas', $stats['completed_tasks'])
                ->description('Finalizadas')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            
            Stat::make('Usuarios Activos', $stats['total_users'])
                ->description('En el sistema')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}

class RecentTasks extends TableWidget
{
    protected static ?string $heading = 'Tareas Recientes';
    
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Task::query()
            ->with(['assignedUser:id,name', 'department:id,name', 'creator:id,name'])
            ->latest()
            ->limit(5); // Reducir a 5 para mejor rendimiento
    }
    
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Nombre')
                ->searchable()
                ->limit(30),
            
            TextColumn::make('assignedUser.name')
                ->label('Asignado a'),
            
            BadgeColumn::make('status')
                ->label('Estado')
                ->colors([
                    'gray' => 'pending',
                    'blue' => 'in_progress',
                    'red' => 'blocked',
                    'success' => 'completed',
                    'gray' => 'cancelled',
                ])
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'Pendiente',
                    'in_progress' => 'En Progreso',
                    'blocked' => 'Bloqueada',
                    'completed' => 'Completada',
                    'cancelled' => 'Cancelada',
                }),
            
            BadgeColumn::make('priority')
                ->label('Prioridad')
                ->colors([
                    'success' => 'low',
                    'warning' => 'medium',
                    'danger' => 'high',
                    'danger' => 'urgent',
                ])
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'low' => 'Baja',
                    'medium' => 'Media',
                    'high' => 'Alta',
                    'urgent' => 'Urgente',
                }),
            
            TextColumn::make('due_date')
                ->label('Vencimiento')
                ->date('d/m/Y'),
        ];
    }
}
