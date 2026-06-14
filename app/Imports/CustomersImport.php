<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    public int $skipped = 0;
    public int $imported = 0;

    private array $typeMap = [
        'individual' => 'Cá nhân',
        'company' => 'Công ty',
    ];

    private array $statusMap = [
        'active' => 'Đang hoạt động',
        'potential' => 'Tiềm năng',
        'inactive' => 'Ngừng hoạt động',
    ];

    public function model(array $row): ?Customer
    {
        $email = $row['email'] ?? null;
        $phone = $row['phone'] ?? null;

        if ($email && Customer::where('email', $email)->exists()) {
            $this->skipped++;
            return null;
        }

        if ($phone && Customer::where('phone', $phone)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return new Customer([
            'name' => $row['name'],
            'company_name' => $row['company_name'] ?? null,
            'email' => $email,
            'phone' => $phone,
            'tax_code' => $row['tax_code'] ?? null,
            'address' => $row['address'] ?? null,
            'website' => $row['website'] ?? null,
            'type' => $this->typeMap[$row['type'] ?? 'individual'] ?? 'Cá nhân',
            'status' => $this->statusMap[$row['status'] ?? 'potential'] ?? 'Tiềm năng',
            'note' => $row['note'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:10',
            'type' => 'nullable|in:individual,company',
            'status' => 'nullable|in:active,potential,inactive',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'Cột "name" không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'type.in' => 'Loại phải là "individual" hoặc "company".',
            'status.in' => 'Trạng thái phải là "active", "potential" hoặc "inactive".',
        ];
    }
}