<?php

namespace App\Exports;

use App\Models\Student;
use Filament\Notifications\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;

class StudentsExport implements FromQuery
{
    use Exportable;
    public $students;

    public function __constructor(Collection $students)
    {
        $this->students = $students;
    }


    public function query()
    {
        return Student::whereKey($this->students->pluck('id')->toArray());
    }
}
