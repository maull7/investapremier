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

    protected function formRoutes(): array
    {
        return [
            'layout'          => 'layouts.admin',
            'store'           => route('admin.analisa-rd.store'),
            'template'        => route('admin.analisa-rd.template'),
            'cancel'          => route('admin.reksa-dana.index'),
            'parse_pdf'       => route('admin.analisa-rd.parse-pdf'),
            'preview_ai'      => route('admin.analisa-rd.preview-ai'),
            'preview_ai_plus' => route('admin.analisa-rd.preview-ai-plus'),
        ];
    }
}
