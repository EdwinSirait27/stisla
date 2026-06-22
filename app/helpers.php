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
    /** @var \App\Models\User|null $user */

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
     elseif ($user->hasRole('Training')) {
        return 'pages.employee-training';
    }

    return 'dashboard'; // default fallback
}
