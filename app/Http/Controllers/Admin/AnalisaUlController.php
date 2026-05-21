<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AnalisaController;

class AnalisaUlController extends AnalisaController
{
    protected bool $isAdminContext = true;
    protected string $productType = 'unit_link';
    protected string $productLabel = 'Unit Link';
}
