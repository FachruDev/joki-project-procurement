<?php

namespace App;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Completed = 'completed';
}
