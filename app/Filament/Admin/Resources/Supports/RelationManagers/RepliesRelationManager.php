<?php

namespace App\Filament\Admin\Resources\Supports\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RepliesRelationManager extends RelationManager
{
    protected static string $relationship = 'replies';
    protected static ?string $title = 'Lịch sử phản hồi';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('sender_type')
                ->label('Người gửi')
                ->required()
                ->options([
                    'user' => '👨‍💼 Nhân viên',
                    'customer' => '👤 Khách hàng',
                ])
                ->default('user')
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state === 'user') {
                        $set('user_id', Auth::id());
                        $set('customer_id', null);
                    } else {
                        $set('user_id', null);
                    }
                }),

            Select::make('user_id')
                ->label('Nhân viên')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->default(Auth::id())
                ->visible(fn(callable $get) => $get('sender_type') === 'user'),

            Select::make('customer_id')
                ->label('Khách hàng')
                ->default(fn() => $this->getOwnerRecord()->customer_id)
                ->options(fn() => \App\Models\Customer::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->visible(fn(callable $get) => $get('sender_type') === 'customer'),

            RichEditor::make('content')
                ->label('Nội dung')
                ->required()
                ->toolbarButtons([
                    'bold',
                    'italic',
                    'underline',
                    'bulletList',
                    'orderedList',
                    'link',
                ])
                ->columnSpanFull(),

            FileUpload::make('file_path')
                ->label('File đính kèm')
                ->maxSize(10240)
                ->acceptedFileTypes([
                    'image/*',
                    'application/pdf',
                    'application/msword',
                    'application/zip',
                ])
                ->helperText('Tối đa 10MB')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sender_type')
                    ->label('Người gửi')
                    ->formatStateUsing(fn($state, $record) => $state === 'user'
                        ? '👨‍💼 ' . ($record->user?->name ?? 'Nhân viên')
                        : '👤 ' . ($record->customer?->name ?? 'Khách hàng'))
                    ->badge()
                    ->color(fn(string $state) => $state === 'user' ? 'info' : 'warning'),

                TextColumn::make('content')
                    ->label('Nội dung')
                    ->html()
                    ->limit(80)
                    ->wrap(),

                TextColumn::make('file_path')
                    ->label('File')
                    ->placeholder('—')
                    ->formatStateUsing(fn($state) => $state ? '📎 Có file' : '—'),

                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Thêm phản hồi')
                    ->mutateFormDataUsing(function (array $data) {
                        if ($data['sender_type'] === 'user') {
                            $data['user_id'] = $data['user_id'] ?? Auth::id();
                            $data['customer_id'] = null;
                        } else {
                            $data['user_id'] = null;
                        }
                        return $data;
                    }),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin'])),
            ])
            ->defaultSort('created_at', 'asc')
            ->emptyStateHeading('Chưa có phản hồi nào')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}