<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'user_id',
        'salary_month',
        'month',
        'year',
        'payslip_number',
        'working_days',
        'present_days',
        'paid_leaves',
        'unpaid_leaves',
        'total_hours_worked',
        'overtime_hours',
        'basic_salary',
        'allowances',
        'total_allowances',
        'deductions',
        'total_deductions',
        'gross_salary',
        'net_salary',
        'payment_status',
        'payment_method',
        'payment_date',
        'transaction_reference',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'allowances' => 'array',
        'deductions' => 'array',
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'present_days' => 'float',
        'paid_leaves' => 'float',
        'unpaid_leaves' => 'float',
        'total_hours_worked' => 'float',
        'overtime_hours' => 'float',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedMonthYearAttribute(): string
    {
        $date = \Carbon\Carbon::createFromDate($this->year, $this->month, 1);
        return $date ? $date->format('F Y') : "{$this->month}/{$this->year}";
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-success-subtle text-success border border-success-subtle',
            'processing' => 'bg-info-subtle text-info border border-info-subtle',
            'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle',
            default => 'bg-warning-subtle text-warning border border-warning-subtle',
        };
    }

    public function getNetSalaryInWordsAttribute(): string
    {
        $amount = round((float)$this->net_salary);
        if ($amount <= 0) {
            return 'Zero Rupees Only';
        }

        return $this->numberToWordsIndian($amount) . ' Rupees Only';
    }

    private function numberToWordsIndian(int $num): string
    {
        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
        ];
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        ];

        if ($num < 20) {
            return $ones[$num];
        }
        if ($num < 100) {
            return $tens[(int)($num / 10)] . ($num % 10 !== 0 ? ' ' . $ones[$num % 10] : '');
        }
        if ($num < 1000) {
            return $ones[(int)($num / 100)] . ' Hundred' . ($num % 100 !== 0 ? ' and ' . $this->numberToWordsIndian($num % 100) : '');
        }
        if ($num < 100000) {
            return $this->numberToWordsIndian((int)($num / 1000)) . ' Thousand' . ($num % 1000 !== 0 ? ' ' . $this->numberToWordsIndian($num % 1000) : '');
        }
        if ($num < 10000000) {
            return $this->numberToWordsIndian((int)($num / 100000)) . ' Lakh' . ($num % 100000 !== 0 ? ' ' . $this->numberToWordsIndian($num % 100000) : '');
        }

        return $this->numberToWordsIndian((int)($num / 10000000)) . ' Crore' . ($num % 10000000 !== 0 ? ' ' . $this->numberToWordsIndian($num % 10000000) : '');
    }
}