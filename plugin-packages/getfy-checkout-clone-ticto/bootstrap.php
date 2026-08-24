<?php

/**
 * Checkout Clone Ticto — libera o template via plugin.json
 * (`checkout_builder_templates` com core_layout / ui_variant / features).
 * O layout é renderizado no core do Getfy (CHECKOUT_CORE_LAYOUTS).
 *
 * @param  \Illuminate\Contracts\Foundation\Application  $app
 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
 */
return function ($app, $events): void {
    // Sem hooks extras: o core resolve o template pelo manifesto.
};
