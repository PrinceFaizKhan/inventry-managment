<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{
    /**
     * Return translated strings for the specified or current locale.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $request->query('locale') ?? app()->getLocale();

        // Normalize locale code to short or full
        $langKey = str_starts_with($locale, 'ur') ? 'ur' : 'en';
        $fullLocale = $langKey === 'ur' ? 'ur-IN' : 'en-IN';

        $messages = [];

        // 1. Load base lang/app.php
        $basePaths = [
            base_path("lang/{$langKey}/app.php"),
            base_path("lang/{$fullLocale}/app.php"),
        ];
        foreach ($basePaths as $bp) {
            if (File::exists($bp)) {
                $data = require $bp;
                if (is_array($data)) {
                    $messages = array_merge($messages, $data);
                }
            }
        }

        // 2. Discover all module and sub-menu translation files
        $langFiles = array_unique([
            ...File::glob(base_path("Modules/*/Resources/lang/{$langKey}/app.php")),
            ...File::glob(base_path("Modules/*/Resources/lang/{$fullLocale}/app.php")),
            ...File::glob(base_path("Modules/*/*/Resources/lang/{$langKey}/app.php")),
            ...File::glob(base_path("Modules/*/*/Resources/lang/{$fullLocale}/app.php")),
        ]);

        foreach ($langFiles as $filePath) {
            $data = require $filePath;
            if (! is_array($data)) {
                continue;
            }

            // Derive namespace prefix, e.g. "Modules/Catalog/Attributes/Resources/lang/en/app.php" -> "catalog.attributes"
            $relative = str_replace('\\', '/', substr($filePath, strlen(base_path('Modules/'))));
            $parts = explode('/', $relative);
            // parts e.g. ['Catalog', 'Attributes', 'Resources', 'lang', 'en', 'app.php'] or ['Admin', 'Resources', 'lang', 'en', 'app.php']
            $langIdx = array_search('Resources', $parts, true);
            if ($langIdx !== false) {
                $scopeParts = array_slice($parts, 0, $langIdx);
                $namespace = strtolower(implode('.', $scopeParts));

                foreach ($data as $k => $v) {
                    $messages["{$namespace}.{$k}"] = $v;
                    $messages["{$namespace}::{$k}"] = $v;
                    if (in_array($namespace, ['attribute', 'attributes'], true)) {
                        $messages["catalog.attributes.{$k}"] = $v;
                        $messages["catalog.attributes::{$k}"] = $v;
                    }
                    // Also expose root key if not already defined
                    if (! isset($messages[$k])) {
                        $messages[$k] = $v;
                    }
                }
            }
        }

        return response()->json([
            'locale' => $fullLocale,
            'direction' => $langKey === 'ur' ? 'rtl' : 'ltr',
            'messages' => $messages,
        ]);
    }
}
