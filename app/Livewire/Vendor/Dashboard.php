<?php

namespace App\Livewire\Vendor;

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

#[Title('Dashboard')]
class Dashboard extends Component
{
    public int $recentLimit = 6;

    public function render(): View
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $vendor = $user->vendor;
        $isVendorUser = $user->hasRole('Vendor');

        $canViewVendorSummary = $user->can('report.vendor.summary');
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

        $recentRfqs = $canViewRfqReport
            ? (clone $this->rfqScope($vendor))
                ->withCount('vendors')
                ->latest('id')
                ->limit($this->recentLimit)
                ->get()
            : collect();

        $recentPurchaseOrders = $canViewPurchaseReport
            ? (clone $this->purchaseOrderScope($vendor))
                ->with('vendor:id,company_name')
                ->latest('id')
                ->limit($this->recentLimit)
                ->get()
            : collect();

        $recentInvoices = $canViewInvoiceReport
            ? (clone $this->invoiceScope($vendor))
                ->with(['vendor:id,company_name', 'purchaseOrder:id,total_price'])
                ->latest('id')
                ->limit($this->recentLimit)
                ->get()
            : collect();

        $recentVendorStatuses = $canViewVendorSummary
            ? (clone $vendorScope)
                ->with('user:id,name')
                ->latest('id')
                ->limit($this->recentLimit)
                ->get()
            : collect();

        $vendorChartData = $isVendorUser
            ? $this->buildVendorChartData($vendor)
            : null;

        return view('livewire.vendor.dashboard', [
            'vendor' => $vendor,
            'isVendorUser' => $isVendorUser,
            'isVendorPending' => $vendor?->status === VendorStatus::Pending,
            'canViewVendorSummary' => $canViewVendorSummary,
            'canViewRfqReport' => $canViewRfqReport,
            'canViewPurchaseReport' => $canViewPurchaseReport,
            'canViewInvoiceReport' => $canViewInvoiceReport,
            'canViewSystemReport' => $user->can('report.view'),
            'totalVendors' => $totalVendors,
            'approvedVendorsCount' => $approvedVendorsCount,
            'pendingVendorsCount' => $pendingVendorsCount,
            'activeRfqsCount' => $activeRfqsCount,
            'activePoCount' => $activePoCount,
            'pendingInvoicesCount' => $pendingInvoicesCount,
            'vendorStatusChart' => $vendorStatusChart,
            'operationalBars' => $operationalBars,
            'recentVendorStatuses' => $recentVendorStatuses,
            'recentRfqs' => $recentRfqs,
            'recentPurchaseOrders' => $recentPurchaseOrders,
            'recentInvoices' => $recentInvoices,
            'vendorChartData' => $vendorChartData,
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
     *     invoiceStatus: array{labels: array<int, string>, data: array<int, int>},
     *     monthlyTrend: array{labels: array<int, string>, rfqs: array<int, int>, pos: array<int, int>, invoices: array<int, int>}
     * }
     */
    private function buildVendorChartData(?Vendor $vendor): array
    {
        if ($vendor === null) {
            return [
                'invoiceStatus' => [
                    'labels' => [__('Pending'), __('Approved'), __('Rejected')],
                    'data' => [0, 0, 0],
                ],
                'monthlyTrend' => [
                    'labels' => collect(range(5, 0))->map(fn (int $offset): string => now()->subMonths($offset)->format('M Y'))->all(),
                    'rfqs' => array_fill(0, 6, 0),
                    'pos' => array_fill(0, 6, 0),
                    'invoices' => array_fill(0, 6, 0),
                ],
            ];
        }

        $monthlyComparisons = collect(range(5, 0))
            ->map(function (int $monthOffset) use ($vendor): array {
                $from = now()->subMonths($monthOffset)->startOfMonth();
                $to = $from->copy()->endOfMonth();

                return [
                    'label' => $from->format('M Y'),
                    'rfqs' => Rfq::query()
                        ->whereHas('vendors', fn ($rfqVendorQuery) => $rfqVendorQuery->whereKey($vendor->id))
                        ->whereBetween('created_at', [$from, $to])
                        ->count(),
                    'pos' => PurchaseOrder::query()
                        ->where('vendor_id', $vendor->id)
                        ->whereBetween('created_at', [$from, $to])
                        ->count(),
                    'invoices' => Invoice::query()
                        ->where('vendor_id', $vendor->id)
                        ->whereBetween('created_at', [$from, $to])
                        ->count(),
                ];
            });

        return [
            'invoiceStatus' => [
                'labels' => [__('Pending'), __('Approved'), __('Rejected')],
                'data' => [
                    Invoice::query()->where('vendor_id', $vendor->id)->where('status', InvoiceStatus::Pending)->count(),
                    Invoice::query()->where('vendor_id', $vendor->id)->where('status', InvoiceStatus::Approved)->count(),
                    Invoice::query()->where('vendor_id', $vendor->id)->where('status', InvoiceStatus::Rejected)->count(),
                ],
            ],
            'monthlyTrend' => [
                'labels' => $monthlyComparisons->pluck('label')->all(),
                'rfqs' => $monthlyComparisons->pluck('rfqs')->all(),
                'pos' => $monthlyComparisons->pluck('pos')->all(),
                'invoices' => $monthlyComparisons->pluck('invoices')->all(),
            ],
        ];
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
