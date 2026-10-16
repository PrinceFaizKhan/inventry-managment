<?php

namespace Modules\Attribute\Actions;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Attribute\AttributeCodeGenerator;
use Modules\Attribute\AttributeTypeRegistry;
use Modules\Attribute\Models\Attribute;

class CreateAttribute
{
    public function __construct(private readonly AttributeTypeRegistry $types) {}

    /** @param array{name: string, type: string} $data */
    public function execute(array $data): Attribute
    {
        $code = AttributeCodeGenerator::fromName($data['name']);
        if ($code === '') {
            throw ValidationException::withMessages(['code' => 'This name does not produce a valid attribute code.']);
        }

        try {
            return DB::transaction(fn (): Attribute => Attribute::query()->create([
                'name' => $data['name'],
                'code' => $code,
                'type' => $data['type'],
                'required' => false,
                'is_filterable' => false,
                'settings' => $this->types->defaults($data['type']),
            ]));
        } catch (QueryException $exception) {
            if (in_array($exception->errorInfo[0] ?? null, ['23000', '23505'], true)) {
                throw ValidationException::withMessages(['code' => 'This attribute code already exists. Choose a different name.']);
            }

            throw $exception;
        }
    }
}
