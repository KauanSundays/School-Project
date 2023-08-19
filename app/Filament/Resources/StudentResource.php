<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Exports\StudentsExport;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\StudentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StudentResource\RelationManagers;
use Filament\Tables\Actions\Action;

class StudentResource extends Resource // Declara uma classe 'StudentResource' que herda da classe base 'Resource'
{
    protected static ?string $model = Student::class; // Define o modelo associado ao recurso como 'Student'

    protected static ?string $navigationGroup = 'Academic Management'; // Define o grupo de navegação como 'Academic Management'

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap'; // Define o ícone de navegação como 'heroicon-o-academic-cap'

    public static function form(Form $form): Form // Define a estrutura do formulário para criação/edição de estudantes
    {
        return $form
            ->schema([
                TextInput::make('name') // Campo de entrada de texto para o nome do estudante
                    ->required() // Requerido
                    ->autofocus() // Foca automaticamente
                    ->unique(), // Deve ser único

                TextInput::make('email') // Campo de entrada de texto para o e-mail do estudante
                    ->required() // Requerido
                    ->unique(), // Deve ser único

                TextInput::make('phone_number') // Campo de entrada de texto para o número de telefone do estudante
                    ->required() // Requerido
                    ->tel() // Formato de telefone
                    ->unique(), // Deve ser único

                TextInput::make('address') // Campo de entrada de texto para o endereço do estudante
                    ->required(), // Requerido

                Select::make('class_id') // Campo de seleção para a classe do estudante
                    ->relationship('class', 'name') // Relação com o modelo 'class' usando o campo 'name'
                    ->reactive(), // Atualização reativa dos componentes dependentes

                Select::make('section_id') // Campo de seleção para a seção do estudante
                    ->label('Select Section') // Rótulo do campo
                    ->options(function (callable $get) { // Função para definir opções dinamicamente
                        $classId = $get('class_id'); // Obtém o valor do campo 'class_id'

                        if ($classId) { // Se a classe estiver selecionada
                            return Section::where('class_id', $classId)->pluck('name', 'id')->toArray();
                            // Obtém as seções associadas a essa classe
                        }
                    })
            ]);
    }

    public static function table(Table $table): Table // Define a estrutura da tabela para listar estudantes
    {
        return $table
            ->columns([
                TextColumn::make('name') // Coluna de texto para o nome do estudante
                    ->sortable() // Pode ser ordenada
                    ->searchable(), // Pode ser pesquisada

                TextColumn::make('email') // Coluna de texto para o e-mail do estudante
                    ->sortable() // Pode ser ordenada
                    ->searchable() // Pode ser pesquisada
                    ->toggleable(), // Pode ser alternada

                TextColumn::make('phone_number') // Coluna de texto para o número de telefone do estudante
                    ->sortable() // Pode ser ordenada
                    ->searchable() // Pode ser pesquisada
                    ->toggleable(), // Pode ser alternada

                TextColumn::make('class.name') // Coluna de texto para o nome da classe do estudante
                    ->sortable() // Pode ser ordenada
                    ->searchable(), // Pode ser pesquisada

                TextColumn::make('section.name') // Coluna de texto para o nome da seção do estudante
                    ->sortable() // Pode ser ordenada
                    ->searchable() // Pode ser pesquisada
            ])
            ->filters([
                Filter::make('class-section-filter') // Define um filtro chamado 'class-section-filter'
                    ->form([
                        Select::make('class_id') // Campo de seleção para filtrar por classe
                            ->label('Filter By Class') // Rótulo do campo
                            ->placeholder('Select a Class') // Texto de placeholder
                            ->options(Classes::pluck('name', 'id')->toArray()) // Obtém opções de classes
                            ->afterStateUpdated(
                                fn (callable $set) => $set('section_id', null)
                            ),
                        Select::make('section_id') // Campo de seleção para filtrar por seção
                            ->label('Filter By Section') // Rótulo do campo
                            ->placeholder('Select a Section') // Texto de placeholder
                            ->options(
                                function (callable $get) {
                                    $classId = $get('class_id'); // Obtém o valor do campo 'class_id'

                                    if ($classId) { // Se a classe estiver selecionada
                                        return Section::where('class_id', $classId)->pluck('name', 'id')->toArray();
                                        // Obtém as seções associadas a essa classe
                                    }
                                }
                            ),
                    ])
                    ->query(function (Builder $query, array $data): Builder { // Função para modificar a consulta da tabela com base nos filtros
                        return $query
                            ->when(
                                $data['class_id'],
                                fn (Builder $query, $record): Builder => $query->where('class_id', $record),
                            )
                            ->when(
                                $data['section_id'],
                                fn (Builder $query, $record): Builder => $query->where('section_id', $record),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Adiciona uma ação de edição
                DeleteAction::make(), // Adiciona uma ação de exclusão
                Action::make('Download Pdf') // Adiciona uma ação personalizada para download de PDF
                    ->icon('heroicon-o-document-download') // Define o ícone da ação
                    ->openUrlInNewTab(), // Abre a URL em uma nova aba
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(), // Adiciona uma ação de exclusão em massa
                BulkAction::make('export') // Adiciona uma ação em massa personalizada para exportar
                    ->label('Export Selected') // Rótulo da ação
                    ->icon('heroicon-o-document-download') // Define o ícone da ação
                    ->action(fn (Collection $records) => (new StudentsExport($records))->download('students.xlsx')) // Ação a ser executada
            ]);
    }

    public static function getRelations(): array // Define as relações associadas ao recurso
    {
        return [
            //
        ];
    }

    public static function getPages(): array // Define as páginas associadas ao recurso
    {
        return [
            'index' => Pages\ListStudents::route('/'), // Página de listagem de estudantes
            'create' => Pages\CreateStudent::route('/create'), // Página de criação de estudantes
            'edit' => Pages\EditStudent::route('/{record}/edit'), // Página de edição de estudantes
        ];
    }

    protected static function getNavigationBadge(): ?string // Define o distintivo de navegação
    {
        return self::$model::count(); // Retorna a contagem de estudantes como distintivo
    }
}
