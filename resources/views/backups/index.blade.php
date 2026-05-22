@extends('main')
@section('content')
    @vite(['resources/css/mp.css'])
    @vite(['resources/css/user-management.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <div class="menu-pricing-parent-container">

        {{-- Flash --}}
        @if(session('success'))
            <div class="my-alert" id="flashAlert">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="my-alert my-alert-error" id="flashAlert">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="my-alert my-alert-error" id="flashAlert">{{ $errors->first() }}</div>
        @endif

        {{-- Header --}}
        <div class="header-container">
            <div class="controls-container">
                <div style="flex:1;">
                    <h2 style="margin:0; font-size:1.3rem; font-weight:800; color:#2d3436;">
                        <i class="fa-solid fa-database me-2" style="color:#2975da;"></i>Database Backups
                    </h2>
                    <p style="margin:0.2rem 0 0; font-size:0.85rem; color:#636e72;">
                        Backups are stored locally at <code>storage/backups/</code>. Auto-backup runs daily at midnight.
                    </p>
                </div>
                <form method="POST" action="{{ route('backups.run') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="add-item-button">
                        <i class="fa-solid fa-database me-2"></i> Backup Now
                    </button>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-container">
            <table>
                <colgroup>
                    <col style="width: 45%">
                    <col style="width: 15%">
                    <col style="width: 25%">
                    <col style="width: 15%">
                </colgroup>
                <thead>
                    <tr class="tr">
                        <th class="th">Filename</th>
                        <th class="th">Size</th>
                        <th class="th">Created</th>
                        <th class="th" style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                    <tr class="tr">
                        <td>
                            <i class="fa-solid fa-file-code me-2" style="color:#2975da;"></i>
                            <span style="font-weight:700; color:#2d3436; font-size:0.9rem;">{{ $backup['name'] }}</span>
                        </td>
                        <td>
                            <span class="badge" style="background:#e3f2fd; color:#1565c0;">{{ $backup['size'] }}</span>
                        </td>
                        <td style="color:#636e72; font-size:0.9rem;">
                            <i class="fa-regular fa-clock me-1"></i>{{ $backup['created'] }}
                        </td>
                        <td class="td-actions">
                            <div class="td-actions-container">
                                {{-- Download --}}
                                <a href="{{ route('backups.download', $backup['name']) }}" title="Download">
                                    <i class="fa-solid fa-download" style="color:grey; font-size:1rem; transition:color 0.2s;"
                                        onmouseover="this.style.color='#2975da'" onmouseout="this.style.color='grey'"></i>
                                </a>
                                {{-- Delete --}}
                                <form method="POST" action="{{ route('backups.destroy', $backup['name']) }}" style="margin:0;"
                                    onsubmit="return confirm('Delete {{ $backup['name'] }}? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-button" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center; color:#b2bec3; padding:3rem;">
                            <i class="fa-solid fa-box-open" style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>
                            No backups yet. Click <strong>Backup Now</strong> to create the first one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Restore Section --}}
        <div style="margin-top:1.5rem; background:#fff8f0; border:1px solid #ffd8a8; border-radius:1rem; padding:1.4rem 1.5rem;">
            <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.8rem;">
                <i class="fa-solid fa-upload" style="color:#e67e22; font-size:1.1rem;"></i>
                <span style="font-weight:800; color:#2d3436; font-size:0.95rem;">Restore Database from Backup</span>
            </div>
            <p style="font-size:0.83rem; color:#636e72; margin:0 0 1rem;">
                Upload a previously downloaded <code>.sql</code> backup file to restore the database.
                <strong style="color:#c0392b;">⚠ Warning:</strong> This will overwrite existing data. Make sure to create a fresh backup first.
            </p>
            <form method="POST" action="{{ route('backups.restore') }}" enctype="multipart/form-data"
                  id="restoreForm" style="display:flex; gap:0.8rem; align-items:center; flex-wrap:wrap;">
                @csrf
                <input type="file" name="sql_file" accept=".sql,.txt" id="sqlFileInput" required
                    style="flex:1; min-width:200px; padding:0.5rem 0.8rem; border:2px solid #ffd8a8;
                           border-radius:0.6rem; font-size:0.85rem; background:#fff; color:#2d3436;">
                <button type="submit" id="restoreBtn"
                    style="padding:0.55rem 1.3rem; background:linear-gradient(135deg,#e67e22,#d35400);
                           color:white; border:none; border-radius:50px; font-weight:700;
                           font-size:0.88rem; cursor:pointer; white-space:nowrap;">
                    <i class="fa-solid fa-rotate-left me-1"></i> Restore Now
                </button>
            </form>
        </div>

        {{-- Info card --}}
        <div style="margin-top:1rem; background:#f0f7ff; border:1px solid #d0e8ff; border-radius:1rem; padding:1.2rem 1.5rem; display:flex; gap:1rem; align-items:flex-start;">
            <i class="fa-solid fa-circle-info" style="color:#2975da; font-size:1.2rem; margin-top:0.1rem;"></i>
            <div>
                <div style="font-weight:700; color:#2d3436; font-size:0.9rem; margin-bottom:0.3rem;">About Local Backups</div>
                <div style="font-size:0.82rem; color:#636e72; line-height:1.6;">
                    Backups are saved as <code>.sql</code> dump files on this server. The last <strong>30 backups</strong> are kept automatically — older ones are pruned.<br>
                    To restore, upload the <code>.sql</code> file using the form above, or use <code>psql &lt; backup_file.sql</code> (PostgreSQL) from your terminal.
                </div>
            </div>
        </div>
    </div>

    <script>
        const flashAlert = document.getElementById('flashAlert');
        if (flashAlert) {
            setTimeout(() => { flashAlert.style.opacity = '0'; flashAlert.style.transition = 'opacity 0.5s'; setTimeout(() => flashAlert.remove(), 500); }, 3500);
        }

        // Confirm before restoring
        document.getElementById('restoreForm').addEventListener('submit', function(e) {
            const file = document.getElementById('sqlFileInput').files[0];
            if (!file) return;
            if (!confirm('⚠ Are you sure you want to restore the database from "' + file.name + '"?\n\nThis will overwrite existing data and cannot be undone.')) {
                e.preventDefault();
            }
        });
    </script>
@endsection
