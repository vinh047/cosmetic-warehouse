<?php

namespace App\Enums;

enum InventoryReferenceType: string
{
    case ORDER = 'order';
    case PURCHASE = 'purchase';
    case ADJUSTMENT = 'adjustment';
}
