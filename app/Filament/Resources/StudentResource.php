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

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->searchable()
                    ->sortable(),

                TextInput::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextInput::make('phone_number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextInput::make('address')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap(),

                Select::make('class_id')
                    ->relationship('class', 'name'),
                Select::make('section_id')
                    ->options(function (callable $get) {
                        $classId = $get('class_id');

                        if ($classId) {
                            return Section::where('class_id', $classId)->pluck('name', 'id')->toArray();
                        }
                    }),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone_number')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                // TextColumn::make('address')
                //     ->sortable()
                //     ->searchable()
                //     ->toggleable()
                //     ->wrap(),

                TextColumn::make('class.name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('section.name')
                    ->sortable()
                    ->searchable()
            ])
            ->filters([
                    Select::make('class_id')

                    ->options(
                        Classes::pluck('name', 'id')->toArray()
                    ),
                Forms\components\DatePicker::make('created_until'),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['created_from'],
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                    )
                    ->when(
                        $data['created_until'],
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                    );
            })
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                BulkAction::make('export')
                ->label('Export Selected')
                ->icon('heroicon-o-document-download')
                    ->action(fn (Collection $records) => (new StudentsExport($records))->download('students.xlsx'))
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }    
}
