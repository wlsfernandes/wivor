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
    | Default packet (bundle) discount percentage
    |--------------------------------------------------------------------------
    |
    | The discount a customer receives when buying at least three photos from
    | one event. An administrator can override it per event. Set to 0 to turn
    | the packet discount off.
    |
    */

    'packet_percentage' => (float) env('WIVOR_PACKET_PERCENTAGE', 20),
];
