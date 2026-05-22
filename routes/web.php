<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ProductAuditLogController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\KitchenProductionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\POSController;

use App\Http\Controllers\ExportController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\TwoFactorController;

// Login Route
Route::get('/', function () {return view('login');})->name('login')->middleware('guest');
Route::get('/login', function () {return redirect('/');})->middleware('guest'); // alias for /login URL
Route::post('/login', [AuthorizationController::class, 'login'])->name('login.submit')->middleware('throttle:5,2');
Route::post('/logout', [AuthorizationController::class, 'logout'])->name('logout')->middleware('auth');

// 2FA Routes (no auth middleware — user is not yet fully authenticated)
Route::get('/two-factor',        [TwoFactorController::class, 'show'])   ->name('2fa.show');
Route::post('/two-factor/verify',[TwoFactorController::class, 'verify']) ->name('2fa.verify');
Route::post('/two-factor/resend',[TwoFactorController::class, 'resend']) ->name('2fa.resend');

// Main dashboard route — redirect to role-specific page
Route::get('/main', function () {
    $role = auth()->user()->role;
    return match ($role) {
        'cashier'           => redirect()->route('pos'),
        'kitchen_manager'   => redirect()->route('kp'),
        'inventory_manager' => redirect()->route('im'),
        default             => redirect()->route('reports.dashboard'),
    };
})->middleware('auth');

// === Kitchen Production Routes (Admin + Kitchen Manager) ===
Route::middleware(['auth', 'role:admin,kitchen_manager'])->group(function () {
    Route::get('/kitchenproduction', [KitchenProductionController::class, 'index'])->name('kp');
    Route::post('/kitchen/start-production', [KitchenProductionController::class, 'startProduction'])->name('kitchen.startProduction');
    Route::patch('/kitchen/update-status/{id}', [KitchenProductionController::class, 'updateStatus'])->name('kitchen.updateStatus');
    Route::get('/kitchen/recipes/{product}', [KitchenProductionController::class, 'getRecipes'])->name('kitchen.getRecipes');
    Route::get('/kitchenproductionlogs', [KitchenProductionController::class, 'logs'])->name('kitchen.logs');
    Route::post('/kitchen/start-shift', [KitchenProductionController::class, 'startShift'])->name('kitchen.startShift');
    Route::post('/kitchen/end-shift', [KitchenProductionController::class, 'endShift'])->name('kitchen.endShift');
    Route::delete('/kitchen/cancel/{id}', [KitchenProductionController::class, 'cancelProduction'])->name('kitchen.cancel');
});

// === Recipe Management (Admin only) ===
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])->name('recipes.destroy');
});
Route::get('/recipes/{product}', [RecipeController::class, 'show'])->name('recipes.show')->middleware('auth');
Route::get('/ingredients/all', function () {return \App\Models\Ingredient::select('id', 'name', 'unit')->get();})->middleware('auth');

// === Analysis and Reporting (Admin only) ===
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/reports/cost-variance', [ReportController::class, 'costVariance'])->name('reports.cost-variance');
    Route::get('/reports/yield-forecasting', [ReportController::class, 'yieldForecasting'])->name('reports.yield');
    Route::get('/reports/end-of-day', [ReportController::class, 'endOfDay'])->name('reports.end-of-day');
});
Route::get('/analysisandreporting', function () {return redirect()->route('reports.dashboard');})->name('ar')->middleware(['auth', 'role:admin']);

// === Point of Sales Routes (Admin + Cashier) ===
Route::middleware(['auth', 'role:admin,cashier'])->group(function () {
    Route::get('/pointofsales', [POSController::class, 'index'])->name('pos');
    Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    Route::get('/transactionhistory', [POSController::class, 'history'])->name('POShistory');
});

// === Inventory Management (Admin + Inventory Manager) ===
Route::middleware(['auth', 'role:admin,inventory_manager'])->group(function () {
    Route::get('/stockhistory', [IngredientController::class, 'auditLog'])->name('stock-history');
    Route::get('/inventorymanagement', [IngredientController::class, 'index'])->name('im');
    Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
    Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update');
    Route::post('/ingredients/stock-in', [IngredientController::class, 'stockIn'])->name('ingredients.stockIn');
    Route::post('/ingredients/stock-out', [IngredientController::class, 'stockOut'])->name('ingredients.stockOut');
    Route::get('/ingredient-history', [IngredientController::class, 'ingredientHistory'])->name('ingredient-history');
    Route::post('/products/stock-in', [IngredientController::class, 'stockInProduct'])->name('products.stockIn');
});

// Delete ingredients — Admin only
Route::delete('/ingredients/{ingredients}', [IngredientController::class, 'destroy'])->name('ingredients.destroy')->middleware(['auth', 'role:admin']);

// Stock Reconciliation (Admin only)
Route::get('/ingredients/reconcile', [IngredientController::class, 'reconcile'])->name('ingredients.reconcile')->middleware(['auth', 'role:admin']);

// === Menu & Pricing Routes (Admin) ===
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/menuandpricing', [ProductController::class, 'index'])->name('mp');
    Route::get('/menuandpricing/{product}/edit', [ProductController::class, 'edit'])->name('editMP');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::post('/products/{product}/waste', [ProductController::class, 'waste'])->name('products.waste');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/pricinghistory', [ProductAuditLogController::class, 'index'])->name('pricing-history');
    Route::get('/wastelogs', [ProductAuditLogController::class, 'wasteLogs'])->name('waste.logs');
});

// === User Management (Admin only) ===
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle', [UserManagementController::class, 'toggleActive'])->name('users.toggle');
});

// === Database Backups (Admin only) ===
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/backups',                          [\App\Http\Controllers\BackupController::class, 'index'])   ->name('backups.index');
    Route::post('/backups/run',                     [\App\Http\Controllers\BackupController::class, 'run'])     ->name('backups.run');
    Route::post('/backups/restore',                 [\App\Http\Controllers\BackupController::class, 'restore']) ->name('backups.restore');
    Route::get('/backups/download/{filename}',      [\App\Http\Controllers\BackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/{filename}',            [\App\Http\Controllers\BackupController::class, 'destroy']) ->name('backups.destroy');
});

// === Export Routes (Authenticated) ===
Route::middleware('auth')->prefix('export')->group(function () {
    Route::get('/pos-history', [ExportController::class, 'posHistory'])->name('export.pos-history');
    Route::get('/pricing-history', [ExportController::class, 'pricingHistory'])->name('export.pricing-history');
    Route::get('/stock-history', [ExportController::class, 'stockHistory'])->name('export.stock-history');
    Route::get('/ingredient-history', [ExportController::class, 'ingredientHistory'])->name('export.ingredient-history');
    Route::get('/kitchen-logs', [ExportController::class, 'kitchenLogs'])->name('export.kitchen-logs');
    Route::get('/waste-logs', [ExportController::class, 'wasteLogs'])->name('export.waste-logs');
    Route::get('/cost-variance', [ExportController::class, 'costVariance'])->name('export.cost-variance');
    Route::get('/yield-forecasting', [ExportController::class, 'yieldForecasting'])->name('export.yield-forecasting');
});