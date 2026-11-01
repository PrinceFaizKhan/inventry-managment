<?php

namespace Modules\Attribute\DataGrids;

use Modules\Attribute\Models\Attribute;
use Modules\Core\DataGrid\Action;
use Modules\Core\DataGrid\BulkAction;
use Modules\Core\DataGrid\Column;
use Modules\Core\DataGrid\DataGrid;
use Modules\Core\DataGrid\Filter;

class AttributeDataGrid extends DataGrid
{
    public function build(): void
    {
        $this->source(Attribute::query()->select(['id', 'name', 'code', 'type', 'required', 'is_unique', 'is_filterable', 'created_at']))
            ->primaryKey('id')
            ->searchable(['name', 'code'])
            ->defaultSort('name', 'asc')
            ->searchPlaceholder('Search by code or name...')
            ->columns([
                Column::make('code')->label('Code')->sortable()->searchable(),
                Column::make('name')->label('Name')->sortable()->searchable(),
                Column::make('type')->label('Type')->sortable()->badge(),
                Column::make('required')->label('Required')->sortable()->boolean(),
                Column::make('is_unique')->label('Unique')->sortable()->boolean(),
                Column::make('is_filterable')->label('Filterable')->sortable()->boolean(),
            ])
            ->filters([
                Filter::select('type')->label('Type')->options([
                    'text' => 'Text',
                    'textarea' => 'Textarea',
                    'boolean' => 'Boolean',
                    'checkbox' => 'Checkbox',
                    'select' => 'Select',
                    'multiselect' => 'Multiselect',
                    'image' => 'Image',
                    'gallery' => 'Gallery',
                    'radio' => 'Radio',
                    'money' => 'Money',
                ]),
                Filter::boolean('required')->label('Required'),
                Filter::boolean('is_unique')->label('Unique'),
                Filter::boolean('is_filterable')->label('Filterable'),
            ])
            ->actions([
                Action::edit('catalog.attributes.edit', fn ($row) => url('catalog/attributes/'.$row->id.'/edit')),
                Action::delete('catalog.attributes.delete', fn ($row) => url('api/catalog/attributes/'.$row->id)),
            ])
            ->bulkActions([
                BulkAction::delete('catalog.attributes.delete_selected'),
            ]);
    }
}
