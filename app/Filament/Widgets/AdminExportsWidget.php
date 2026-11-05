<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AdminExportsWidget extends Widget
{
    protected string $view = 'admin.widgets.admin-exports';

    protected int|string|array $columnSpan = 'full';
}
