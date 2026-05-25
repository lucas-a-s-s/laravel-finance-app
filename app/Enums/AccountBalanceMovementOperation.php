<?php

namespace App\Enums;

enum AccountBalanceMovementOperation: string
{
    case Applied = 'applied';
    case Reversed = 'reversed';
}
