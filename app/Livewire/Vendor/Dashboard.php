<?php

namespace App\Livewire\Vendor;

use App\Actions\Exports\DashboardReportExport;
use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\User;
use App\Models\Vendor;
use App\PurchaseOrderStatus;
use App\RfqStatus;
use App\VendorStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Title('Dashboard')]
class Dashboard extends Component
{
    use WithPagination;

    public int $perPage = 8;

    /**
     * Export dashboard reports to an Excel file.
     */
    public function exportToExcel(): BinaryFileResponse
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $vendor = $user->vendor;

        $canViewVendorSummary = $user->can('report.vendor.summary');
        $canViewVendorReport = $this->canViewVendorReport($user);
        $canViewRfqReport = $user->can('rfq.view');
        $canViewPurchaseReport = $user->can('po.view');
        $canViewInvoiceReport = $user->can('invoice.approve') || $user->can('invoice.upload') || $user->can('invoice.view');

        $vendorScope = $this->vendorScope($user, $vendor);

        $summary = [
            'total_vendors' => $canViewVendorSummary ? (clone $vendorScope)->count() : 0,
            'approved_vendors' => $canViewVendorSummary
                ? (clone $vendorScope)->where('status', VendorStatus::Approved)->count()
                : 0,
            'pending_vendors' => $canViewVendorSummary
                ? (clone $vendorScope)->where('status', VendorStatus::Pending)->count()
                : 0,
            'active_rfqs' => $canViewRfqReport
                ? (clone $this->rfqScope($vendor))->where('status', RfqStatus::Open)->count()
                : 0,
            'active_pos' => $canViewPurchaseReport
                ? (clone $this->purchaseOrderScope($vendor))
                    ->whereIn('status', [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Approved])
                    ->count()
                : 0,
            'pending_invoices' => $canViewInvoiceReport
                ? (clone $this->invoiceScope($vendor))->where('status', InvoiceStatus::Pending)->count()
                : 0,
        ];

        $vendors = $canViewVendorReport
            ? (clone $vendorScope)
                ->withCount(['rfqs', 'purchaseOrders'])
                ->withSum('purchaseOrders as total_transaction_amount', 'total_price')
                ->orderBy('company_name')
                ->get()
                ->map(fn (Vendor $vendorItem): array => [
                    'company_name' => $vendorItem->company_name,
                    'status' => strtoupper($vendorItem->status->value),
                    'rfq_joined' => $vendorItem->rfqs_count,
                    'po_won' => $vendorItem->purchase_orders_count,
                    'total_transaction' => number_format((float) ($vendorItem->total_transaction_amount ?? 0), 2),
                ])
                ->all()
            : [];

        $rfqs = $canViewRfqReport
            ? (clone $this->rfqScope($vendor))
                ->withCount('vendors')
                ->with([
                    'purchaseOrders' => fn ($query) => $query
                        ->latest('id')
                        ->with('vendor:id,company_name'),
                ])
                ->orderByDesc('id')
                ->get()
                ->map(fn (Rfq $rfqItem): array => [
                    'title' => $rfqItem->title,
                    'vendor_joined' => $rfqItem->vendors_count,
                    'selected_vendor' => $rfqItem->purchaseOrders->first()?->vendor?->company_name ?? '-',
                    'status' => strtoupper($rfqItem->status->value),
                ])
                ->all()
            : [];

        $purchaseOrders = $canViewPurchaseReport
            ? (clone $this->purchaseOrderScope($vendor))
                ->with('vendor:id,company_name')
                ->orderByDesc('id')
                ->get()
                ->map(fn (PurchaseOrder $purchaseOrder): array => [
                    'po_number' => '#'.$purchaseOrder->id,
                    'vendor' => $purchaseOrder->vendor?->company_name ?? '-',
                    'total_price' => number_format((float) $purchaseOrder->total_price, 2),
                    'status' => strtoupper($purchaseOrder->status->value),
                    'date' => $purchaseOrder->created_at?->format('Y-m-d H:i') ?? '-',
                ])
                ->all()
            : [];

        $invoices = $canViewInvoiceReport
            ? (clone $this->invoiceScope($vendor))
                ->with(['vendor:id,company_name', 'purchaseOrder:id,total_price'])
                ->orderByDesc('id')
                ->get()
                ->map(fn (Invoice $invoice): array => [
                    'invoice_number' => '#'.$invoice->id,
                    'vendor' => $invoice->vendor?->company_name ?? '-',
                    'value' => number_format((float) ($invoice->purchaseOrder?->total_price ?? 0), 2),
                    'status' => strtoupper($invoice->status->value),
                    'date' => $invoice->created_at?->format('Y-m-d H:i') ?? '-',
                ])
                ->all()
            : [];

        return Excel::download(
            new DashboardReportExport(
                summary: $summary,
                vendors: $vendors,
                rfqs: $rfqs,
                purchaseOrders: $purchaseOrders,
                invoices: $invoices,
            ),
            'dashboard-report-'.now()->format('Ymd_His').'.xlsx',
        );
    }

    public function render(): View
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $vendor = $user->vendor;

        $canViewVendorSummary = $user->can('report.vendor.summary');
        $canViewVendorReport = $this->canViewVendorReport($user);
        $canViewRfqReport = $user->can('rfq.view');
        $canViewPurchaseReport = $user->can('po.view');
        $canViewInvoiceReport = $user->can('invoice.approve') || $user->can('invoice.upload') || $user->can('invoice.view');

        $vendorScope = $this->vendorScope($user, $vendor);

        $totalVendors = $canViewVendorSummary ? (clone $vendorScope)->count() : 0;
        $approvedVendorsCount = $canViewVendorSummary
            ? (clone $vendorScope)->where('status', VendorStatus::Approved)->count()
            : 0;
        $pendingVendorsCount = $canViewVendorSummary
            ? (clone $vendorScope)->where('status', VendorStatus::Pending)->count()
            : 0;
        $rejectedVendorsCount = $canViewVendorSummary
            ? (clone $vendorScope)->where('status', VendorStatus::Rejected)->count()
            : 0;

        $activeRfqsCount = $canViewRfqReport
            ? (clone $this->rfqScope($vendor))->where('status', RfqStatus::Open)->count()
            : 0;

        $activePoCount = $canViewPurchaseReport
            ? (clone $this->purchaseOrderScope($vendor))
                ->whereIn('status', [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Approved])
                ->count()
            : 0;

        $pendingInvoicesCount = $canViewInvoiceReport
            ? (clone $this->invoiceScope($vendor))->where('status', InvoiceStatus::Pending)->count()
            : 0;

        $vendorStatusChart = $this->buildVendorStatusChart(
            approvedCount: $approvedVendorsCount,
            pendingCount: $pendingVendorsCount,
            rejectedCount: $rejectedVendorsCount,
        );

        $operationalBars = $this->buildOperationalBars(
            activeRfqsCount: $activeRfqsCount,
            activePoCount: $activePoCount,
            pendingInvoicesCount: $pendingInvoicesCount,
        );

        $vendorsReport = $canViewVendorReport
            ? (clone $vendorScope)
                ->with('user:id,name')
                ->withCount(['rfqs', 'purchaseOrders'])
                ->withSum('purchaseOrders as total_transaction_amount', 'total_price')
                ->orderBy('company_name')
                ->paginate($this->perPage, ['*'], 'vendorsPage')
            : Vendor::query()->whereRaw('1 = 0')->paginate($this->perPage, ['*'], 'vendorsPage');

        $rfqsReport = $canViewRfqReport
            ? (clone $this->rfqScope($vendor))
                ->withCount('vendors')
                ->with([
                    'purchaseOrders' => fn ($query) => $query
                        ->latest('id')
                        ->with('vendor:id,company_name'),
                ])
                ->orderByDesc('id')
                ->paginate($this->perPage, ['*'], 'rfqsPage')
            : Rfq::query()->whereRaw('1 = 0')->paginate($this->perPage, ['*'], 'rfqsPage');

        $purchaseOrdersReport = $canViewPurchaseReport
            ? (clone $this->purchaseOrderScope($vendor))
                ->with('vendor:id,company_name')
                ->orderByDesc('id')
                ->paginate($this->perPage, ['*'], 'purchaseOrdersPage')
            : PurchaseOrder::query()->whereRaw('1 = 0')->paginate($this->perPage, ['*'], 'purchaseOrdersPage');

        $invoicesReport = $canViewInvoiceReport
            ? (clone $this->invoiceScope($vendor))
                ->with(['vendor:id,company_name', 'purchaseOrder:id,total_price'])
                ->orderByDesc('id')
                ->paginate($this->perPage, ['*'], 'invoicesPage')
            : Invoice::query()->whereRaw('1 = 0')->paginate($this->perPage, ['*'], 'invoicesPage');

        return view('livewire.vendor.dashboard', [
            'vendor' => $vendor,
            'isVendorPending' => $vendor?->status === VendorStatus::Pending,
            'canViewVendorSummary' => $canViewVendorSummary,
            'canViewVendorReport' => $canViewVendorReport,
            'canViewRfqReport' => $canViewRfqReport,
            'canViewPurchaseReport' => $canViewPurchaseReport,
            'canViewInvoiceReport' => $canViewInvoiceReport,
            'totalVendors' => $totalVendors,
            'approvedVendorsCount' => $approvedVendorsCount,
            'pendingVendorsCount' => $pendingVendorsCount,
            'activeRfqsCount' => $activeRfqsCount,
            'activePoCount' => $activePoCount,
            'pendingInvoicesCount' => $pendingInvoicesCount,
            'vendorStatusChart' => $vendorStatusChart,
            'operationalBars' => $operationalBars,
            'vendorsReport' => $vendorsReport,
            'rfqsReport' => $rfqsReport,
            'purchaseOrdersReport' => $purchaseOrdersReport,
            'invoicesReport' => $invoicesReport,
        ]);
    }

    private function vendorScope(User $user, ?Vendor $vendor): Builder
    {
        $query = Vendor::query();

        if (! $user->can('vendor.manage')) {
            if ($vendor === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereKey($vendor->id);
            }
        }

        return $query;
    }

    /**
     * Determine if vendor report section should be visible.
     */
    private function canViewVendorReport(User $user): bool
    {
        if ($user->hasRole('Vendor')) {
            return false;
        }

        return $user->can('vendor.manage');
    }

    private function rfqScope(?Vendor $vendor): Builder
    {
        $query = Rfq::query();

        if ($vendor !== null) {
            $query->whereHas('vendors', fn ($rfqVendorQuery) => $rfqVendorQuery->whereKey($vendor->id));
        }

        return $query;
    }

    private function purchaseOrderScope(?Vendor $vendor): Builder
    {
        $query = PurchaseOrder::query();

        if ($vendor !== null) {
            $query->where('vendor_id', $vendor->id);
        }

        return $query;
    }

    private function invoiceScope(?Vendor $vendor): Builder
    {
        $query = Invoice::query();

        if ($vendor !== null) {
            $query->where('vendor_id', $vendor->id);
        }

        return $query;
    }

    /**
     * @return array{
     *     approved_percent: float,
     *     pending_percent: float,
     *     rejected_percent: float,
     *     background: string
     * }
     */
    private function buildVendorStatusChart(int $approvedCount, int $pendingCount, int $rejectedCount): array
    {
        $total = $approvedCount + $pendingCount + $rejectedCount;

        if ($total === 0) {
            return [
                'approved_percent' => 0,
                'pending_percent' => 0,
                'rejected_percent' => 0,
                'background' => 'conic-gradient(#d4d4d8 0% 100%)',
            ];
        }

        $approvedPercent = round(($approvedCount / $total) * 100, 2);
        $pendingPercent = round(($pendingCount / $total) * 100, 2);
        $rejectedPercent = round(100 - $approvedPercent - $pendingPercent, 2);

        $approvedStop = $approvedPercent;
        $pendingStop = $approvedPercent + $pendingPercent;

        return [
            'approved_percent' => $approvedPercent,
            'pending_percent' => $pendingPercent,
            'rejected_percent' => $rejectedPercent,
            'background' => sprintf(
                'conic-gradient(#16a34a 0%% %.2f%%, #f59e0b %.2f%% %.2f%%, #ef4444 %.2f%% 100%%)',
                $approvedStop,
                $approvedStop,
                $pendingStop,
                $pendingStop,
            ),
        ];
    }

    /**
     * @return array<int, array{label: string, value: int, percent: float, color_class: string}>
     */
    private function buildOperationalBars(
        int $activeRfqsCount,
        int $activePoCount,
        int $pendingInvoicesCount,
    ): array {
        $metrics = [
            ['label' => 'RFQ Aktif', 'value' => $activeRfqsCount, 'color_class' => 'bg-cyan-500'],
            ['label' => 'PO Aktif', 'value' => $activePoCount, 'color_class' => 'bg-indigo-500'],
            ['label' => 'Invoice Pending', 'value' => $pendingInvoicesCount, 'color_class' => 'bg-amber-500'],
        ];

        $maximumValue = max(array_column($metrics, 'value'));

        if ($maximumValue === 0) {
            $maximumValue = 1;
        }

        return array_map(
            fn (array $metric): array => [
                'label' => $metric['label'],
                'value' => $metric['value'],
                'percent' => round(($metric['value'] / $maximumValue) * 100, 2),
                'color_class' => $metric['color_class'],
            ],
            $metrics,
        );
    }
}
