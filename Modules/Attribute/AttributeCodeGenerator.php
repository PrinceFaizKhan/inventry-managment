<?php

namespace Modules\Attribute;

class AttributeCodeGenerator
{
    public static function fromName(string $name): string
    {
        $code = preg_replace('/[^a-z0-9]+/', '_', strtolower($name));

        return trim($code ?? '', '_');
    }
}
