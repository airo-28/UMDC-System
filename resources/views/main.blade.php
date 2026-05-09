<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Dashboard</title>
    @vite(['resources/css/main.css', 'resources/css/pagination.css'])
    @vite(['resources/js/main.js', 'resources/js/export.js'])
</head>
<body>
@php $userRole = auth()->user()->role ?? 'admin'; @endphp
<div class="side-bar-container">
    <!-- LOGO DROP DOWN  -->
    <div class="logo-and-drop-down-container">
        <div class="logo-container">
            <img src="{{asset('photos/UMDiningCenter.png')}}" alt="UM Dining Center" class ="sidebar-container-logo">
            <img src="{{asset('favicon.png')}}" alt="UM Dining Center" class ="sidebar-logo-collapsed">
        </div>
        <div class="drop-down-container" style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-angles-left drop-down-container-button"></i>
        </div>
    </div>
    <div class="drop-down-main-container">
        {{-- ============================================ --}}
        {{-- POINT OF SALE — Admin + Cashier --}}
        {{-- ============================================ --}}
        @if(in_array($userRole, ['admin', 'cashier']))
        <div class="point-of-sales-container subsystem">
            <div class="point-of-sale">
                <i class="fa-solid fa-cash-register me-3"></i>
                <span class ="subsystem-span">Point of Sale</span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button-pos"></i>
            </div>
        </div>
        <div class="subsystem-feature">    
            <a href="{{ route('pos') }}">
                <div class="new-transaction feature d-flex align-items-center">
                    <i class="fa-solid fa-plus-circle me-3 sub-icon"></i>
                    <span class="subsystem-span">New Transaction</span>
                </div>
            </a>
            <a href="{{ route('POShistory') }}">
                <div class="transaction-history feature d-flex align-items-center">
                    <i class="fa-solid fa-receipt me-3 sub-icon"></i>
                    <span class="subsystem-span">Transaction History</span>                    
                </div>
            </a>
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- KITCHEN PRODUCTION — Admin + Kitchen Manager --}}
        {{-- ============================================ --}}
        @if(in_array($userRole, ['admin', 'kitchen_manager']))
        <div class="kitchen-production-container subsystem">
            <div class="kitchen-production">
                <i class="fa-solid fa-utensils me-2"></i>
                <span class="subsystem-span">Kitchen Production</span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button"></i>
            </div>
        </div>
        <div class="subsystem-feature">
            <a href="{{ route('kp') }}">
                <div class="feature d-flex align-items-center">
                    <i class="fa-solid fa-fire me-3 sub-icon"></i>
                    <span class="subsystem-span">Kitchen Board</span>
                </div>
            </a>
            <a href="{{ route('kitchen.logs') }}">
                <div class="feature d-flex align-items-center">
                    <i class="fa-solid fa-clipboard-list me-3 sub-icon"></i>
                    <span class="subsystem-span">Production Log</span>
                </div>
            </a>
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- INVENTORY MANAGEMENT — Admin + Inventory Manager --}}
        {{-- ============================================ --}}
        @if(in_array($userRole, ['admin', 'inventory_manager']))
        <div class="inventory-management-container subsystem">
            <div class="inventory-management">
                <i class="fa-solid fa-boxes-stacked me-3"></i>
                <span class ="subsystem-span">Inventory Management</span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button"></i>
            </div>
        </div>
        <div class="subsystem-feature">
            <a href="{{ route('im') }}">
                <div class="new-transaction feature d-flex align-items-center">
                    <i class="fa-solid fa-list-check me-3 sub-icon"></i>
                    <span class ="subsystem-span">Ingredient Master List</span>
                </div>
            </a>
            <a href="{{ route('stock-history') }}">
                <div class="transaction-history feature d-flex align-items-center">
                    <i class="fa-solid fa-truck-ramp-box me-3 sub-icon"></i>
                    <span class ="subsystem-span">Stock History</span>                    
                </div>
            </a>
            <a href="{{ route('ingredient-history') }}">
                <div class="feature d-flex align-items-center">
                    <i class="fa-solid fa-clock-rotate-left me-3 sub-icon"></i>
                    <span class="subsystem-span">Ingredient History</span>
                </div>
            </a>
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- MENU & PRICING — Admin only --}}
        {{-- ============================================ --}}
        @if($userRole === 'admin')
        <div class="menu-pricing-container subsystem">
            <div class="menu-pricing">
                <i class="fa-solid fa-book-open me-3"></i>
                <span class ="subsystem-span">Menu & Pricing </span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button"></i>
            </div>
        </div>
        <div class="subsystem-feature">
            <a href="{{ route('mp') }}">
                <div class="new-transaction feature d-flex align-items-center">
                    <i class="fa-solid fa-clipboard-list me-3 sub-icon"></i>
                    <span class ="subsystem-span">Product Master List</span>
                </div>
            </a>
            <a href="{{ route('pricing-history') }}">
                <div class="transaction-history feature d-flex align-items-center">
                    <i class="fa-solid fa-tag me-3 sub-icon"></i>
                    <span class ="subsystem-span">Product Audit Log</span>                    
                </div>
            </a>
            <a href="{{ route('waste.logs') }}">
                <div class="transaction-history feature d-flex align-items-center">
                    <i class="fa-solid fa-trash-can-arrow-up me-3 sub-icon"></i>
                    <span class ="subsystem-span">Waste Log</span>                    
                </div>
            </a>
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- ANALYSIS & REPORTING — Admin only --}}
        {{-- ============================================ --}}
        @if($userRole === 'admin')
        <div class="analysis-and-reporting-container subsystem">
            <div class="analysis-and-reporting">
                <i class="fa-solid fa-chart-line me-3"></i>
                <span class ="subsystem-span">Analysis & Reporting</span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button"></i>
            </div>
        </div>
        <div class="subsystem-feature">
            <a href="{{ route('reports.dashboard') }}">
                <div class="new-transaction feature d-flex align-items-center">
                    <i class="fa-solid fa-gauge-high me-3 sub-icon"></i>
                    <span class ="subsystem-span">Financial Dashboard</span>
                </div>
            </a>
            <a href="{{ route('reports.cost-variance') }}">
                <div class="transaction-history feature d-flex align-items-center">
                    <i class="fa-solid fa-file-invoice-dollar me-3 sub-icon"></i>
                    <span class ="subsystem-span">Cost & Variance Report</span>                    
                </div>
            </a>
            <a href="{{ route('reports.yield') }}">
                <div class="transaction-history feature d-flex align-items-center">
                    <i class="fa-solid fa-magnifying-glass-chart me-3 sub-icon"></i>
                    <span class ="subsystem-span">Yield & Forecasting Report</span>                    
                </div>
            </a>
            <a href="{{ route('reports.end-of-day') }}">
                <div class="feature d-flex align-items-center">
                    <i class="fa-solid fa-calendar-day me-3 sub-icon"></i>
                    <span class="subsystem-span">End of Day Report</span>
                </div>
            </a>
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- USER MANAGEMENT — Admin only --}}
        {{-- ============================================ --}}
        @if($userRole === 'admin')
        <div class="user-management-container subsystem">
            <div class="user-management">
                <i class="fa-solid fa-users-gear me-3"></i>
                <span class="subsystem-span">User Management</span>
            </div>
            <div class="subsystem-drop-down">
                <i class="fa-solid fa-angles-right arrow-icon button"></i>
            </div>
        </div>
        <div class="subsystem-feature">
            <a href="{{ route('users.index') }}">
                <div class="feature d-flex align-items-center">
                    <i class="fa-solid fa-id-card me-3 sub-icon"></i>
                    <span class="subsystem-span">Manage Accounts</span>
                </div>
            </a>
            <a href="{{ route('backups.index') }}">
                <div class="feature d-flex align-items-center">
                    <i class="fa-solid fa-database me-3 sub-icon"></i>
                    <span class="subsystem-span">Database Backups</span>
                </div>
            </a>
        </div>
        @endif
    </div>
    <form method="POST" action="{{ route('logout') }}" style="margin-top: auto;">
        @csrf
        <div class="logout-button-wrapper">
            <button class="logout-button" type="submit">
                <i class="fa-solid fa-right-from-bracket me-3" style="color: red;"></i>
                <span class="subsystem-span">Logout</span>
            </button>
        </div>
    </form>
</div>
<div class="main-content">
    @yield('content')
</div>

<!-- ===== SESSION TIMEOUT WARNING MODAL ===== -->
<div id="sessionWarningModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:10000; background:rgba(0,0,0,0.55); justify-content:center; align-items:center;">
    <div style="background:white; border-radius:1.2rem; padding:2rem 2.5rem; width:380px; max-width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.25); text-align:center;">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,#f39c12,#d68910);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
            <i class="fa-solid fa-clock" style="color:white;font-size:1.4rem;"></i>
        </div>
        <h3 style="margin:0 0 0.5rem;font-size:1.1rem;color:#2d3436;font-weight:800;">Session Expiring Soon</h3>
        <p style="margin:0 0 1.2rem;font-size:0.88rem;color:#636e72;line-height:1.5;">
            Your session will expire in <strong id="sessionCountdown" style="color:#c0392b;">1:00</strong>.<br>
            Click <strong>Stay Logged In</strong> to continue.
        </p>
        <div style="display:flex;gap:0.75rem;justify-content:center;">
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" style="padding:0.55rem 1.3rem;border:1px solid #dfe6e9;background:white;border-radius:2rem;cursor:pointer;font-weight:600;color:#636e72;font-size:0.85rem;">Logout Now</button>
            </form>
            <button id="stayLoggedInBtn" style="padding:0.55rem 1.3rem;border:none;background:#2975da;color:white;border-radius:2rem;cursor:pointer;font-weight:700;font-size:0.85rem;box-shadow:0 3px 10px rgba(41,117,218,0.25);">
                <i class="fa-solid fa-rotate-right" style="margin-right:0.3rem;"></i>Stay Logged In
            </button>
        </div>
    </div>
</div>
<script>
(function() {
    const SESSION_MINUTES = 30;
    const WARN_BEFORE_SECONDS = 60; // warn 1 min before expiry
    let warningTimer, countdownTimer, countdownSeconds;

    function showWarning() {
        countdownSeconds = WARN_BEFORE_SECONDS;
        document.getElementById('sessionWarningModal').style.display = 'flex';
        updateCountdown();
        countdownTimer = setInterval(() => {
            countdownSeconds--;
            updateCountdown();
            if (countdownSeconds <= 0) {
                clearInterval(countdownTimer);
                // POST logout via form submit (logout route only accepts POST)
                const f = document.createElement('form');
                f.method = 'POST'; f.action = "{{ route('logout') }}";
                const csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
                f.appendChild(csrf); document.body.appendChild(f); f.submit();
            }
        }, 1000);
    }

    function updateCountdown() {
        const m = String(Math.floor(countdownSeconds / 60)).padStart(2, '0');
        const s = String(countdownSeconds % 60).padStart(2, '0');
        const el = document.getElementById('sessionCountdown');
        if (el) el.textContent = `${m}:${s}`;
    }

    function resetTimer() {
        clearTimeout(warningTimer);
        clearInterval(countdownTimer);
        document.getElementById('sessionWarningModal').style.display = 'none';
        const warnAt = (SESSION_MINUTES * 60 - WARN_BEFORE_SECONDS) * 1000;
        warningTimer = setTimeout(showWarning, warnAt);
    }

    // Ping server to keep session alive
    document.getElementById('stayLoggedInBtn')?.addEventListener('click', () => {
        fetch(window.location.href, { method: 'GET', credentials: 'same-origin' });
        resetTimer();
    });

    // Start timer on page load; reset on any user interaction
    resetTimer();
    ['click', 'keydown', 'mousemove', 'scroll'].forEach(evt =>
        document.addEventListener(evt, resetTimer, { passive: true })
    );
})();
</script>

<!-- Export Date Range Modal (shared) -->

<div id="exportDateRangeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:white; border-radius:1rem; padding:2rem; width:400px; max-width:90%; box-shadow:0 10px 40px rgba(0,0,0,0.25);">
        <h3 style="margin:0 0 0.5rem 0; font-size:1.15rem; color:#2d3436;"><i class="fa-solid fa-file-export" style="margin-right:0.5rem; color:#2975da;"></i>Export Data</h3>
        <p style="margin:0 0 1.2rem 0; font-size:0.85rem; color:#636e72;">Select a date range for the export. All matching records will be included.</p>
        <div style="display:flex; gap:1rem; margin-bottom:1.2rem;">
            <div style="flex:1;">
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#636e72; margin-bottom:0.3rem;">From</label>
                <input type="date" id="exportDateFrom" style="width:100%; padding:0.55rem 0.7rem; border:1px solid #e0e0e0; border-radius:0.6rem; font-family:inherit; font-size:0.9rem;">
            </div>
            <div style="flex:1;">
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#636e72; margin-bottom:0.3rem;">To</label>
                <input type="date" id="exportDateTo" style="width:100%; padding:0.55rem 0.7rem; border:1px solid #e0e0e0; border-radius:0.6rem; font-family:inherit; font-size:0.9rem;">
            </div>
        </div>
        <div style="display:flex; gap:0.75rem; justify-content:flex-end;">
            <button id="exportCancelBtn" style="padding:0.55rem 1.3rem; border:1px solid #dfe6e9; background:white; border-radius:2rem; cursor:pointer; font-weight:600; color:#636e72; font-size:0.85rem;">Cancel</button>
            <button id="exportConfirmBtn" style="padding:0.55rem 1.3rem; border:none; background:#2975da; color:white; border-radius:2rem; cursor:pointer; font-weight:700; font-size:0.85rem; box-shadow:0 3px 10px rgba(41,117,218,0.25);">Download CSV</button>
        </div>
    </div>
</div>

<style>
/* ===== Print-to-PDF Styles for Report Pages ===== */
@media print {
    body {
        background: white !important;
        overflow: visible !important;
    }
    .side-bar-container,
    .overlay,
    .modal-container,
    .eod-tabs,
    .report-actions button,
    .eod-date-form,
    #exportDateRangeModal,
    .end-shift-btn-report,
    [data-export-url],
    [data-export-type],
    .export-audit-log-button,
    .pagination-container,
    nav,
    .filter-icon-container,
    .filter-dropdown {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        height: auto !important;
        overflow: visible !important;
    }
    .main-container,
    .parent-container,
    .report-container {
        padding: 0 !important;
        margin: 0 !important;
    }
    .print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #2975da;
    }
    .print-header h1 {
        font-size: 1.4rem;
        color: #2d3436;
        margin: 0;
    }
    .print-header p {
        font-size: 0.85rem;
        color: #636e72;
        margin: 0.2rem 0 0 0;
    }
    /* Show all panels including hidden tabs */
    .eod-panel {
        display: block !important;
        page-break-inside: avoid;
        margin-bottom: 1.5rem;
    }
    /* Chart.js canvases print as raster images automatically */
    canvas {
        max-width: 100% !important;
        height: auto !important;
    }
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
    }
}
.print-header {
    display: none;
}
</style>
</body>
</html>