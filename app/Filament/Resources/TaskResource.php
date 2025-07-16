<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    
    protected static ?string $navigationLabel = 'Tareas';
    
    protected static ?string $modelLabel = 'Tarea';
    
    protected static ?string $pluralModelLabel = 'Tareas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Tarea')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->disabled(),
                        
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->disabled(),
                        
                        DatePicker::make('due_date')
                            ->label('Fecha de Vencimiento')
                            ->disabled(),
                        
                        Select::make('priority')
                            ->label('Prioridad')
                            ->options([
                                'low' => 'Baja',
                                'medium' => 'Media',
                                'high' => 'Alta',
                                'urgent' => 'Urgente',
                            ])
                            ->disabled(),
                        
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'in_progress' => 'En Progreso',
                                'blocked' => 'Bloqueada',
                                'completed' => 'Completada',
                                'cancelled' => 'Cancelada',
                            ])
                            ->disabled(),
                    ])->columns(2),
                
                Section::make('Asignación')
                    ->schema([
                        Select::make('assigned_user_id')
                            ->label('Usuario Asignado')
                            ->options(User::all()->pluck('name', 'id'))
                            ->disabled(),
                        
                        Select::make('department_id')
                            ->label('Departamento')
                            ->options(Department::all()->pluck('name', 'id'))
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->searchable(),
                
                TextColumn::make('due_date')
                    ->label('Fecha de Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Task $record): string => $record->isOverdue() ? 'danger' : 'success'),
                
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
                
                TextColumn::make('assignedUser.name')
                    ->label('Usuario Asignado')
                    ->searchable(),
                
                TextColumn::make('department.name')
                    ->label('Departamento')
                    ->searchable(),
                
                TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'in_progress' => 'En Progreso',
                        'blocked' => 'Bloqueada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ]),
                
                SelectFilter::make('priority')
                    ->label('Prioridad')
                    ->options([
                        'low' => 'Baja',
                        'medium' => 'Media',
                        'high' => 'Alta',
                        'urgent' => 'Urgente',
                    ]),
                
                SelectFilter::make('department_id')
                    ->label('Departamento')
                    ->options(Department::all()->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'view' => Pages\ViewTask::route('/{record}'),
        ];
    }
}
