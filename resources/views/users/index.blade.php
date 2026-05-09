@extends('main')
@section('content')
    @vite(['resources/css/mp.css'])
    @vite(['resources/css/user-management.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <div class="menu-pricing-parent-container">

        {{-- ===== FLASH MESSAGES ===== --}}
        @if(session('success'))
            <div class="my-alert" id="flashAlert">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="my-alert my-alert-error" id="flashAlert">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        {{-- ===== HEADER / CONTROLS ===== --}}
        <div class="header-container">
            <div class="controls-container">
                {{-- Filter Dropdown --}}
                <div style="position: relative;">
                    <div id="filter-button" class="filter-icon-container">
                        <i class="bi bi-funnel default-icon"></i>
                        <i class="bi bi-x-lg active-icon" style="display: none !important;"></i>
                    </div>
                    <div class="filter-dropdown" id="filterDropdown" style="display: none;">
                        <form method="GET" action="{{ route('users.index') }}" class="filter-dropdown-form">
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                            <div class="filter-group">
                                <label>Role</label>
                                <select name="role">
                                    <option value="">All Roles</option>
                                    <option value="admin"              {{ request('role') === 'admin'              ? 'selected' : '' }}>Admin</option>
                                    <option value="cashier"            {{ request('role') === 'cashier'            ? 'selected' : '' }}>Cashier</option>
                                    <option value="kitchen_manager"    {{ request('role') === 'kitchen_manager'    ? 'selected' : '' }}>Kitchen Manager</option>
                                    <option value="inventory_manager"  {{ request('role') === 'inventory_manager'  ? 'selected' : '' }}>Inventory Manager</option>
                                </select>
                            </div>
                            <button type="submit" class="apply-filter-button">Apply Filter</button>
                        </form>
                    </div>
                </div>

                {{-- Search --}}
                <form method="GET" action="{{ route('users.index') }}">
                    @if(request('role')) <input type="hidden" name="role" value="{{ request('role') }}"> @endif
                    <input type="text" name="search" class="search-input"
                        placeholder="Search by name or email"
                        value="{{ request('search') }}">
                </form>

                {{-- Active Filter Badge --}}
                @if(request('role'))
                    <div class="active-filter-title">
                        <i class="bi bi-funnel-fill"></i>
                        {{ ucwords(str_replace('_', ' ', request('role'))) }}
                    </div>
                @endif

                {{-- Add User Button --}}
                <button class="add-item-button" id="openAddUserModal">
                    <i class="fa-solid fa-user-plus me-2"></i> Add User
                </button>
            </div>
        </div>

        {{-- ===== USERS TABLE ===== --}}
        <div class="table-container">
            <table>
                <colgroup>
                    <col style="width: 22%">
                    <col style="width: 28%">
                    <col style="width: 18%">
                    <col style="width: 12%">
                    <col style="width: 10%">
                    <col style="width: 10%">
                </colgroup>
                <thead>
                    <tr class="tr">
                        <th class="th">Name</th>
                        <th class="th">Email</th>
                        <th class="th">Role</th>
                        <th class="th">Status</th>
                        <th class="th">Created</th>
                        <th class="th" style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="tr">
                        <td style="font-weight: 800; color: #2d3436;">
                            {{ $user->first_name }} {{ $user->last_name }}
                            @if($user->id === auth()->id())
                                <span class="badge-you">You</span>
                            @endif
                        </td>
                        <td style="color: #636e72; font-size: 0.95rem;">{{ $user->email }}</td>
                        <td>
                            @php
                                $roleLabels = [
                                    'admin'              => ['label' => 'Admin',             'class' => 'badge-role-admin'],
                                    'cashier'            => ['label' => 'Cashier',           'class' => 'badge-role-cashier'],
                                    'kitchen_manager'    => ['label' => 'Kitchen Mgr',       'class' => 'badge-role-kitchen'],
                                    'inventory_manager'  => ['label' => 'Inventory Mgr',     'class' => 'badge-role-inventory'],
                                ];
                                $roleInfo = $roleLabels[$user->role] ?? ['label' => ucfirst($user->role), 'class' => ''];
                            @endphp
                            <span class="badge {{ $roleInfo['class'] }}">{{ $roleInfo['label'] }}</span>
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td style="color: #b2bec3; font-size: 0.85rem;">
                            {{ $user->created_at->format('m/d/Y') }}
                        </td>
                        <td class="td-actions">
                            <div class="td-actions-container">
                                {{-- Edit Button --}}
                                <button class="edit-button open-edit-modal"
                                    data-id="{{ $user->id }}"
                                    data-first="{{ $user->first_name }}"
                                    data-last="{{ $user->last_name }}"
                                    data-email="{{ $user->email }}"
                                    data-role="{{ $user->role }}"
                                    title="Edit User">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>

                                {{-- Toggle Active/Inactive --}}
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.toggle', $user) }}" style="margin:0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="{{ $user->is_active ? 'deactivate-button' : 'activate-button' }}"
                                        title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fa-solid {{ $user->is_active ? 'fa-ban' : 'fa-circle-check' }}"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; color:#b2bec3; padding: 3rem;">
                            <i class="fa-solid fa-users-slash" style="font-size:2rem; margin-bottom:0.5rem; display:block;"></i>
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination-container">
            @include('components.pagination', ['paginator' => $users])
        </div>
    </div>

    {{-- ===== ADD USER MODAL ===== --}}
    <div class="floating-add-item-container" id="addUserModal">
        <div class="floating-add-item">
            <span><i class="fa-solid fa-user-plus me-2" style="color:#2975da;"></i>Add New User</span>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="POST-class">
            @csrf
            <div class="um-form-grid">
                <div>
                    <label class="um-label">First Name</label>
                    <input type="text" name="first_name" class="input" placeholder="e.g. Juan" required>
                </div>
                <div>
                    <label class="um-label">Last Name</label>
                    <input type="text" name="last_name" class="input" placeholder="e.g. Dela Cruz" required>
                </div>
            </div>
            <div>
                <label class="um-label">Email Address</label>
                <input type="email" name="email" class="input" placeholder="e.g. juan@udc.edu.ph" required>
            </div>
            <div>
                <label class="um-label">Role</label>
                <div class="category-input-wrapper">
                    <input type="radio" name="role" id="add-role-admin"     value="admin">
                    <label for="add-role-admin">Admin</label>
                    <input type="radio" name="role" id="add-role-cashier"   value="cashier" checked>
                    <label for="add-role-cashier">Cashier</label>
                    <input type="radio" name="role" id="add-role-kitchen"   value="kitchen_manager">
                    <label for="add-role-kitchen">Kitchen Mgr</label>
                    <input type="radio" name="role" id="add-role-inventory" value="inventory_manager">
                    <label for="add-role-inventory">Inventory Mgr</label>
                </div>
            </div>
            <div>
                <label class="um-label">Password</label>
                <input type="password" name="password" class="input" placeholder="Min. 8 characters" required>
            </div>
            <div class="floating-add-item-options">
                <button type="button" class="cancel-button" id="cancelAddUser">Cancel</button>
                <button type="submit" class="add-button">Create Account</button>
            </div>
        </form>
    </div>

    {{-- ===== EDIT USER MODAL ===== --}}
    <div class="floating-edit-item-container" id="editUserModal">
        <div class="floating-edit-item">
            <span><i class="fa-solid fa-user-pen me-2" style="color:#2975da;"></i>Edit User</span>
        </div>
        <form method="POST" action="" id="editUserForm" class="floating-edit-item-form">
            @csrf
            @method('PUT')
            <div class="um-form-grid">
                <div>
                    <label class="um-label">First Name</label>
                    <input type="text" name="first_name" id="edit-first-name" class="input" required>
                </div>
                <div>
                    <label class="um-label">Last Name</label>
                    <input type="text" name="last_name" id="edit-last-name" class="input" required>
                </div>
            </div>
            <div>
                <label class="um-label">Email Address</label>
                <input type="email" name="email" id="edit-email" class="input" required>
            </div>
            <div>
                <label class="um-label">Role</label>
                <div class="category-input-wrapper">
                    <input type="radio" name="role" id="edit-role-admin"     value="admin">
                    <label for="edit-role-admin">Admin</label>
                    <input type="radio" name="role" id="edit-role-cashier"   value="cashier">
                    <label for="edit-role-cashier">Cashier</label>
                    <input type="radio" name="role" id="edit-role-kitchen"   value="kitchen_manager">
                    <label for="edit-role-kitchen">Kitchen Mgr</label>
                    <input type="radio" name="role" id="edit-role-inventory" value="inventory_manager">
                    <label for="edit-role-inventory">Inventory Mgr</label>
                </div>
            </div>
            <div>
                <label class="um-label">New Password <span style="color:#b2bec3; font-weight:400;">(leave blank to keep current)</span></label>
                <input type="password" name="password" id="edit-password" class="input" placeholder="Leave blank to keep current">
            </div>
            <div class="floating-edit-item-options">
                <button type="button" class="cancel-button" id="cancelEditUser">Cancel</button>
                <button type="submit" class="save-button">Save Changes</button>
            </div>
        </form>
    </div>

    <div class="overlay" id="overlay"></div>

    <script>
        // ===== Flash Alert Auto-Hide =====
        const flashAlert = document.getElementById('flashAlert');
        if (flashAlert) {
            setTimeout(() => {
                flashAlert.style.opacity = '0';
                flashAlert.style.transition = 'opacity 0.5s';
                setTimeout(() => flashAlert.remove(), 500);
            }, 3500);
        }

        // ===== Filter Toggle =====
        const filterBtn = document.getElementById('filter-button');
        const filterDropdown = document.getElementById('filterDropdown');
        filterBtn?.addEventListener('click', () => {
            filterDropdown.style.display = filterDropdown.style.display === 'none' ? 'block' : 'none';
        });
        document.addEventListener('click', (e) => {
            if (!filterBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
                filterDropdown.style.display = 'none';
            }
        });

        // ===== Add User Modal =====
        const overlay = document.getElementById('overlay');
        const addModal = document.getElementById('addUserModal');
        document.getElementById('openAddUserModal')?.addEventListener('click', () => {
            addModal.classList.add('show');
            overlay.classList.add('show');
        });
        document.getElementById('cancelAddUser')?.addEventListener('click', () => {
            addModal.classList.remove('show');
            overlay.classList.remove('show');
        });

        // ===== Edit User Modal =====
        const editModal = document.getElementById('editUserModal');
        const editForm  = document.getElementById('editUserForm');

        document.querySelectorAll('.open-edit-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                const id    = btn.dataset.id;
                const first = btn.dataset.first;
                const last  = btn.dataset.last;
                const email = btn.dataset.email;
                const role  = btn.dataset.role;

                document.getElementById('edit-first-name').value = first;
                document.getElementById('edit-last-name').value  = last;
                document.getElementById('edit-email').value      = email;
                document.getElementById('edit-password').value   = '';

                // Set role radio
                const roleRadio = document.querySelector(`input[name="role"][value="${role}"]`);
                if (roleRadio) roleRadio.checked = true;

                // Update form action
                editForm.action = `/users/${id}`;

                editModal.classList.add('show');
                overlay.classList.add('show');
            });
        });

        document.getElementById('cancelEditUser')?.addEventListener('click', () => {
            editModal.classList.remove('show');
            overlay.classList.remove('show');
        });

        overlay?.addEventListener('click', () => {
            addModal.classList.remove('show');
            editModal.classList.remove('show');
            overlay.classList.remove('show');
        });
    </script>
@endsection
