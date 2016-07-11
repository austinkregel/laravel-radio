<?php

return [
    'local-routes' => false,
    'middleware-api' => ['api', 'jwt.auth'],
    'middleware' => ['web', 'auth'],
    'base-layout' => 'vendor.spark.layouts.app'
];