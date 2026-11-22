@extends('layouts.admin', ['active' => 'nguoi-dung'])

@section('title', 'User Management')

@section('top_breadcrumb')
    <span>System</span>
    <span class="crumb-arrow">></span>
    <span style="color: var(--admin-primary); font-weight: 600;">User Management</span>
@endsection

@section('content')
<div class="user-management-page">

    {{-- Session Alerts --}}
    @if (session('success'))
        <div class="alert-admin-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-admin-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- 1. Page Header & Actions --}}
    <div class="user-page-header">
        <div>
            <div class="user-title-row">
                <h1 class="user-page-title">User Management</h1>
                <span class="user-count-badge">{{ number_format($stats['total']) }} users</span>
            </div>
            <p class="user-page-subtitle">
                Manage students, teachers, and system administrators. Control system roles and account access permissions.
            </p>
        </div>

        <div class="user-header-actions">
            {{-- Export CSV --}}
            <a href="{{ route('admin.users.export') }}" class="btn-admin-action excel" title="Export users list to CSV">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Export CSV</span>
            </a>

            {{-- Import CSV --}}
            <button type="button" class="btn-admin-action import" onclick="openAdminModal('modal-import')" title="Import accounts from CSV">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="18" x2="12" y2="12"></line>
                    <line x1="9" y1="15" x2="15" y2="15"></line>
                </svg>
                <span>Import CSV</span>
            </button>

            {{-- Add New User --}}
            <button type="button" class="btn-admin-primary" onclick="openAdminModal('modal-add-user')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
                <span>Add User</span>
            </button>
        </div>
    </div>

    {{-- 2. KPI Stat Cards --}}
    <div class="user-kpi-grid">
        {{-- Card 1: Total Users --}}
        <div class="user-kpi-card">
            <div class="user-kpi-header">
                <p class="user-kpi-label">TOTAL USERS</p>
                <div class="user-kpi-icon icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
            </div>
            <div class="user-kpi-value">{{ number_format($stats['total']) }}</div>
            <div class="user-kpi-footer">
                <span class="kpi-trend-pill trend-purple">
                    {{ number_format($stats['active']) }} active
                </span>
                <span>registered accounts</span>
            </div>
        </div>

        {{-- Card 2: Teachers & Staff --}}
        <div class="user-kpi-card">
            <div class="user-kpi-header">
                <p class="user-kpi-label">TEACHERS & STAFF</p>
                <div class="user-kpi-icon icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <circle cx="12" cy="15" r="2"></circle>
                    </svg>
                </div>
            </div>
            <div class="user-kpi-value">{{ number_format($stats['teachers']) }}</div>
            <div class="user-kpi-footer">
                <span>Teaching staff & test reviewers</span>
            </div>
        </div>

        {{-- Card 3: Locked / Suspended --}}
        <div class="user-kpi-card">
            <div class="user-kpi-header">
                <p class="user-kpi-label">LOCKED / SUSPENDED</p>
                <div class="user-kpi-icon icon-red">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
            </div>
            <div class="user-kpi-value" style="color: #dc2626;">{{ number_format($stats['locked']) }}</div>
            <div class="user-kpi-footer">
                <span>Access restricted or locked</span>
            </div>
        </div>
    </div>

    {{-- 3. Search & Filter Bar --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="user-filter-bar">
        {{-- Search input --}}
        <div class="user-search-wrap">
            <svg class="user-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text"
                   name="search"
                   value="{{ $filters['search'] }}"
                   placeholder="Search by name, email, username or ID..."
                   class="user-search-input">
        </div>

        {{-- Role filter --}}
        <div class="user-filter-select-wrap">
            <select name="role" class="user-filter-select" onchange="this.form.submit()">
                <option value="all" {{ $filters['role'] === 'all' ? 'selected' : '' }}>All Roles</option>
                <option value="student" {{ $filters['role'] === 'student' ? 'selected' : '' }}>Student</option>
                <option value="teacher" {{ $filters['role'] === 'teacher' ? 'selected' : '' }}>Teacher</option>
                <option value="admin" {{ $filters['role'] === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            <svg class="user-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>

        {{-- Status filter --}}
        <div class="user-filter-select-wrap">
            <select name="status" class="user-filter-select" onchange="this.form.submit()">
                <option value="ALL" {{ $filters['status'] === 'ALL' ? 'selected' : '' }}>All Statuses</option>
                <option value="ACTIVE" {{ $filters['status'] === 'ACTIVE' ? 'selected' : '' }}>• Active</option>
                <option value="PENDING" {{ $filters['status'] === 'PENDING' ? 'selected' : '' }}>• Pending</option>
                <option value="LOCKED" {{ $filters['status'] === 'LOCKED' ? 'selected' : '' }}>• Locked</option>
            </select>
            <svg class="user-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>

        {{-- Time filter --}}
        <div class="user-filter-select-wrap">
            <select name="time" class="user-filter-select" onchange="this.form.submit()" style="padding-left: 36px;">
                <option value="all" {{ $filters['time'] === 'all' ? 'selected' : '' }}>All Time</option>
                <option value="7" {{ $filters['time'] === '7' ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ $filters['time'] === '30' ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ $filters['time'] === '90' ? 'selected' : '' }}>Last 90 days</option>
            </select>
            <svg style="position: absolute; left: 12px; width: 16px; height: 16px; color: #94a3b8; pointer-events: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <svg class="user-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>

        {{-- Reset filter button --}}
        <a href="{{ route('admin.users.index') }}" class="user-refresh-btn" title="Reset filters">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 4 23 10 17 10"></polyline>
                <polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
        </a>
    </form>

    {{-- 4. Bulk Action Selection Bar (shown when checkboxes are checked) --}}
    <div id="user-selection-bar" class="qbank-selection-bar" style="display: none;">
        <div class="qbank-sel-left">
            <span class="qbank-sel-circle" id="selected-badge-count">0</span>
            <span>user(s) selected</span>
            <span style="color: #cbd5e1;">|</span>
            <button type="button" class="qbank-sel-link" onclick="clearAllSelections()" style="background: none; border: none; padding: 0;">Clear selection</button>
        </div>
        <div class="qbank-sel-actions" style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="btn-admin-action" onclick="submitBulkAction('lock')" title="Lock selected users">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span>Lock Selected</span>
            </button>
            <button type="button" class="btn-admin-action" onclick="submitBulkAction('unlock')" title="Unlock selected users">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                </svg>
                <span>Unlock Selected</span>
            </button>
            <button type="button" class="btn-admin-action danger" onclick="submitBulkAction('delete')" title="Delete selected users">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <span>Delete Selected</span>
            </button>
        </div>
    </div>

    {{-- Hidden Form for Bulk Action --}}
    <form id="bulk-action-form" method="POST" action="{{ route('admin.users.bulk-action') }}" style="display: none;">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input" value="">
        <div id="bulk-action-ids-container"></div>
    </form>

    {{-- 5. Users Data Table --}}
    <div class="user-table-card">
        <div class="user-table-scroll">
            <table class="user-data-table">
                <thead>
                    <tr>
                        <th style="width: 44px; text-align: center;">
                            <input type="checkbox" id="check-all-users" class="custom-checkbox" title="Select All">
                        </th>
                        <th>USER</th>
                        <th>ROLE</th>
                        <th>ENERGY / ATTEMPTS</th>
                        <th>STATUS</th>
                        <th>JOINED</th>
                        <th class="admin-actions-heading">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            {{-- Checkbox --}}
                            <td style="text-align: center;">
                                <input type="checkbox"
                                       class="custom-checkbox user-row-checkbox"
                                       value="{{ $user->user_id }}"
                                       {{ $user->user_id === auth()->id() ? 'disabled' : '' }}
                                       onchange="handleRowCheckboxChange()">
                            </td>

                            {{-- User Info (Avatar + Name + Email + ID) --}}
                            <td>
                                <div class="user-info-cell">
                                    <div class="user-avatar-wrap">
                                        <div class="user-avatar-circle"
                                             style="background-color: {{ $user->avatar_bg }}; color: {{ $user->avatar_text_color }};">
                                            {{ $user->initials }}
                                        </div>
                                        @if (!$user->isLocked())
                                            <span class="user-status-dot-subtle" title="Active"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="user-name-line">
                                            <span class="user-name-text">{{ $user->full_name ?: $user->user_name }}</span>
                                            @if ($user->user_id === auth()->id())
                                                <span style="display: inline-flex; align-items: center; font-size: 11px; font-weight: 700; color: #4338ca; background: #eef2ff; border: 1px solid #c7d2fe; padding: 1px 7px; border-radius: 999px; margin-left: 6px;">
                                                    You
                                                </span>
                                            @endif
                                        </div>
                                        <div class="user-email-text">{{ $user->email }}</div>
                                        <div class="user-id-code">{{ $user->stp_id }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Role --}}
                            <td>
                                <span class="role-pill {{ $user->role_badge_class }}">
                                    {{ $user->role_display }}
                                </span>
                            </td>

                            {{-- Energy & Attempts --}}
                            <td>
                                <div class="progress-cell-wrap">
                                    <div class="progress-info-row">
                                        <span class="progress-score-main">{{ number_format($user->energy) }} energy</span>
                                        <span class="progress-sub-text">{{ number_format($user->attempts_count) }} attempts</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="status-pill {{ $user->status_badge_class }}">
                                    <span class="status-dot"></span>
                                    {{ $user->status_label }}
                                </span>
                            </td>

                            {{-- Joined Date --}}
                            <td style="color: #64748b; font-size: 13px; white-space: nowrap;">
                                {{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}
                            </td>

                            {{-- Actions --}}
                            <td class="admin-actions-cell">
                                <div class="user-actions-cell">
                                    {{-- Edit User --}}
                                    <button type="button"
                                            class="action-icon-btn"
                                            title="Edit User"
                                            onclick='openEditModal(@json($user))'>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                    {{-- Reset Password --}}
                                    <button type="button"
                                            class="action-icon-btn"
                                            title="Reset Password"
                                            onclick="openResetPasswordModal({{ $user->user_id }}, '{{ addslashes($user->full_name ?: $user->user_name) }}', '{{ addslashes($user->email) }}')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 2l-2 2m-1.5 1.5L16 7l-2-2m-1.5 1.5L11 8l-2-2m-1.5 1.5L6 9l-4 4 3 3 2-2 2 2 2-2 2 2 7-7-1.5-1.5z"></path>
                                            <circle cx="16.5" cy="7.5" r="4.5"></circle>
                                        </svg>
                                    </button>

                                    {{-- Lock / Unlock --}}
                                    @if ($user->user_id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user->user_id) }}" style="display: inline; margin: 0;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="action-icon-btn {{ $user->isLocked() ? 'danger' : '' }}"
                                                    title="{{ $user->isLocked() ? 'Unlock Account' : 'Lock Account' }}"
                                                    onclick="return confirm('Are you sure you want to {{ $user->isLocked() ? 'UNLOCK' : 'LOCK' }} user \'{{ addslashes($user->full_name ?: $user->user_name) }}\'?')">
                                                @if ($user->isLocked())
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                        <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                                                    </svg>
                                                @else
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Delete User --}}
                                    @if ($user->user_id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->user_id) }}" style="display: inline; margin: 0;" onsubmit="return confirm('Are you sure you want to permanently delete user \'{{ addslashes($user->full_name ?: $user->user_name) }}\'? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon-btn danger" title="Delete User">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 48px 20px; color: #64748b;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                    <p style="margin: 0; font-weight: 600; font-size: 15px;">No matching users found</p>
                                    <p style="margin: 0; font-size: 13px; color: #94a3b8;">Try adjusting your search query or filter options</p>
                                    <a href="{{ route('admin.users.index') }}" class="btn-admin-action" style="margin-top: 10px;">Clear Filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 6. Table Footer & Dynamic Pagination --}}
        <div class="user-table-footer">
            <div class="user-footer-left">
                <span>
                    Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ number_format($users->total()) }} users
                </span>

                <div class="user-per-page-select">
                    <span>Rows per page:</span>
                    <input type="number"
                           class="per-page-input"
                           value="{{ $filters['per_page'] }}"
                           min="5"
                           max="100"
                           onchange="location.href='{{ request()->fullUrlWithQuery(['per_page' => '']) }}' + this.value">
                </div>
            </div>

            <div class="user-pagination">
                {{-- First Page |< --}}
                @if ($users->onFirstPage())
                    <span class="page-btn disabled" title="First page">&verbar;&lt;</span>
                    <span class="page-btn disabled" title="Previous page">&lt;</span>
                @else
                    <a href="{{ $users->url(1) }}" class="page-btn" title="First page">&verbar;&lt;</a>
                    <a href="{{ $users->previousPageUrl() }}" class="page-btn" title="Previous page">&lt;</a>
                @endif

                {{-- Sliding Window Page Numbers --}}
                @php
                    $cur = $users->currentPage();
                    $last = $users->lastPage();
                    $start = max(1, $cur - 2);
                    $end = min($last, $cur + 2);
                @endphp

                @if ($start > 1)
                    <a href="{{ $users->url(1) }}" class="page-btn">1</a>
                    @if ($start > 2)
                        <span class="page-btn disabled">...</span>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    @if ($i === $cur)
                        <span class="page-btn active">{{ $i }}</span>
                    @else
                        <a href="{{ $users->url($i) }}" class="page-btn">{{ $i }}</a>
                    @endif
                @endfor

                @if ($end < $last)
                    @if ($end < $last - 1)
                        <span class="page-btn disabled">...</span>
                    @endif
                    <a href="{{ $users->url($last) }}" class="page-btn">{{ $last }}</a>
                @endif

                {{-- Next Page > and Last Page >| --}}
                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="page-btn" title="Next page">&gt;</a>
                    <a href="{{ $users->url($last) }}" class="page-btn" title="Last page">&gt;&verbar;</a>
                @else
                    <span class="page-btn disabled" title="Next page">&gt;</span>
                    <span class="page-btn disabled" title="Last page">&gt;&verbar;</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: ADD NEW USER --}}
@php
    $openAddModal = $errors->any() && old('_modal') === 'modal-add-user';
@endphp
<div id="modal-add-user" class="admin-modal-backdrop {{ $openAddModal ? 'open' : '' }}">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add New User</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-add-user')">&times;</button>
        </div>
        <form id="form-add-user" method="POST" action="{{ route('admin.users.store') }}" novalidate autocomplete="off">
            @csrf
            <input type="hidden" name="_modal" value="modal-add-user">
            <div class="admin-modal-body">
                {{-- Full Name --}}
                <div class="form-group-admin">
                    <label>Full Name <span style="color: #dc2626;">*</span></label>
                    <input type="text"
                           id="add_full_name"
                           name="full_name"
                           value="{{ old('_modal') === 'modal-add-user' ? old('full_name') : '' }}"
                           class="form-control-admin {{ $errors->has('full_name') && old('_modal') === 'modal-add-user' ? 'is-invalid' : '' }}"
                           placeholder="e.g. John Doe"
                           autocomplete="off">
                    <span class="field-error-text" id="error-add_full_name">
                        @if ($errors->has('full_name') && old('_modal') === 'modal-add-user')
                            {{ $errors->first('full_name') }}
                        @endif
                    </span>
                </div>

                {{-- Email Address --}}
                <div class="form-group-admin">
                    <label>Email Address <span style="color: #dc2626;">*</span></label>
                    <input type="email"
                           id="add_email"
                           name="email"
                           value="{{ old('_modal') === 'modal-add-user' ? old('email') : '' }}"
                           class="form-control-admin {{ $errors->has('email') && old('_modal') === 'modal-add-user' ? 'is-invalid' : '' }}"
                           placeholder="user@studyprep.edu.vn"
                           autocomplete="off">
                    <span class="field-error-text" id="error-add_email">
                        @if ($errors->has('email') && old('_modal') === 'modal-add-user')
                            {{ $errors->first('email') }}
                        @endif
                    </span>
                </div>

                {{-- Username --}}
                <div class="form-group-admin">
                    <label>Username</label>
                    <input type="text"
                           id="add_user_name"
                           name="user_name"
                           value="{{ old('_modal') === 'modal-add-user' ? old('user_name') : '' }}"
                           class="form-control-admin {{ $errors->has('user_name') && old('_modal') === 'modal-add-user' ? 'is-invalid' : '' }}"
                           placeholder="Leave empty to auto-generate from email"
                           autocomplete="off">
                    <span class="field-error-text" id="error-add_user_name">
                        @if ($errors->has('user_name') && old('_modal') === 'modal-add-user')
                            {{ $errors->first('user_name') }}
                        @endif
                    </span>
                </div>

                {{-- Initial Password --}}
                <div class="form-group-admin">
                    <label>Initial Password <span style="color: #dc2626;">*</span></label>
                    <input type="password"
                           id="add_password"
                           name="password"
                           class="form-control-admin {{ $errors->has('password') && old('_modal') === 'modal-add-user' ? 'is-invalid' : '' }}"
                           placeholder="Minimum 6 characters"
                           autocomplete="new-password">
                    <span class="field-error-text" id="error-add_password">
                        @if ($errors->has('password') && old('_modal') === 'modal-add-user')
                            {{ $errors->first('password') }}
                        @endif
                    </span>
                </div>

                {{-- System Role --}}
                <div class="form-group-admin">
                    <label>System Role <span style="color: #dc2626;">*</span></label>
                    <select id="add_role" name="role" class="form-control-admin">
                        <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                        <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                {{-- Initial Status --}}
                <div class="form-group-admin">
                    <label>Initial Status <span style="color: #dc2626;">*</span></label>
                    <select id="add_status" name="status" class="form-control-admin">
                        <option value="ACTIVE" {{ old('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="PENDING" {{ old('status') === 'PENDING' ? 'selected' : '' }}>Pending (Email unverified)</option>
                        <option value="LOCKED" {{ old('status') === 'LOCKED' ? 'selected' : '' }}>Locked</option>
                    </select>
                </div>

                {{-- Initial Energy Points --}}
                <div class="form-group-admin">
                    <label>Initial Energy Points</label>
                    <input type="number"
                           id="add_energy"
                           name="energy"
                           value="{{ old('energy') }}"
                           class="form-control-admin"
                           placeholder="Default: 20 (999 for Admin)" min="0" max="99999">
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-add-user')">Cancel</button>
                <button type="submit" class="btn-admin-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: EDIT USER --}}
@php
    $openEditModal = $errors->any() && old('_modal') === 'modal-edit-user';
@endphp
<div id="modal-edit-user" class="admin-modal-backdrop {{ $openEditModal ? 'open' : '' }}">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Edit User</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-edit-user')">&times;</button>
        </div>
        <form id="form-edit-user" method="POST" action="{{ old('_edit_action', '') }}" novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="modal-edit-user">
            <input type="hidden" id="edit_action_holder" name="_edit_action" value="{{ old('_edit_action', '') }}">
            <div class="admin-modal-body">
                {{-- Full Name --}}
                <div class="form-group-admin">
                    <label>Full Name <span style="color: #dc2626;">*</span></label>
                    <input type="text"
                           id="edit_full_name"
                           name="full_name"
                           value="{{ old('_modal') === 'modal-edit-user' ? old('full_name') : '' }}"
                           class="form-control-admin {{ $errors->has('full_name') && old('_modal') === 'modal-edit-user' ? 'is-invalid' : '' }}">
                    <span class="field-error-text" id="error-edit_full_name">
                        @if ($errors->has('full_name') && old('_modal') === 'modal-edit-user')
                            {{ $errors->first('full_name') }}
                        @endif
                    </span>
                </div>

                {{-- Email Address --}}
                <div class="form-group-admin">
                    <label>Email Address <span style="color: #dc2626;">*</span></label>
                    <input type="email"
                           id="edit_email"
                           name="email"
                           value="{{ old('_modal') === 'modal-edit-user' ? old('email') : '' }}"
                           class="form-control-admin {{ $errors->has('email') && old('_modal') === 'modal-edit-user' ? 'is-invalid' : '' }}">
                    <span class="field-error-text" id="error-edit_email">
                        @if ($errors->has('email') && old('_modal') === 'modal-edit-user')
                            {{ $errors->first('email') }}
                        @endif
                    </span>
                </div>

                {{-- Username --}}
                <div class="form-group-admin">
                    <label>Username</label>
                    <input type="text"
                           id="edit_user_name"
                           name="user_name"
                           value="{{ old('_modal') === 'modal-edit-user' ? old('user_name') : '' }}"
                           class="form-control-admin {{ $errors->has('user_name') && old('_modal') === 'modal-edit-user' ? 'is-invalid' : '' }}">
                    <span class="field-error-text" id="error-edit_user_name">
                        @if ($errors->has('user_name') && old('_modal') === 'modal-edit-user')
                            {{ $errors->first('user_name') }}
                        @endif
                    </span>
                </div>

                {{-- Password --}}
                <div class="form-group-admin">
                    <label>New Password (Leave blank to keep unchanged)</label>
                    <input type="password"
                           id="edit_password"
                           name="password"
                           class="form-control-admin {{ $errors->has('password') && old('_modal') === 'modal-edit-user' ? 'is-invalid' : '' }}"
                           placeholder="Enter new password if changing"
                           autocomplete="new-password">
                    <span class="field-error-text" id="error-edit_password">
                        @if ($errors->has('password') && old('_modal') === 'modal-edit-user')
                            {{ $errors->first('password') }}
                        @endif
                    </span>
                </div>

                {{-- System Role --}}
                <div class="form-group-admin">
                    <label>System Role <span style="color: #dc2626;">*</span></label>
                    <select id="edit_role" name="role" class="form-control-admin">
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                {{-- Account Status --}}
                <div class="form-group-admin">
                    <label>Account Status <span style="color: #dc2626;">*</span></label>
                    <select id="edit_status" name="status" class="form-control-admin">
                        <option value="ACTIVE">Active</option>
                        <option value="PENDING">Pending</option>
                        <option value="LOCKED">Locked</option>
                    </select>
                </div>

                {{-- Energy Points --}}
                <div class="form-group-admin">
                    <label>Energy Points</label>
                    <input type="number"
                           id="edit_energy"
                           name="energy"
                           value="{{ old('_modal') === 'modal-edit-user' ? old('energy') : '' }}"
                           class="form-control-admin" min="0" max="99999">
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-edit-user')">Cancel</button>
                <button type="submit" class="btn-admin-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: RESET PASSWORD --}}
<div id="modal-reset-pwd" class="admin-modal-backdrop">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Reset User Password</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-reset-pwd')">&times;</button>
        </div>
        <form id="form-reset-pwd" method="POST" action="">
            @csrf
            @method('PATCH')
            <div class="admin-modal-body">
                <p style="font-size: 14px; color: #475569; margin-top: 0;">
                    Resetting password for: <strong id="reset-pwd-user-name"></strong>
                </p>
                <div class="form-group-admin">
                    <label>New Password</label>
                    <input type="text" name="new_password" class="form-control-admin" value="StudyPrep@2026">
                    <small style="color: #64748b; font-size: 12px; margin-top: 4px; display: block;">
                        Default suggested password is <code>StudyPrep@2026</code>. You can customize it as needed.
                    </small>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-reset-pwd')">Cancel</button>
                <button type="submit" class="btn-admin-primary">Confirm Reset</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: IMPORT USERS FROM CSV --}}
<div id="modal-import" class="admin-modal-backdrop">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Import Users from CSV</h3>
            <button type="button" class="admin-modal-close" onclick="closeAdminModal('modal-import')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="admin-modal-body">
                <p style="font-size: 13.5px; color: #475569; margin-top: 0;">
                    Upload a <code>.CSV</code> file with the following columns:
                    <strong>Full Name, Email, Role (student / teacher / admin), Status (ACTIVE / PENDING / LOCKED), Password</strong>.
                </p>
                <div style="margin-bottom: 16px;">
                    <a href="{{ route('admin.users.sample-csv') }}" class="btn-admin-action" style="font-size: 12.5px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Download Sample CSV Template</span>
                    </a>
                </div>
                <div class="form-group-admin">
                    <label>Select CSV File</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" class="form-control-admin" required>
                </div>
                <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 12px; font-size: 12px; color: #64748b;">
                    Example row: <code>John Doe, john.doe@example.com, student, ACTIVE, StudyPrep@123</code>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-admin-action" onclick="closeAdminModal('modal-import')">Cancel</button>
                <button type="submit" class="btn-admin-primary">Upload & Import</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAdminModal(id) {
        var el = document.getElementById(id);
        if (el) el.classList.add('open');
    }

    function closeAdminModal(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('open');
    }

    // Close modal when clicking outside
    document.querySelectorAll('.admin-modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) {
                backdrop.classList.remove('open');
            }
        });
    });

    // Helper functions for field errors
    function showFieldError(inputId, message) {
        var input = document.getElementById(inputId);
        if (input) {
            input.classList.add('is-invalid');
        }
        var errSpan = document.getElementById('error-' + inputId);
        if (errSpan) {
            errSpan.innerText = message;
            errSpan.style.display = 'block';
        }
    }

    function clearFieldError(inputId) {
        var input = document.getElementById(inputId);
        if (input) {
            input.classList.remove('is-invalid');
        }
        var errSpan = document.getElementById('error-' + inputId);
        if (errSpan) {
            errSpan.innerText = '';
        }
    }

    // Real-time clear on typing
    document.querySelectorAll('.form-control-admin').forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.id) {
                clearFieldError(this.id);
            }
        });
    });

    // Client-side instant validation on submit for Add User
    document.getElementById('form-add-user')?.addEventListener('submit', function(e) {
        var hasError = false;
        var fullNameInput = document.getElementById('add_full_name');
        var emailInput = document.getElementById('add_email');
        var passwordInput = document.getElementById('add_password');

        clearFieldError('add_full_name');
        clearFieldError('add_email');
        clearFieldError('add_password');

        if (!fullNameInput.value.trim()) {
            showFieldError('add_full_name', 'Please enter the user full name.');
            if (!hasError) fullNameInput.focus();
            hasError = true;
        }

        var emailVal = emailInput.value.trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailVal) {
            showFieldError('add_email', 'Please enter an email address.');
            if (!hasError) emailInput.focus();
            hasError = true;
        } else if (!emailRegex.test(emailVal)) {
            showFieldError('add_email', 'The email address format is invalid.');
            if (!hasError) emailInput.focus();
            hasError = true;
        }

        var passVal = passwordInput.value;
        if (!passVal) {
            showFieldError('add_password', 'Please enter an initial password.');
            if (!hasError) passwordInput.focus();
            hasError = true;
        } else if (passVal.length < 6) {
            showFieldError('add_password', 'The password must be at least 6 characters.');
            if (!hasError) passwordInput.focus();
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
        }
    });

    // Client-side instant validation on submit for Edit User
    document.getElementById('form-edit-user')?.addEventListener('submit', function(e) {
        var hasError = false;
        var fullNameInput = document.getElementById('edit_full_name');
        var emailInput = document.getElementById('edit_email');
        var passwordInput = document.getElementById('edit_password');

        clearFieldError('edit_full_name');
        clearFieldError('edit_email');
        clearFieldError('edit_password');

        if (!fullNameInput.value.trim()) {
            showFieldError('edit_full_name', 'Please enter the user full name.');
            if (!hasError) fullNameInput.focus();
            hasError = true;
        }

        var emailVal = emailInput.value.trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailVal) {
            showFieldError('edit_email', 'Please enter an email address.');
            if (!hasError) emailInput.focus();
            hasError = true;
        } else if (!emailRegex.test(emailVal)) {
            showFieldError('edit_email', 'The email address format is invalid.');
            if (!hasError) emailInput.focus();
            hasError = true;
        }

        if (passwordInput.value && passwordInput.value.length < 6) {
            showFieldError('edit_password', 'The new password must be at least 6 characters.');
            if (!hasError) passwordInput.focus();
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
        }
    });

    // Populate and open Edit Modal
    function openEditModal(user) {
        var form = document.getElementById('form-edit-user');
        var actionUrl = '/admin/users/' + user.user_id;
        form.action = actionUrl;
        document.getElementById('edit_action_holder').value = actionUrl;

        clearFieldError('edit_full_name');
        clearFieldError('edit_email');
        clearFieldError('edit_user_name');
        clearFieldError('edit_password');

        document.getElementById('edit_full_name').value = user.full_name || user.user_name || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_user_name').value = user.user_name || '';
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_energy').value = (user.energy !== undefined && user.energy !== null) ? user.energy : 20;

        var roleVal = 'student';
        var lowerRole = (user.role || '').toLowerCase();
        if (['teacher', 'instructor', 'giang_vien'].includes(lowerRole)) {
            roleVal = 'teacher';
        } else if (['admin', 'super_admin'].includes(lowerRole)) {
            roleVal = 'admin';
        }
        document.getElementById('edit_role').value = roleVal;

        var statusVal = (user.status || 'ACTIVE').toUpperCase();
        if (user.locked_until && new Date(user.locked_until) > new Date()) {
            statusVal = 'LOCKED';
        }
        document.getElementById('edit_status').value = statusVal;

        openAdminModal('modal-edit-user');
    }

    // Populate and open Reset Password Modal
    function openResetPasswordModal(userId, userName, userEmail) {
        var form = document.getElementById('form-reset-pwd');
        form.action = '/admin/users/' + userId + '/reset-password';
        document.getElementById('reset-pwd-user-name').innerText = userName + (userEmail ? ' (' + userEmail + ')' : '');
        openAdminModal('modal-reset-pwd');
    }

    // Handle Checkboxes & Bulk Action Bar
    var checkAll = document.getElementById('check-all-users');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.user-row-checkbox:not(:disabled)').forEach(function(cb) {
                cb.checked = checked;
            });
            updateSelectionBar();
        });
    }

    function handleRowCheckboxChange() {
        var allBoxes = document.querySelectorAll('.user-row-checkbox:not(:disabled)');
        var checkedBoxes = document.querySelectorAll('.user-row-checkbox:checked');
        if (checkAll) {
            checkAll.checked = allBoxes.length > 0 && allBoxes.length === checkedBoxes.length;
        }
        updateSelectionBar();
    }

    function updateSelectionBar() {
        var checkedBoxes = document.querySelectorAll('.user-row-checkbox:checked');
        var bar = document.getElementById('user-selection-bar');
        var badge = document.getElementById('selected-badge-count');
        if (checkedBoxes.length > 0) {
            if (bar) bar.style.display = 'flex';
            if (badge) badge.innerText = checkedBoxes.length;
        } else {
            if (bar) bar.style.display = 'none';
        }
    }

    function clearAllSelections() {
        if (checkAll) checkAll.checked = false;
        document.querySelectorAll('.user-row-checkbox').forEach(function(cb) {
            cb.checked = false;
        });
        updateSelectionBar();
    }

    function submitBulkAction(actionType) {
        var checkedBoxes = document.querySelectorAll('.user-row-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select at least one user.');
            return;
        }

        var actionLabels = {
            'delete': 'PERMANENTLY DELETE',
            'lock': 'LOCK',
            'unlock': 'UNLOCK'
        };

        var label = actionLabels[actionType] || actionType.toUpperCase();
        var confirmMsg = 'Are you sure you want to ' + label + ' ' + checkedBoxes.length + ' selected user(s)?';
        if (actionType === 'delete') {
            confirmMsg += ' This action cannot be undone!';
        }

        if (!confirm(confirmMsg)) {
            return;
        }

        var form = document.getElementById('bulk-action-form');
        document.getElementById('bulk-action-input').value = actionType;
        var container = document.getElementById('bulk-action-ids-container');
        container.innerHTML = '';

        checkedBoxes.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        form.submit();
    }
</script>
@endpush
