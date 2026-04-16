<?php

namespace App\Livewire\Report;

use App\Actions\Exports\SystemReportExport;
use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\Vendor;
use App\PurchaseOrderStatus;
use App\RfqStatus;
use App\VendorStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Title('System Reports')]
class Index extends Component
{
    /**
     * Mount the component.
     */
    public function mount(): void
    {
        Gate::authorize('report.view');
    }

    /**
     * Export system report to Excel.
     */
    public function exportToExcel(): BinaryFileResponse
    {
        Gate::authorize('report.export');

        $vendorStatusCounts = $this->vendorStatusCounts();
        $rfqStatusCounts = $this->rfqStatusCounts();
        $purchaseOrderStatus = $this->purchaseOrderStatus();
        $invoiceStatusCounts = $this->invoiceStatusCounts();
        $monthlyComparisons = $this->monthlyComparisons();
        $topVendors = $this->topVendors();

        return Excel::download(
            new SystemReportExport(
                summary: [
                    'total_vendor' => array_sum($vendorStatusCounts),
                    'total_rfq' => array_sum($rfqStatusCounts),
                    'total_po' => array_sum(array_column($purchaseOrderStatus, 'count')),
                    'total_invoice' => array_sum($invoiceStatusCounts),
                ],
                vendorStatusCounts: $vendorStatusCounts,
                rfqStatusCounts: $rfqStatusCounts,
                purchaseOrderStatus: $purchaseOrderStatus,
                invoiceStatusCounts: $invoiceStatusCounts,
                monthlyComparisons: $monthlyComparisons
                    ->map(fn (array $row): array => $row)
                    ->all(),
                topVendors: $topVendors
                    ->map(fn (Vendor $vendor): array => [
                        'vendor' => $vendor->company_name,
                        'contact' => $vendor->user?->name ?? '-',
                        'rfq_joined' => (int) $vendor->rfqs_count,
                        'po_count' => (int) $vendor->purchase_orders_count,
                        'total_transaction' => (float) ($vendor->total_transaction_amount ?? 0),
                    ])
                    ->all(),
            ),
            'system-report-'.now()->format('Ymd_His').'.xlsx',
        );
    }

    public function render(): View
    {
        $vendorStatusCounts = $this->vendorStatusCounts();
        $rfqStatusCounts = $this->rfqStatusCounts();
        $purchaseOrderStatus = $this->purchaseOrderStatus();
        $invoiceStatusCounts = $this->invoiceStatusCounts();
        $monthlyComparisons = $this->monthlyComparisons();
        $topVendors = $this->topVendors();

        $totalVendor = array_sum($vendorStatusCounts);
        $totalRfq = array_sum($rfqStatusCounts);
        $totalPo = array_sum(array_column($purchaseOrderStatus, 'count'));
        $totalInvoice = array_sum($invoiceStatusCounts);

        return view('livewire.report.index', [
            'canExportReport' => Gate::allows('report.export'),
            'vendorStatusCounts' => $vendorStatusCounts,
            'rfqStatusCounts' => $rfqStatusCounts,
            'purchaseOrderStatus' => $purchaseOrderStatus,
            'invoiceStatusCounts' => $invoiceStatusCounts,
            'monthlyComparisons' => $monthlyComparisons,
            'topVendors' => $topVendors,
            'totalVendor' => $totalVendor,
            'totalRfq' => $totalRfq,
            'totalPo' => $totalPo,
            'totalInvoice' => $totalInvoice,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function vendorStatusCounts(): array
    {
        return [
            'approved' => Vendor::query()->where('status', VendorStatus::Approved)->count(),
            'pending' => Vendor::query()->where('status', VendorStatus::Pending)->count(),
            'rejected' => Vendor::query()->where('status', VendorStatus::Rejected)->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function rfqStatusCounts(): array
    {
        return [
            'open' => Rfq::query()->where('status', RfqStatus::Open)->count(),
            'closed' => Rfq::query()->where('status', RfqStatus::Closed)->count(),
        ];
    }

    /**
     * @return array<string, array{count: int, value: float}>
     */
    private function purchaseOrderStatus(): array
    {
        $draftCount = PurchaseOrder::query()->where('status', PurchaseOrderStatus::Draft)->count();
        $approvedCount = PurchaseOrder::query()->where('status', PurchaseOrderStatus::Approved)->count();
        $completedCount = PurchaseOrder::query()->where('status', PurchaseOrderStatus::Completed)->count();

        return [
            'draft' => [
                'count' => $draftCount,
                'value' => (float) PurchaseOrder::query()
                    ->where('status', PurchaseOrderStatus::Draft)
                    ->sum('total_price'),
            ],
            'approved' => [
                'count' => $approvedCount,
                'value' => (float) PurchaseOrder::query()
                    ->where('status', PurchaseOrderStatus::Approved)
                    ->sum('total_price'),
            ],
            'completed' => [
                'count' => $completedCount,
                'value' => (float) PurchaseOrder::query()
                    ->where('status', PurchaseOrderStatus::Completed)
                    ->sum('total_price'),
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function invoiceStatusCounts(): array
    {
        return [
            'pending' => Invoice::query()->where('status', InvoiceStatus::Pending)->count(),
            'approved' => Invoice::query()->where('status', InvoiceStatus::Approved)->count(),
            'rejected' => Invoice::query()->where('status', InvoiceStatus::Rejected)->count(),
        ];
    }

    /**
     * @return Collection<int, array{label: string, vendors: int, rfqs: int, pos: int, invoices: int}>
     */
    private function monthlyComparisons(): Collection
    {
        return collect(range(5, 0))
            ->map(function (int $monthOffset): array {
                $from = now()->subMonths($monthOffset)->startOfMonth();
                $to = $from->copy()->endOfMonth();

                return [
                    'label' => $from->format('M Y'),
                    'vendors' => Vendor::query()->whereBetween('created_at', [$from, $to])->count(),
                    'rfqs' => Rfq::query()->whereBetween('created_at', [$from, $to])->count(),
                    'pos' => PurchaseOrder::query()->whereBetween('created_at', [$from, $to])->count(),
                    'invoices' => Invoice::query()->whereBetween('created_at', [$from, $to])->count(),
                ];
            });
    }

    /**
     * @return Collection<int, Vendor>
     */
    private function topVendors(): Collection
    {
        return Vendor::query()
            ->with('user:id,name')
            ->withCount(['rfqs', 'purchaseOrders'])
            ->withSum('purchaseOrders as total_transaction_amount', 'total_price')
            ->orderByDesc('total_transaction_amount')
            ->limit(8)
            ->get();
    }
}
