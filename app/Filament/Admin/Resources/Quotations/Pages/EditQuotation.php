<?php

namespace App\Filament\Admin\Resources\Quotations\Pages;

use App\Filament\Admin\Resources\Quotations\QuotationResource;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('convert_to_project')
                ->label('Chuyển thành Dự án')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->visible(fn() => in_array($this->record->status, ['Chấp nhận']) && !$this->record->projects()->exists())
                ->form([
                    TextInput::make('name')
                        ->label('Tên dự án')
                        ->required()
                        ->maxLength(50)
                        ->default(fn() => $this->record->title),

                    Select::make('type')
                        ->label('Loại dự án')
                        ->required()
                        ->options([
                            'Website' => 'Website',
                            'Phần mềm' => 'Phần mềm',
                            'App Mobile' => 'App Mobile',
                            'Bảo trì' => 'Bảo trì',
                            'Tên miền' => 'Tên miền',
                            'Hosting' => 'Hosting',
                            'Khác' => 'Khác',
                        ]),

                    Select::make('priority')
                        ->label('Độ ưu tiên')
                        ->options([
                            'Thấp' => 'Thấp',
                            'Trung bình' => 'Trung bình',
                            'Cao' => 'Cao',
                            'Khẩn cấp' => 'Khẩn cấp',
                        ])
                        ->default('Trung bình'),

                    DatePicker::make('start_date')
                        ->label('Ngày bắt đầu')
                        ->default(now()),

                    DatePicker::make('end_date')
                        ->label('Ngày kết thúc dự kiến'),

                    Select::make('manager_id')
                        ->label('Người phụ trách')
                        ->options(fn() => User::pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data) {
                    $quotation = $this->record;

                    Project::create([
                        'customer_id' => $quotation->customer_id,
                        'quotation_id' => $quotation->id,
                        'name' => $data['name'],
                        'type' => $data['type'],
                        'priority' => $data['priority'],
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'contract_value' => $quotation->total,
                        'status' => 'Lên kế hoạch',
                        'manager_id' => $data['manager_id'] ?? null,
                        'created_by' => Auth::id(),
                    ]);

                    $quotation->update(['status' => 'Đã chuyển DA']);

                    Notification::make()
                        ->success()
                        ->title('Đã chuyển thành dự án thành công')
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            RestoreAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
