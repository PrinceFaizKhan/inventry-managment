<?php

namespace Modules\Attribute\DataGrids;

use Modules\Attribute\Models\AttributeGroup;
use Modules\Core\DataGrid\Action;
use Modules\Core\DataGrid\Column;
use Modules\Core\DataGrid\DataGrid;

class AttributeGroupDataGrid extends DataGrid
{
    public function build(): void
    {
        $this->source(AttributeGroup::query()->select(['id', 'name', 'code', 'created_at']))
            ->primaryKey('id')
            ->searchable(['name', 'code'])
            ->defaultSort('name', 'asc')
            ->searchPlaceholder('Search by code or name...')
            ->columns([
                Column::make('code')->label('Code')->sortable()->searchable(),
                Column::make('name')->label('Name')->sortable()->searchable(),
                Column::make('created_at')->label('Created At')->sortable(),
            ])
            ->actions([
                Action::edit('catalog.attribute-groups.edit', fn ($row) => url('catalog/attribute-groups/'.$row->id.'/edit')),
                Action::delete('catalog.attribute-groups.delete', fn ($row) => url('api/catalog/attribute-groups/'.$row->id)),
            ]);
    }
}
