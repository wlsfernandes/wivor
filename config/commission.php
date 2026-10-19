<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default WivorPhotos commission percentage
    |--------------------------------------------------------------------------
    |
    | The platform-wide commission percentage applied to every order item.
    | An administrator can override it per event; events without an override
    | always follow this value.
    |
    */

    'percentage' => (float) env('WIVOR_COMMISSION_PERCENTAGE', 20),

    /*
    |--------------------------------------------------------------------------
    | Old default packet (bundle) discount percentage
    |--------------------------------------------------------------------------
    |
    | No longer used for pricing: events now set package prices. It is read only
    | by the 2026_10_18_030000 migration, which converts each event's bundle
    | discount into a 3-photo package price. Remove it together with the unused
    | events.packet_discount_percentage column.
    |
    */

    'packet_percentage' => (float) env('WIVOR_PACKET_PERCENTAGE', 20),
];
