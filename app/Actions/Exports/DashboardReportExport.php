<?php

namespace App\Actions\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DashboardReportExport implements FromArray, ShouldAutoSize, WithTitle
{
    /**
     * @param  array<string, int>  $summary
     * @param  array<int, array<string, mixed>>  $vendors
     * @param  array<int, array<string, mixed>>  $rfqs
     * @param  array<int, array<string, mixed>>  $purchaseOrders
     * @param  array<int, array<string, mixed>>  $invoices
     */
    public function __construct(
        private readonly array $summary,
        private readonly array $vendors,
        private readonly array $rfqs,
        private readonly array $purchaseOrders,
        private readonly array $invoices,
    ) {}

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [
            ['Dashboard Report'],
            ['Generated At', now()->format('Y-m-d H:i:s')],
            [],
            ['Summary KPI', 'Value'],
            ['Total Vendor', $this->summary['total_vendors'] ?? 0],
            ['Vendor Approved', $this->summary['approved_vendors'] ?? 0],
            ['Vendor Pending', $this->summary['pending_vendors'] ?? 0],
            ['RFQ Aktif', $this->summary['active_rfqs'] ?? 0],
            ['PO Aktif', $this->summary['active_pos'] ?? 0],
            ['Invoice Pending', $this->summary['pending_invoices'] ?? 0],
            [],
        ];

        $this->appendSection(
            rows: $rows,
            title: 'Vendor Report',
            headings: ['Vendor', 'Status', 'RFQ Joined', 'PO Won', 'Total Transaction'],
            items: $this->vendors,
            keys: ['company_name', 'status', 'rfq_joined', 'po_won', 'total_transaction'],
        );

        $this->appendSection(
            rows: $rows,
            title: 'RFQ Report',
            headings: ['RFQ', 'Vendor Joined', 'Selected Vendor', 'Status'],
            items: $this->rfqs,
            keys: ['title', 'vendor_joined', 'selected_vendor', 'status'],
        );

        $this->appendSection(
            rows: $rows,
            title: 'Purchase Report (PO)',
            headings: ['PO #', 'Vendor', 'Total Price', 'Status', 'Date'],
            items: $this->purchaseOrders,
            keys: ['po_number', 'vendor', 'total_price', 'status', 'date'],
        );

        $this->appendSection(
            rows: $rows,
            title: 'Invoice Report',
            headings: ['Invoice #', 'Vendor', 'Value', 'Status', 'Date'],
            items: $this->invoices,
            keys: ['invoice_number', 'vendor', 'value', 'status', 'date'],
        );

        return $rows;
    }

    public function title(): string
    {
        return 'Dashboard Report';
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>  $headings
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, string>  $keys
     */
    private function appendSection(array &$rows, string $title, array $headings, array $items, array $keys): void
    {
        $rows[] = [$title];
        $rows[] = $headings;

        if ($items === []) {
            $rows[] = ['No data available'];
            $rows[] = [];

            return;
        }

        foreach ($items as $item) {
            $row = [];

            foreach ($keys as $key) {
                $row[] = $item[$key] ?? '';
            }

            $rows[] = $row;
        }

        $rows[] = [];
    }
}
