<?php

namespace App\Filament\Admin\Resources\Customers\Pages;

use App\Exports\CustomersExport;
use App\Filament\Admin\Resources\Customers\CustomerResource;
use App\Imports\CustomersImport;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tải mẫu Excel
            Action::make('download_template')
                ->label('Tải mẫu Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    return response()->download(
                        Storage::disk('public')->path('templates/mau_import_khach_hang.xlsx'),
                        'Mau_Import_Khach_Hang.xlsx'
                    );
                }),

            // Export Excel
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->action(function (Excel $excel) {
                    $filename = 'khach_hang_' . now()->format('dmY_His') . '.xlsx';
                    return $excel->download(new CustomersExport(), $filename);
                }),

            // Import Excel
            Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('Chọn file Excel')
                        ->disk('public')
                        ->directory('imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                ])
                ->action(function (array $data, Excel $excel) {
                    $path = storage_path('app/public/' . $data['file']);

                    try {
                        $import = new CustomersImport();
                        $excel->import($import, $path);

                        $errors = $import->errors();

                        if ($errors->isNotEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title('Import hoàn tất nhưng có lỗi')
                                ->body("Đã thêm {$import->imported} khách hàng. Bỏ qua {$import->skipped} trùng lặp. Một số dòng không hợp lệ.")
                                ->send();
                        } elseif ($import->skipped > 0) {
                            Notification::make()
                                ->warning()
                                ->title('Import hoàn tất')
                                ->body("Đã thêm {$import->imported} khách hàng. Bỏ qua {$import->skipped} bản ghi trùng lặp (email hoặc số điện thoại đã tồn tại).")
                                ->send();
                        } else {
                            Notification::make()
                                ->success()
                                ->title('Import thành công')
                                ->body("Đã thêm {$import->imported} khách hàng.")
                                ->send();
                        }
                    } catch (Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Import thất bại')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            // Thêm khách hàng
            CreateAction::make()
                ->label('Thêm khách hàng'),
        ];
    }
}