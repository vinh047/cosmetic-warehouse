<?php

namespace App\Enums;

enum OrderChannel: string
{
    case OFFLINE = 'offline';
    case ONLINE  = 'online';
}
