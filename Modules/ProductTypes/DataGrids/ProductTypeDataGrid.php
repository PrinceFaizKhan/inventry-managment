<?php

namespace Modules\ProductTypes\DataGrids;

use Modules\Core\DataGrid\Action;
use Modules\Core\DataGrid\Column;
use Modules\Core\DataGrid\DataGrid;
use Modules\ProductTypes\Models\ProductType;

class ProductTypeDataGrid extends DataGrid
{
    public function build(): void
    {
        $this->source(
            ProductType::query()
                ->select(['id', 'name', 'code', 'created_at'])
                ->withCount([
                    'groups',
                    'allGroupAttributes as attributes_count',
                    'variants as variants_count',
                ])
        )
            ->primaryKey('id')
            ->searchable(['name', 'code'])
            ->defaultSort('name', 'asc')
            ->searchPlaceholder('Search by code or name...')
            ->columns([
                Column::make('code')->label('Code')->sortable()->searchable(),
                Column::make('name')->label('Name')->sortable()->searchable(),
                Column::make('groups_count')->label('Groups')->sortable(false)->align('center'),
                Column::make('attributes_count')->label('Attributes')->sortable(false)->align('center'),
                Column::make('variants_count')->label('Variant Types')->sortable(false)->align('center'),
                Column::make('created_at')->label('Created At')->sortable(),
            ])
            ->actions([
                Action::edit('catalog.product-types.edit', fn ($row) => url('catalog/product-types/'.$row->id.'/edit')),
                Action::delete('catalog.product-types.delete', fn ($row) => url('api/catalog/product-types/'.$row->id)),
            ]);
    }
}
