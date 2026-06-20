<?php

namespace App\Filament\Admin\Resources\Contracts\Pages;

use App\Filament\Admin\Resources\Contracts\ContractResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditContract extends EditRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_child_contract')
                ->label('Tạo phụ lục / Gia hạn')
                ->icon('heroicon-o-document-plus')
                ->color('primary')
                ->visible(
                    fn() =>
                    in_array($this->record->status, ['Đang hiệu lực', 'Hết hạn'])
                    && Auth::user()->hasAnyRole(['super_admin', 'admin', 'sales'])
                )
                ->form([
                    \Filament\Forms\Components\TextInput::make('title')
                        ->label('Tiêu đề phụ lục')
                        ->required()
                        ->maxLength(255)
                        ->default(fn() => 'Phụ lục ' . $this->record->code),

                    \Filament\Forms\Components\Select::make('type')
                        ->label('Loại')
                        ->required()
                        ->options([
                            'Phụ lục' => 'Phụ lục',
                            'Gia hạn' => 'Gia hạn',
                            'Điều chỉnh' => 'Điều chỉnh giá trị',
                        ])
                        ->default('Phụ lục'),

                    \Filament\Forms\Components\DatePicker::make('start_date')
                        ->label('Ngày bắt đầu')
                        ->displayFormat('d/m/Y')
                        ->default(fn() => $this->record->end_date?->addDay()),

                    \Filament\Forms\Components\DatePicker::make('end_date')
                        ->label('Ngày kết thúc')
                        ->displayFormat('d/m/Y')
                        ->afterOrEqual('start_date'),

                    \Filament\Forms\Components\TextInput::make('contract_value')
                        ->label('Giá trị phụ lục')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->prefix('VNĐ'),
                ])
                ->action(function (array $data) {
                    $parent = $this->record;

                    $child = \App\Models\Contract::create([
                        'customer_id' => $parent->customer_id,
                        'project_id' => $parent->project_id,
                        'parent_id' => $parent->id,
                        'title' => $data['title'],
                        'type' => $data['type'],
                        'status' => 'Bản nháp',
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'contract_value' => $data['contract_value'] ?? 0,
                        'has_vat' => $parent->has_vat,
                        'vat_percent' => $parent->vat_percent,
                        'total' => $data['contract_value'] ?? 0,
                        'created_by' => Auth::id(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Đã tạo ' . $child->code)
                        ->body('Phụ lục được tạo ở trạng thái Bản nháp.')
                        ->send();

                    $this->redirect(
                        ContractResource::getUrl('edit', ['record' => $child->id])
                    );
                }),
            RestoreAction::make(),

            DeleteAction::make()
                ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin']))
                ->before(function ($action) {
                    if ($this->record->children()->exists()) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Không thể xoá')
                            ->body('Hợp đồng này còn ' . $this->record->children()->count() . ' phụ lục/gia hạn.')
                            ->send();
                        $action->cancel();
                    }
                }),

            ForceDeleteAction::make()
                ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'admin']))
                ->before(function ($action) {
                    if ($this->record->children()->exists()) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Không thể xoá')
                            ->body('Hợp đồng này còn ' . $this->record->children()->count() . ' phụ lục/gia hạn.')
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }
}
