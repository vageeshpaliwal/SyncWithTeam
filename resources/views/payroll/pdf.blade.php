<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Salary Slip - {{ $payroll->payslip_number }}</title>
    <style>
        @page {
            margin: 12mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .org-name {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }
        .period-badge {
            background-color: #eff6ff;
            color: #1d4ed8;
            font-weight: bold;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            margin-bottom: 10px;
            padding: 4px 6px;
        }
        .info-table td {
            padding: 2.5px 4px;
        }
        .label {
            color: #6b7280;
            font-size: 8px;
            text-transform: uppercase;
            font-family: monospace;
        }
        .val {
            font-weight: bold;
            font-size: 10px;
        }
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .breakdown-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        .breakdown-table td {
            border: 1px solid #e5e7eb;
            padding: 3px 5px;
            vertical-align: top;
        }
        .inner-table {
            width: 100%;
            border-collapse: collapse;
        }
        .inner-table td {
            border: none;
            border-bottom: 1px dashed #e5e7eb;
            padding: 2.5px 2px;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .net-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 8px 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .net-val {
            font-size: 15px;
            font-weight: bold;
            color: #1d4ed8;
        }
        .signatures {
            width: 100%;
            margin-top: 25px;
        }
        .signatures td {
            text-align: center;
            padding-top: 20px;
        }
        .line {
            border-top: 1px solid #374151;
            width: 130px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                <div class="org-name">{{ $payroll->organization->name ?? 'SyncWithTeam Workspace' }}</div>
                <div style="color: #6b7280; font-size: 9px;">{{ $payroll->organization->address ?? 'Corporate Office' }} @if($payroll->organization->email) &bull; {{ $payroll->organization->email }} @endif</div>
            </td>
            <td class="text-right">
                <div class="period-badge">SALARY SLIP &bull; {{ $payroll->formatted_month_year }}</div>
                <div style="color: #6b7280; font-size: 9px; margin-top: 3px;">Ref: <strong>{{ $payroll->payslip_number }}</strong> | Date: {{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : $payroll->created_at->format('d M Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Employee & Bank Info & Attendance in Condensed Table -->
    <table class="info-table">
        <tr>
            <td width="25%"><span class="label">Employee Name:</span><br><span class="val">{{ $payroll->user->full_name }}</span></td>
            <td width="25%"><span class="label">Employee ID:</span><br><span class="val">EMP-{{ str_pad($payroll->user->id, 4, '0', STR_PAD_LEFT) }}</span></td>
            <td width="25%"><span class="label">Designation:</span><br><span class="val">{{ $payroll->user->display_designation }}</span></td>
            <td width="25%"><span class="label">Department:</span><br><span class="val">{{ $payroll->user->department?->name ?? 'General' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Bank Name:</span><br><span class="val">{{ $payroll->user->bank_name ?? 'N/A' }}</span></td>
            <td><span class="label">Account Number:</span><br><span class="val">{{ $payroll->user->account_number ?? 'N/A' }}</span></td>
            <td><span class="label">IFSC Code:</span><br><span class="val">{{ $payroll->user->ifsc_code ?? 'N/A' }}</span></td>
            <td><span class="label">PAN Number:</span><br><span class="val">{{ $payroll->user->pan_number ?? 'N/A' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Calendar Days:</span><br><span class="val">{{ $payroll->working_days }}</span></td>
            <td><span class="label">Present Days:</span><br><span class="val" style="color: #16a34a;">{{ $payroll->present_days }}</span></td>
            <td><span class="label">Paid Leaves:</span><br><span class="val" style="color: #2563eb;">{{ $payroll->paid_leaves }}</span></td>
            <td><span class="label">Loss of Pay (LOP):</span><br><span class="val" style="color: #dc2626;">{{ $payroll->unpaid_leaves }}</span></td>
        </tr>
    </table>

    <!-- Breakdown Table -->
    <table class="breakdown-table">
        <thead>
            <tr>
                <th width="50%" style="color: #16a34a;">Earnings (INR)</th>
                <th width="50%" style="color: #dc2626;">Deductions (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <table class="inner-table">
                        <tr>
                            <td>Basic Salary</td>
                            <td class="text-right">&#8377;{{ number_format($payroll->basic_salary, 2) }}</td>
                        </tr>
                        @if(is_array($payroll->allowances))
                            @foreach($payroll->allowances as $a)
                                <tr>
                                    <td>{{ $a['name'] ?? 'Allowance' }}</td>
                                    <td class="text-right">&#8377;{{ number_format((float)($a['amount'] ?? 0), 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                </td>
                <td>
                    <table class="inner-table">
                        @if(is_array($payroll->deductions) && count($payroll->deductions) > 0)
                            @foreach($payroll->deductions as $d)
                                <tr>
                                    <td>{{ $d['name'] ?? 'Deduction' }}</td>
                                    <td class="text-right">&#8377;{{ number_format((float)($d['amount'] ?? 0), 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td style="font-style: italic; color: #9ca3af;">No deductions</td>
                                <td class="text-right">&#8377;0.00</td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
            <tr style="background-color: #f9fafb; font-weight: bold;">
                <td>
                    <table class="inner-table">
                        <tr>
                            <td>Total Gross Earnings (A)</td>
                            <td class="text-right">&#8377;{{ number_format($payroll->gross_salary, 2) }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="inner-table">
                        <tr>
                            <td>Total Deductions (B)</td>
                            <td class="text-right" style="color: #dc2626;">&#8377;{{ number_format($payroll->total_deductions, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Net Salary Box -->
    <div class="net-box">
        <table width="100%">
            <tr>
                <td>
                    <div class="label" style="font-size: 9px; font-weight: bold;">Net Salary Payable (A - B)</div>
                    <div class="net-val">&#8377;{{ number_format($payroll->net_salary, 2) }}</div>
                    <div style="font-size: 9px; font-style: italic; color: #4b5563; margin-top: 1px;">{{ $payroll->net_salary_in_words }}</div>
                </td>
                @if($payroll->payment_date)
                    <td class="text-right">
                        <div style="color: #16a34a; font-weight: bold; font-size: 10px;">Direct Transfer Disbursed</div>
                        <div style="color: #6b7280; font-size: 8.5px;">Date: {{ $payroll->payment_date->format('d M Y') }}</div>
                        @if($payroll->transaction_reference)
                            <div style="color: #6b7280; font-size: 8px;">Ref: {{ $payroll->transaction_reference }}</div>
                        @endif
                    </td>
                @endif
            </tr>
        </table>
    </div>

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td width="50%">
                <div class="line"></div>
                <div style="font-size: 8.5px; color: #6b7280; margin-top: 3px;">Employee Signature</div>
            </td>
            <td width="50%">
                <div class="line"></div>
                <div style="font-size: 8.5px; color: #6b7280; margin-top: 3px;">Authorized Signatory</div>
            </td>
        </tr>
    </table>

    <div style="text-align: center; margin-top: 15px; font-size: 8px; color: #9ca3af;">
        This is a computer-generated salary document from {{ config('app.name', 'SyncWithTeam') }} and does not require a physical seal.
    </div>

</body>
</html>