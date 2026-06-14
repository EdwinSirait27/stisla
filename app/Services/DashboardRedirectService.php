<?php

namespace App\Services;
// App\Services\DashboardRedirectService.php
use App\Models\User;

class DashboardRedirectService
{
    public static function routes(): array
    {
        return [
            'dashboardAdmin'      => 'pages.dashboardAdmin',
            'dashboardHuman'      => 'pages.dashboardHuman',
            'dashboardSupervisor' => 'pages.dashboardSupervisor',
            'dashboardTeam'       => 'pages.dashboardTeam',
            'dashboardDirector'   => 'pages.dashboardDirector',
            'dashboardHR'         => 'pages.dashboardHR',
        ];
    }

    public static function redirectForUser(User $user): ?\Illuminate\Http\RedirectResponse
    {
        foreach (self::routes() as $permission => $route) {
            if ($user->hasPermissionTo($permission)) {
                return redirect()->route($route);
            }
        }
        return null;
    }
}