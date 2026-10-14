<?php

use Illuminate\Support\HtmlString;
use Modules\Core\Support\ViewEvent\ViewRenderEventManager;

if (! function_exists('view_event_render')) {
    /**
     * Render view event hooks registered by modules.
     *
     * @param  array<string, mixed>  $params
     */
    function view_event_render(string $event, array $params = []): HtmlString
    {
        return app(ViewRenderEventManager::class)->render($event, $params);
    }
}
