<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

abstract class Controller
{
    protected function reactPage(string $component, array $props = []): View
    {
        return view('app', [
            'page' => [
                'component' => $component,
                'props' => $props,
            ],
        ]);
    }
}
