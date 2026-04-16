<?php

namespace App\Actions\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class SystemReportExport implements FromArray, ShouldAutoSize, WithTitle
{
    /**
     * @param  array<string, int>  $summary
     * @param  array<string, int>  $vendorStatusCounts
     * @param  array<string, int>  $rfqStatusCounts
     * @param  array<string, array{count: int, value: float}>  $purchaseOrderStatus
     * @param  array<string, int>  $invoiceStatusCounts
     * @param  array<int, array{label: string, vendors: int, rfqs: int, pos: int, invoices: int}>  $monthlyComparisons
     * @param  array<int, array{vendor: string, contact: string, rfq_joined: int, po_count: int, total_transaction: float}>  $topVendors
     */
    public function __construct(
        private readonly array $summary,
        private readonly array $vendorStatusCounts,
        private readonly array $rfqStatusCounts,
        private readonly array $purchaseOrderStatus,
        private readonly array $invoiceStatusCounts,
        private readonly array $monthlyComparisons,
        private readonly array $topVendors,
    ) {}

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [
            ['System Report'],
            ['Generated At', now()->format('Y-m-d H:i:s')],
            [],
            ['Summary KPI', 'Value'],
            ['Total Vendor', $this->summary['total_vendor'] ?? 0],
            ['Total RFQ', $this->summary['total_rfq'] ?? 0],
            ['Total Purchase Order', $this->summary['total_po'] ?? 0],
            ['Total Invoice', $this->summary['total_invoice'] ?? 0],
            [],
            ['Vendor Status', 'Total'],
            ['Approved', $this->vendorStatusCounts['approved'] ?? 0],
            ['Pending', $this->vendorStatusCounts['pending'] ?? 0],
            ['Rejected', $this->vendorStatusCounts['rejected'] ?? 0],
            [],
            ['RFQ Status', 'Total'],
            ['Open', $this->rfqStatusCounts['open'] ?? 0],
            ['Closed', $this->rfqStatusCounts['closed'] ?? 0],
            [],
            ['PO Status', 'Count', 'Total Value'],
        ];

        foreach ($this->purchaseOrderStatus as $status => $meta) {
            $rows[] = [strtoupper($status), $meta['count'] ?? 0, number_format((float) ($meta['value'] ?? 0), 2)];
        }

        $rows[] = [];
        $rows[] = ['Invoice Status', 'Total'];
        $rows[] = ['Pending', $this->invoiceStatusCounts['pending'] ?? 0];
        $rows[] = ['Approved', $this->invoiceStatusCounts['approved'] ?? 0];
        $rows[] = ['Rejected', $this->invoiceStatusCounts['rejected'] ?? 0];
        $rows[] = [];
        $rows[] = ['Monthly Comparison'];
        $rows[] = ['Month', 'Vendor', 'RFQ', 'PO', 'Invoice'];

        if ($this->monthlyComparisons === []) {
            $rows[] = ['No data available'];
        } else {
            foreach ($this->monthlyComparisons as $monthlyComparison) {
                $rows[] = [
                    $monthlyComparison['label'] ?? '',
                    $monthlyComparison['vendors'] ?? 0,
                    $monthlyComparison['rfqs'] ?? 0,
                    $monthlyComparison['pos'] ?? 0,
                    $monthlyComparison['invoices'] ?? 0,
                ];
            }
        }

        $rows[] = [];
        $rows[] = ['Top Vendors by Transaction'];
        $rows[] = ['Vendor', 'Contact', 'RFQ Joined', 'PO Count', 'Total Transaction'];

        if ($this->topVendors === []) {
            $rows[] = ['No data available'];
        } else {
            foreach ($this->topVendors as $topVendor) {
                $rows[] = [
                    $topVendor['vendor'] ?? '',
                    $topVendor['contact'] ?? '',
                    $topVendor['rfq_joined'] ?? 0,
                    $topVendor['po_count'] ?? 0,
                    number_format((float) ($topVendor['total_transaction'] ?? 0), 2),
                ];
            }
        }

        return $rows;
    }

    public function title(): string
    {
        return 'System Report';
    }
}
