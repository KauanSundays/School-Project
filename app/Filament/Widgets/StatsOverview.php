<?php

namespace App\Filament\Widgets;

use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        return [
            Card::make('Total de Alunos', Student::count()),
            Card::make('Total de Salas', Classes::count()),
            Card::make('Total de Seções', Section::count()),
        ];
    }
}