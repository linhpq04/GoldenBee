<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected ?Collection $records = null)
    {
    }

    public function collection(): Collection
    {
        if ($this->records) {
            return $this->records;
        }

        return Customer::all();
    }

    public function headings(): array
    {
        return [
            'Mã KH',
            'Loại',
            'Tên khách hàng',
            'Tên công ty',
            'Mã số thuế',
            'Email',
            'Số điện thoại',
            'Địa chỉ',
            'Website',
            'Nguồn',
            'Trạng thái',
            'Ghi chú',
        ];
    }

    public function map($row): array
    {
        return [
            $row->code,
            $row->type,
            $row->name,
            $row->company_name,
            $row->tax_code,
            $row->email,
            $row->phone,
            $row->address,
            $row->website,
            $row->source,
            $row->status,
            $row->note,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}