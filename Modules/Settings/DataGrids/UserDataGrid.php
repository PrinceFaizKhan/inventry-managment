<?php

namespace Modules\Settings\DataGrids;

use App\Models\Role;
use App\Models\User;
use Modules\Core\DataGrid\Action;
use Modules\Core\DataGrid\BulkAction;
use Modules\Core\DataGrid\Column;
use Modules\Core\DataGrid\DataGrid;
use Modules\Core\DataGrid\Filter;

class UserDataGrid extends DataGrid
{
    public function build(): void
    {
        $this->source(
            User::query()
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.status',
                    'users.role_id',
                    'roles.name as role',
                ])
        )
            ->primaryKey('id')
            ->searchable(['users.name', 'users.email'])
            ->defaultSort('users.name', 'asc')
            ->searchPlaceholder('Search by name or email...')
            ->columns([
                Column::make('name')->label('User')->sortable()->searchable(),
                Column::make('email')->label('Email')->sortable()->searchable(),
                Column::make('role')->label('Role')->sortable(),
                Column::make('status')->label('Status')->sortable()->align('center'),
            ])
            ->filters([
                Filter::select('role_id')->label('Role')->options(fn () => Role::query()->pluck('name', 'id')->all()),
                Filter::select('status')->label('Status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ]),
            ])
            ->actions([
                Action::toggleStatus('settings.users.edit', fn ($row) => url('api/settings/users/'.$row->id.'/toggle-status')),
                Action::edit('settings.users.edit', fn ($row) => url('settings/users/'.$row->id.'/edit')),
                Action::delete('settings.users.delete', fn ($row) => url('api/settings/users/'.$row->id)),
            ])
            ->bulkActions([
                BulkAction::make('activate_selected')->label('Activate Selected')->authorize('settings.users.activate'),
                BulkAction::make('deactivate_selected')->label('Deactivate Selected')->authorize('settings.users.deactivate'),
                BulkAction::delete('settings.users.delete'),
            ]);
    }
}
