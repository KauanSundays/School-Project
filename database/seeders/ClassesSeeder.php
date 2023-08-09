<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cria 10 turmas fictícias usando a factory do modelo Classes.
        Classes::factory()
            ->count(10) // Cria 10 turmas
            ->sequence(fn ($sequence) => ['name' => 'Class ' . $sequence->index + 1]) // Nomes sequenciais como "Class 1", "Class 2", ...
            ->has(
                // Para cada turma, cria 2 seções fictícias usando a factory do modelo Section.
                Section::factory()
                    ->count(2) // Cria 2 seções por turma
                    ->state(
                        new Sequence(
                            ['name' => 'Section A'],
                            ['name' => 'Section B'],
                            ['name' => 'Section C'],
                        )
                    )
                    ->has(
                        // Para cada seção, cria 5 estudantes fictícios usando a factory do modelo Student.
                        Student::factory()
                            ->count(5) // Cria 5 estudantes por seção
                            ->state(
                                function (array $attributes, Section $section) {
                                    // Associa o ID da turma à seção dos alunos.
                                    return ['class_id' => $section->class_id];
                                }
                            )
                    )
            )
            ->create(); // Finaliza a criação e insere os dados no banco de dados.
    }
}
