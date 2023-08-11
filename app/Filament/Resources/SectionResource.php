<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Filament\Resources\SectionResource\RelationManagers;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('class')
                    ->required()
                    ->autofocus()
                    ->unique()
                    ->placeholder('Enter a class Section Name'),

                Select::make('class_id')
                    ->relationship('class', 'name')
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name')
                ->sortable() //classificavel
                ->searchable(),
            TextColumn::make('class.name') //segunda coluna
                ->sortable()
                ->searchable(),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            ActionsDeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }    
}
