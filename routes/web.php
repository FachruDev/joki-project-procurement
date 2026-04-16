<?php

use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\MediaFileController;
use App\Livewire\Admin\PermissionForm;
use App\Livewire\Admin\PermissionManagement;
use App\Livewire\Admin\RoleForm;
use App\Livewire\Admin\UserForm;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\UserProfile;
use App\Livewire\GR\Create as GrCreate;
use App\Livewire\Invoice\Approve as InvoiceApprove;
use App\Livewire\Invoice\ListAll as InvoiceListAll;
use App\Livewire\Invoice\MyInvoice;
use App\Livewire\Invoice\Show as InvoiceShow;
use App\Livewire\Invoice\Upload as InvoiceUpload;
use App\Livewire\PO\Create as PoCreate;
use App\Livewire\PO\Edit as PoEdit;
use App\Livewire\PO\Index as PoIndex;
use App\Livewire\PO\MyPo;
use App\Livewire\PO\Show as PoShow;
use App\Livewire\Report\Index as ReportIndex;
use App\Livewire\RFQ\Create as RfqCreate;
use App\Livewire\RFQ\Edit as RfqEdit;
use App\Livewire\RFQ\Index as RfqIndex;
use App\Livewire\RFQ\MyRfq;
use App\Livewire\RFQ\Respond as RfqRespond;
use App\Livewire\RFQ\Show as RfqShow;
use App\Livewire\Vendor\Dashboard;
use App\Livewire\Vendor\Index as VendorIndex;
use App\Livewire\Vendor\Profile as VendorProfile;
use App\Livewire\Vendor\Register as VendorRegister;
use App\Livewire\Vendor\Show as VendorShow;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');

    Route::livewire('reports', ReportIndex::class)
        ->middleware('permission:report.view')
        ->name('reports.index');

    Route::get('media/{media}', MediaFileController::class)->name('media.show');

    Route::livewire('management/users', UserManagement::class)
        ->middleware('permission:user.manage')
        ->name('management.users');

    Route::livewire('management/users/create', UserForm::class)
        ->middleware('permission:user.manage')
        ->name('management.users.create');

    Route::livewire('management/users/{user}/edit', UserForm::class)
        ->middleware('permission:user.manage')
        ->name('management.users.edit');

    Route::livewire('management/users/{user}/profile', UserProfile::class)
        ->middleware('permission:user.manage')
        ->name('management.users.profile');

    Route::livewire('management/permissions', PermissionManagement::class)
        ->middleware('permission:permission.manage')
        ->name('management.permissions');

    Route::livewire('management/permissions/create', PermissionForm::class)
        ->middleware('permission:permission.manage')
        ->name('management.permissions.create');

    Route::livewire('management/permissions/{permission}/edit', PermissionForm::class)
        ->middleware('permission:permission.manage')
        ->name('management.permissions.edit');

    Route::livewire('management/roles/create', RoleForm::class)
        ->middleware('permission:permission.manage')
        ->name('management.roles.create');

    Route::livewire('management/roles/{role}/edit', RoleForm::class)
        ->middleware('permission:permission.manage')
        ->name('management.roles.edit');

    Route::livewire('vendors', VendorRegister::class)
        ->middleware('can:vendor.manage')
        ->name('vendor.register');

    Route::livewire('vendors/list', VendorIndex::class)
        ->middleware('can:vendor.manage')
        ->name('vendor.index');

    Route::livewire('vendors/{vendor}', VendorShow::class)
        ->middleware('can:view,vendor')
        ->name('vendor.show');

    Route::livewire('vendor/profile', VendorProfile::class)
        ->middleware('can:rfq.respond')
        ->name('vendor.profile');

    Route::livewire('rfqs', RfqIndex::class)
        ->middleware('can:rfq.view')
        ->name('rfqs.index');

    Route::livewire('rfqs/my', MyRfq::class)
        ->middleware('can:rfq.view')
        ->name('rfqs.my');

    Route::livewire('rfqs/create', RfqCreate::class)
        ->middleware('can:rfq.create')
        ->name('rfqs.create');

    Route::livewire('rfqs/{rfq}/edit', RfqEdit::class)
        ->middleware('can:update,rfq')
        ->name('rfqs.edit');

    Route::livewire('rfqs/{rfq}', RfqShow::class)
        ->middleware('can:rfq.view')
        ->name('rfqs.show');

    Route::livewire('rfqs/{rfq}/respond', RfqRespond::class)
        ->middleware(['can:rfq.respond', 'approved_vendor'])
        ->name('rfqs.respond');

    Route::livewire('purchase-orders', PoIndex::class)
        ->middleware('can:po.view')
        ->name('pos.index');

    Route::livewire('purchase-orders/my', MyPo::class)
        ->middleware('can:po.view')
        ->name('pos.my');

    Route::livewire('purchase-orders/create', PoCreate::class)
        ->middleware('can:po.create')
        ->name('pos.create');

    Route::livewire('purchase-orders/{purchaseOrder}/edit', PoEdit::class)
        ->middleware('can:update,purchaseOrder')
        ->name('pos.edit');

    Route::livewire('purchase-orders/{purchaseOrder}', PoShow::class)
        ->middleware('can:po.view')
        ->name('pos.show');

    Route::livewire('purchase-orders/{purchaseOrder}/goods-receipt', GrCreate::class)
        ->middleware('can:gr.create')
        ->name('gr.create');

    Route::livewire('invoices', InvoiceListAll::class)
        ->middleware('can:invoice.view')
        ->name('invoices.list');

    Route::livewire('invoices/my', MyInvoice::class)
        ->middleware('can:invoice.view')
        ->name('invoices.my');

    Route::livewire('purchase-orders/{purchaseOrder}/invoice/upload', InvoiceUpload::class)
        ->middleware(['can:invoice.upload', 'approved_vendor'])
        ->name('invoices.upload');

    Route::livewire('invoices/approval', InvoiceApprove::class)
        ->middleware('can:invoice.approve')
        ->name('invoices.approve');

    Route::livewire('invoices/{invoice}', InvoiceShow::class)
        ->middleware('can:view,invoice')
        ->name('invoices.show');

    Route::get('invoices/{invoice}/print', InvoicePrintController::class)
        ->middleware('can:view,invoice')
        ->name('invoices.print');
});

require __DIR__.'/settings.php';
