<?php

use App\Livewire\GR\Create as GrCreate;
use App\Livewire\Invoice\Approve as InvoiceApprove;
use App\Livewire\Invoice\Upload as InvoiceUpload;
use App\Livewire\PO\Create as PoCreate;
use App\Livewire\PO\Index as PoIndex;
use App\Livewire\PO\Show as PoShow;
use App\Livewire\RFQ\Create as RfqCreate;
use App\Livewire\RFQ\Index as RfqIndex;
use App\Livewire\RFQ\Respond as RfqRespond;
use App\Livewire\RFQ\Show as RfqShow;
use App\Livewire\Vendor\Dashboard;
use App\Livewire\Vendor\Profile as VendorProfile;
use App\Livewire\Vendor\Register as VendorRegister;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');

    Route::livewire('vendors', VendorRegister::class)
        ->middleware('can:vendor.manage')
        ->name('vendor.register');

    Route::livewire('vendor/profile', VendorProfile::class)
        ->middleware('can:rfq.respond')
        ->name('vendor.profile');

    Route::livewire('rfqs', RfqIndex::class)
        ->middleware('can:rfq.view')
        ->name('rfqs.index');

    Route::livewire('rfqs/create', RfqCreate::class)
        ->middleware('can:rfq.create')
        ->name('rfqs.create');

    Route::livewire('rfqs/{rfq}', RfqShow::class)
        ->middleware('can:rfq.view')
        ->name('rfqs.show');

    Route::livewire('rfqs/{rfq}/respond', RfqRespond::class)
        ->middleware(['can:rfq.respond', 'approved_vendor'])
        ->name('rfqs.respond');

    Route::livewire('purchase-orders', PoIndex::class)
        ->middleware('can:po.view')
        ->name('pos.index');

    Route::livewire('purchase-orders/create', PoCreate::class)
        ->middleware('can:po.create')
        ->name('pos.create');

    Route::livewire('purchase-orders/{purchaseOrder}', PoShow::class)
        ->middleware('can:po.view')
        ->name('pos.show');

    Route::livewire('purchase-orders/{purchaseOrder}/goods-receipt', GrCreate::class)
        ->middleware('can:gr.create')
        ->name('gr.create');

    Route::livewire('purchase-orders/{purchaseOrder}/invoice/upload', InvoiceUpload::class)
        ->middleware(['can:invoice.upload', 'approved_vendor'])
        ->name('invoices.upload');

    Route::livewire('invoices/approval', InvoiceApprove::class)
        ->middleware('can:invoice.approve')
        ->name('invoices.approve');
});

require __DIR__.'/settings.php';
