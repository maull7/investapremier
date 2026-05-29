<?php

namespace App\Enums;

enum AnalisaDataSource: string
{
    case DATABASE_KEUANGAN_EMITEN = 'database_keuangan_emiten';
    case UPLOAD_EXCEL = 'upload_excel';
}
