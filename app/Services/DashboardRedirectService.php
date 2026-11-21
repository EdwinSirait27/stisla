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
            'dashboardManager'    => 'pages.dashboardManager',
            'dashboardTeam'       => 'pages.dashboardTeam',
            'dashboardDirector'   => 'pages.dashboardDirector',
            'dashboardHR'         => 'pages.dashboardHR',
            'ManageEmployeeTraining'         => 'pages.employee-training',
        ];
    }

    /** Nama route dashboard pertama yang cocok dengan permission user, null kalau tidak ada. */
    public static function routeNameFor(User $user): ?string
    {
        foreach (self::routes() as $permission => $route) {
            // checkPermissionTo: false (bukan exception) kalau permission belum terdaftar
            if ($user->checkPermissionTo($permission)) {
                return $route;
            }
        }
        return null;
    }

    public static function redirectForUser(User $user): ?\Illuminate\Http\RedirectResponse
    {
        $route = self::routeNameFor($user);

        return $route ? redirect()->route($route) : null;
    }
}