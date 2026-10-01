<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'access', 'permissions'])]
class Role extends Model
{
    protected function casts(): array
    {
        return ['permissions' => AsArrayObject::class];
    }
}
