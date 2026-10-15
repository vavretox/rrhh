<?php

use App\Http\Controllers\ExcelExportController;
use App\Http\Controllers\CatalogExcelController;
use App\Http\Controllers\FormattedFilesController;
use App\Http\Controllers\ImportedPaymentFilesController;
use App\Http\Controllers\PaymentFormController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\Biometric\BiometricDeviceController;
use App\Livewire\Category\CategoryDashboard;
use App\Livewire\CategoryEmployee\CategoryEmployeeDashboard;
use App\Livewire\Charge\ChargeDashboard;
use App\Livewire\ChargeEmployee\ChargeEmployeeDashboard;
use App\Livewire\DependencyRelationship\DependencyRelationshipDashboard;
use App\Livewire\Employee\EmployeeDashboard;
use App\Livewire\EmployeeCategory\EmployeeCategoryDashboard;
use App\Livewire\Form\FormDashboard;
use App\Livewire\OrganizationalArea\OrganizationalAreaDashboard;
use App\Livewire\OrganizationalAreaEmployee\OrganizationalAreaEmployeeDashboard;
use App\Livewire\PensionFundManager\PensionFundManagerDashboard;
use App\Livewire\Person\PersonDashboard;
use App\Livewire\SalaryLevel\SalaryLevelDashboard;
use App\Livewire\Setting\SettingDashboard;
use App\Livewire\User\UserDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/pension-fund-manager', PensionFundManagerDashboard::class)->name('pension-fund-manager.dashboard');
    // Route::get('/organizational-area', [OrganizationalAreaDashboard::class, 'render'])->name('organizational-area.dashboard');
    Route::get('/charge', ChargeDashboard::class)->name('charge.dashboard');
    Route::get('/dependency-relationship', DependencyRelationshipDashboard::class)->name('dependency-relationship.dashboard');
    Route::get('/person', PersonDashboard::class)->name('person.dashboard');
    Route::get('/person/template', [CatalogExcelController::class, 'peopleTemplate'])->name('person.template');
    Route::get('/person/export', [CatalogExcelController::class, 'peopleExport'])->name('person.export');
    Route::post('/person/import', [CatalogExcelController::class, 'importPeople'])->name('person.import');
    Route::get('/employee', EmployeeDashboard::class)->name('employee.dashboard');
    Route::get('/employee/template', [CatalogExcelController::class, 'employeesTemplate'])->name('employee.template');
    Route::get('/employee/export', [CatalogExcelController::class, 'employeesExport'])->name('employee.export');
    Route::post('/employee/import', [CatalogExcelController::class, 'importEmployees'])->name('employee.import');
    // Route::get('/charge-employee', [ChargeEmployeeDashboard::class, 'render'])->name('charge-employee.dashboard');
    // Route::get('/organizational-area-employee', [OrganizationalAreaEmployeeDashboard::class, 'render'])->name('organizational-area-employee.dashboard');
    Route::get('/user', UserDashboard::class)->name('user.dashboard');
    Route::get('/category', CategoryDashboard::class)->name('category.dashboard');
    // Route::get('/category-employee', [CategoryEmployeeDashboard::class, 'render'])->name('category-employee.dashboard');
    Route::get('/setting', SettingDashboard::class)->name('setting.dashboard');
    Route::get('/form', FormDashboard::class)->name('form.dashboard');
    Route::get('/imported-payment-files', [ImportedPaymentFilesController::class, 'show'])->name('imported-payment-files.index');
    Route::post('/imported-payment-files/download', [ImportedPaymentFilesController::class, 'download'])->name('imported-payment-files.download');

    // Route::get('/PaymentForm', [PaymentFormController::class, 'index'])->name('generate-payment-form');
    Route::get('/formatted-files/{formId}', [FormattedFilesController::class, 'index'])->name('formatted-files.download');
    // Route::get('/export/{formId}', [ExcelExportController::class, 'export'])->name('export');
    Route::get('/download-sheets/{formId}', [ExcelExportController::class, 'donwloadSheets'])->name('export.sheets');

    Route::get('/payment-slip/{employeeFormSlug}', [PDFController::class, 'payment_slip'])->name('pdf.payment-slip');

    Route::prefix('biometric')->name('biometric.')->group(function () {
        Route::get('/devices', [BiometricDeviceController::class, 'index'])->name('devices.index');
        Route::post('/devices', [BiometricDeviceController::class, 'store'])->name('devices.store');
        Route::put('/devices/{device}', [BiometricDeviceController::class, 'update'])->name('devices.update');
        Route::delete('/devices/{device}', [BiometricDeviceController::class, 'destroy'])->name('devices.destroy');
        Route::post('/devices/{device}/test', [BiometricDeviceController::class, 'test'])->name('devices.test');
        Route::get('/devices/{device}/logs', [BiometricDeviceController::class, 'logs'])->name('devices.logs');
    });
});

require __DIR__.'/attendance.php';
