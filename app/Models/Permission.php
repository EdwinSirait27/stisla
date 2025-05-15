<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Ramsey\Uuid\Uuid;

use Illuminate\Notifications\Notifiable;
class Permission extends SpatiePermission
{

}