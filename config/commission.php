<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Global WivorPhotos commission percentage
    |--------------------------------------------------------------------------
    |
    | The single, platform-wide commission percentage applied to every order
    | subtotal. The MVP does not support per-photographer or per-event
    | commission plans.
    |
    */

    'percentage' => (float) env('WIVOR_COMMISSION_PERCENTAGE', 20),
];
