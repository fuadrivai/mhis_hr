<?php

namespace App\Services;

interface AuthService
{

    function login($request, $deviceId, $device);
    function register($request);
    function changePassword($request);
    function refresh($request);
    function logout($request);
}
