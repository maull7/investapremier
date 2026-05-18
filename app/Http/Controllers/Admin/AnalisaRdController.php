<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AnalisaController;

class AnalisaRdController extends AnalisaController
{
    protected bool $isAdminContext = true;

    protected function indexRoute(): string
    {
        return 'admin.reksa-dana.index';
    }
}
