<?php

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
if (!function_exists('uuid7')) {
    function uuid7()
    {
        return Uuid::uuid7()->toString();
    }
}
function getDashboardRoute()
{
    $user = Auth::user();

    if ($user->hasRole('Admin')) {
        return 'pages.dashboardAdmin';
    } 
    elseif ($user->hasRole('HeadHR|HR')) {
        return 'pages.dashboardHR';
    }
    elseif ($user->hasRole('Human')) {
        return 'pages.dashboardHuman';
    }
    elseif ($user->hasRole('Manager')) {
        return 'pages.dashboardManager';
    }
    elseif ($user->hasRole('Director')) {
        return 'pages.dashboardDirector';
    }
    elseif ($user->hasRole('Supervisor')) {
        return 'pages.dashboardSupervisor';
    }
     elseif ($user->hasRole('employee')) {
        return 'employee.dashboard';
    }

    return 'dashboard'; // default fallback
}