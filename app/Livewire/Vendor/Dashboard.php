<?php

namespace App\Livewire\Vendor;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\Vendor;
use App\RfqStatus;
use App\VendorStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        if ($user === null) {
            abort(401);
        }

        $vendor = $user->vendor;

        $openRfqsCount = 0;
        if ($user->can('rfq.view')) {
            if ($vendor === null) {
                $openRfqsCount = Rfq::query()->where('status', RfqStatus::Open)->count();
            } else {
                $openRfqsCount = Rfq::query()
                    ->where('status', RfqStatus::Open)
                    ->whereHas('vendors', fn ($query) => $query->whereKey($vendor->id))
                    ->count();
            }
        }

        $purchaseOrdersCount = 0;
        if ($user->can('po.view')) {
            if ($vendor === null) {
                $purchaseOrdersCount = PurchaseOrder::query()->count();
            } else {
                $purchaseOrdersCount = PurchaseOrder::query()->where('vendor_id', $vendor->id)->count();
            }
        }

        $pendingInvoicesCount = 0;
        if ($user->can('invoice.approve')) {
            $pendingInvoicesCount = Invoice::query()->where('status', InvoiceStatus::Pending)->count();
        } elseif ($vendor !== null) {
            $pendingInvoicesCount = Invoice::query()
                ->where('vendor_id', $vendor->id)
                ->where('status', InvoiceStatus::Pending)
                ->count();
        }

        $pendingVendorApprovals = $user->can('vendor.manage')
            ? Vendor::query()->where('status', VendorStatus::Pending)->count()
            : 0;

        return view('livewire.vendor.dashboard', [
            'vendor' => $vendor,
            'isVendorPending' => $vendor?->status === VendorStatus::Pending,
            'openRfqsCount' => $openRfqsCount,
            'purchaseOrdersCount' => $purchaseOrdersCount,
            'pendingInvoicesCount' => $pendingInvoicesCount,
            'pendingVendorApprovals' => $pendingVendorApprovals,
        ]);
    }
}
