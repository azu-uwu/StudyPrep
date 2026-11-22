<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    /**
     * Display the list of users with search, role, status, and time filters.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $role = strtolower((string) $request->input('role', 'all'));
        $status = strtoupper((string) $request->input('status', 'ALL'));
        $timeFilter = (string) $request->input('time', 'all');
        $perPage = (int) $request->input('per_page', 20);
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 20;
        }

        $query = User::query()->withCount('attempts');

        // 1. Search by Full Name, Email, Username, or numeric ID
        if ($search !== '') {
            $cleanId = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanId) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");

                if ($cleanId !== '') {
                    $q->orWhere('user_id', (int) $cleanId);
                }
            });
        }

        // 2. Filter by Role
        if (in_array($role, ['student', 'learner', 'user'], true)) {
            $query->whereIn('role', ['student', 'learner', 'user']);
        } elseif (in_array($role, ['teacher', 'instructor', 'giang_vien'], true)) {
            $query->whereIn('role', ['teacher', 'instructor', 'giang_vien']);
        } elseif (in_array($role, ['admin', 'super_admin'], true)) {
            $query->whereIn('role', ['admin', 'super_admin']);
        } else {
            $role = 'all';
        }

        // 3. Filter by Status
        if ($status === 'ACTIVE') {
            $query->where('status', 'ACTIVE')
                ->where(function ($q) {
                    $q->whereNull('locked_until')->orWhere('locked_until', '<=', now());
                });
        } elseif ($status === 'PENDING') {
            $query->where('status', 'PENDING');
        } elseif ($status === 'LOCKED') {
            $query->where(function ($q) {
                $q->where('status', 'LOCKED')
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('locked_until')->where('locked_until', '>', now());
                    });
            });
        } else {
            $status = 'ALL';
        }

        // 4. Filter by Created Time
        if ($timeFilter === '7') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($timeFilter === '30') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($timeFilter === '90') {
            $query->where('created_at', '>=', now()->subDays(90));
        } else {
            $timeFilter = 'all';
        }

        // Order: Latest users first so newly created accounts appear immediately at the top
        $query->orderByDesc('user_id');

        $users = $query->paginate($perPage)->withQueryString();

        // Statistics
        $totalUsers = User::count();
        $totalStudents = User::whereIn('role', ['student', 'learner', 'user'])->count();
        $totalTeachers = User::whereIn('role', ['teacher', 'instructor', 'giang_vien'])->count();
        $totalAdmins = User::whereIn('role', ['admin', 'super_admin'])->count();
        $totalLocked = User::locked()->count();
        $totalActive = User::active()->count();

        $stats = [
            'total' => $totalUsers,
            'students' => $totalStudents,
            'teachers' => $totalTeachers,
            'admins' => $totalAdmins,
            'locked' => $totalLocked,
            'active' => $totalActive,
        ];

        return view('admin.users.index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
                'time' => $timeFilter,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'user_name' => 'nullable|string|max:100|unique:users,user_name',
            'role' => 'required|in:student,teacher,admin,user',
            'status' => 'required|in:ACTIVE,PENDING,LOCKED',
            'password' => 'required|string|min:6',
            'energy' => 'nullable|integer|min:0|max:99999',
        ], [
            'full_name.required' => 'Please provide the user full name.',
            'email.required' => 'Please provide a valid email address.',
            'email.email' => 'The email address format is invalid.',
            'email.unique' => 'This email address is already registered in the system.',
            'user_name.unique' => 'This username is already taken.',
            'password.required' => 'Please enter an initial password.',
            'password.min' => 'The password must be at least 6 characters.',
        ]);

        $userName = ! empty($validated['user_name'])
            ? Str::slug($validated['user_name'])
            : Str::slug(explode('@', $validated['email'])[0]);

        $baseUserName = $userName ?: 'user';
        $counter = 1;
        while (User::where('user_name', $userName)->exists()) {
            $userName = $baseUserName.$counter;
            $counter++;
        }

        $role = $validated['role'] === 'user' ? 'student' : $validated['role'];
        $isLocked = $validated['status'] === 'LOCKED';
        $energy = isset($validated['energy']) && $validated['energy'] !== null
            ? (int) $validated['energy']
            : ($role === 'admin' ? 999 : 5);

        $user = new User;
        $user->full_name = $validated['full_name'];
        $user->email = $validated['email'];
        $user->user_name = $userName;
        $user->role = $role;
        $user->status = $validated['status'];
        $user->password_hash = Hash::make($validated['password']);
        $user->energy = $energy;
        $user->created_at = now();
        $user->email_verified_at = $validated['status'] === 'PENDING' ? null : now();
        $user->locked_until = $isLocked ? now()->addDays(30) : null;
        $user->failed_login_attempts = 0;
        $user->save();

        // Activity log
        $adminId = auth()->id() ?? 1;
        DB::table('activity_log')->insert([
            'user_id' => $adminId,
            'action' => 'USER_CREATED',
            'description' => "Created user {$user->full_name} ({$user->email}) with role {$user->role}",
            'created_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->full_name}' ({$user->email}) has been created successfully.");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id'),
            ],
            'user_name' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('users', 'user_name')->ignore($user->user_id, 'user_id'),
            ],
            'role' => 'required|in:student,teacher,admin,user',
            'status' => 'required|in:ACTIVE,PENDING,LOCKED',
            'password' => 'nullable|string|min:6',
            'energy' => 'nullable|integer|min:0|max:99999',
        ], [
            'full_name.required' => 'Please provide the user full name.',
            'email.required' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use by another user.',
            'user_name.unique' => 'This username is already taken.',
            'password.min' => 'The new password must be at least 6 characters.',
        ]);

        // Security check: cannot demote own admin role
        if ($user->user_id === auth()->id() && $validated['role'] !== 'admin') {
            return redirect()->back()->with('error', 'You cannot remove your own administrator privileges.');
        }

        // Security check: cannot lock own account
        if ($user->user_id === auth()->id() && $validated['status'] === 'LOCKED') {
            return redirect()->back()->with('error', 'You cannot lock your own active account.');
        }

        $user->full_name = $validated['full_name'];
        $user->email = $validated['email'];
        if (! empty($validated['user_name'])) {
            $user->user_name = Str::slug($validated['user_name']);
        }
        $user->role = $validated['role'] === 'user' ? 'student' : $validated['role'];
        $user->status = $validated['status'];

        if (isset($validated['energy']) && $validated['energy'] !== null) {
            $user->energy = (int) $validated['energy'];
        }

        if ($validated['status'] === 'LOCKED') {
            $user->locked_until = now()->addDays(30);
        } else {
            $user->locked_until = null;
            $user->failed_login_attempts = 0;
            if ($validated['status'] === 'ACTIVE' && ! $user->email_verified_at) {
                $user->email_verified_at = now();
            }
        }

        if (! empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }

        $user->save();

        // Activity log
        $adminId = auth()->id() ?? 1;
        DB::table('activity_log')->insert([
            'user_id' => $adminId,
            'action' => 'USER_UPDATED',
            'description' => "Updated user {$user->full_name} (ID #{$user->user_id})",
            'created_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->full_name}' has been updated successfully.");
    }

    /**
     * Toggle lock/unlock status of a user quickly.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->user_id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot lock your own logged-in account.');
        }

        $isCurrentlyLocked = $user->isLocked();

        DB::transaction(function () use ($user, $isCurrentlyLocked) {
            $targetUser = User::where('user_id', $user->user_id)->lockForUpdate()->firstOrFail();
            $adminId = auth()->id() ?? 1;

            if ($isCurrentlyLocked) {
                $targetUser->status = 'ACTIVE';
                $targetUser->locked_until = null;
                $targetUser->failed_login_attempts = 0;
                $targetUser->save();

                DB::table('activity_log')->insert([
                    'user_id' => $adminId,
                    'action' => 'USER_UNLOCKED',
                    'description' => "Unlocked user {$targetUser->email} (ID #{$targetUser->user_id})",
                    'created_at' => now(),
                ]);
            } else {
                $targetUser->status = 'LOCKED';
                $targetUser->locked_until = now()->addDays(30);
                $targetUser->save();

                DB::table('activity_log')->insert([
                    'user_id' => $adminId,
                    'action' => 'USER_LOCKED',
                    'description' => "Locked user {$targetUser->email} (ID #{$targetUser->user_id})",
                    'created_at' => now(),
                ]);
            }
        });

        $message = $user->fresh()->isLocked()
            ? "User '{$user->full_name}' has been locked."
            : "User '{$user->full_name}' has been unlocked successfully.";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Reset user password to default or custom string.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $newPass = trim((string) $request->input('new_password', ''));
        if ($newPass === '') {
            $newPass = 'StudyPrep@'.date('Y');
        }

        if (strlen($newPass) < 6) {
            return redirect()->back()->with('error', 'The new password must be at least 6 characters long.');
        }

        $user->password_hash = Hash::make($newPass);
        $user->save();

        $adminId = auth()->id() ?? 1;
        DB::table('activity_log')->insert([
            'user_id' => $adminId,
            'action' => 'USER_PASSWORD_RESET',
            'description' => "Reset password for user {$user->email} (ID #{$user->user_id})",
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', "Password for user '{$user->full_name}' has been reset to: {$newPass}");
    }

    /**
     * Permanently remove a user from the system.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->user_id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own logged-in account.');
        }

        if ($user->isAdmin() && User::whereIn('role', ['admin', 'super_admin'])->count() <= 1) {
            return redirect()->back()->with('error', 'Cannot delete the last remaining administrator account.');
        }

        $userName = $user->full_name ?: $user->user_name;
        $userEmail = $user->email;
        $userId = $user->user_id;

        $user->delete();

        $adminId = auth()->id() ?? 1;
        DB::table('activity_log')->insert([
            'user_id' => $adminId,
            'action' => 'USER_DELETED',
            'description' => "Deleted user {$userName} ({$userEmail}) [ID #{$userId}]",
            'created_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' has been deleted permanently.");
    }

    /**
     * Execute bulk action on selected users (delete, lock, unlock).
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $action = (string) $request->input('action', '');
        $userIds = $request->input('user_ids', []);

        if (! is_array($userIds) || empty($userIds)) {
            return redirect()->back()->with('error', 'Please select at least one user to perform this bulk action.');
        }

        $adminId = auth()->id();
        // Prevent action on self
        $filteredIds = array_values(array_filter($userIds, fn ($id) => (int) $id !== (int) $adminId));

        if (empty($filteredIds)) {
            return redirect()->back()->with('error', 'Cannot perform bulk action on your own current account.');
        }

        if ($action === 'delete') {
            // Check remaining admins
            $adminCountInSelection = User::whereIn('user_id', $filteredIds)->whereIn('role', ['admin', 'super_admin'])->count();
            $totalAdmins = User::whereIn('role', ['admin', 'super_admin'])->count();
            if ($totalAdmins - $adminCountInSelection < 1) {
                return redirect()->back()->with('error', 'Cannot delete all system administrators in bulk.');
            }

            $count = User::whereIn('user_id', $filteredIds)->delete();

            DB::table('activity_log')->insert([
                'user_id' => $adminId ?? 1,
                'action' => 'BULK_USER_DELETE',
                'description' => "Bulk deleted {$count} user(s)",
                'created_at' => now(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "Successfully deleted {$count} user(s).");
        }

        if ($action === 'lock') {
            $count = User::whereIn('user_id', $filteredIds)->update([
                'status' => 'LOCKED',
                'locked_until' => now()->addDays(30),
            ]);

            DB::table('activity_log')->insert([
                'user_id' => $adminId ?? 1,
                'action' => 'BULK_USER_LOCK',
                'description' => "Bulk locked {$count} user(s)",
                'created_at' => now(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "Successfully locked {$count} user(s).");
        }

        if ($action === 'unlock') {
            $count = User::whereIn('user_id', $filteredIds)->update([
                'status' => 'ACTIVE',
                'locked_until' => null,
                'failed_login_attempts' => 0,
            ]);

            DB::table('activity_log')->insert([
                'user_id' => $adminId ?? 1,
                'action' => 'BULK_USER_UNLOCK',
                'description' => "Bulk unlocked {$count} user(s)",
                'created_at' => now(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "Successfully unlocked {$count} user(s).");
        }

        return redirect()->back()->with('error', 'Invalid bulk action requested.');
    }

    /**
     * Export all users to a standard CSV file with UTF-8 BOM.
     */
    public function export(): StreamedResponse
    {
        $fileName = 'studyprep_users_'.date('Y_m_d_His').'.csv';
        $users = User::withCount('attempts')->orderByDesc('user_id')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($users) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, [
                'User ID',
                'STP Code',
                'Full Name',
                'Email',
                'Username',
                'Role',
                'Status',
                'Energy',
                'Exam Attempts',
                'Created At',
            ]);

            foreach ($users as $u) {
                fputcsv($output, [
                    $u->user_id,
                    $u->stp_id,
                    $u->full_name,
                    $u->email,
                    $u->user_name,
                    $u->role_display,
                    $u->status_label,
                    $u->energy,
                    $u->attempts_count ?? 0,
                    $u->created_at ? $u->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($output);
        }, 200, $headers);
    }

    /**
     * Download a sample CSV file for importing users.
     */
    public function sampleCsv(): StreamedResponse
    {
        $fileName = 'sample_users_template.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, ['Full Name', 'Email', 'Role', 'Status', 'Password']);
            fputcsv($output, ['John Doe', 'john.doe@example.com', 'student', 'ACTIVE', 'StudyPrep@123']);
            fputcsv($output, ['Sarah Jenkins', 'sarah.jenkins@example.com', 'teacher', 'ACTIVE', 'StudyPrep@123']);
            fputcsv($output, ['Michael Chang', 'michael.chang@example.com', 'student', 'ACTIVE', 'StudyPrep@123']);

            fclose($output);
        }, 200, $headers);
    }

    /**
     * Import users from a CSV file.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'csv_file.required' => 'Please select a CSV file to upload.',
            'csv_file.mimes' => 'The file format must be CSV.',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $importedCount = 0;
        $skippedCount = 0;

        // Skip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Header line
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            $name = trim($row[0] ?? 'User');
            $email = trim($row[1] ?? '');
            $rawRole = strtolower(trim($row[2] ?? 'student'));
            $rawStatus = strtoupper(trim($row[3] ?? 'ACTIVE'));
            $rawPassword = trim($row[4] ?? 'StudyPrep@123');

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skippedCount++;

                continue;
            }

            if (User::where('email', $email)->exists()) {
                $skippedCount++;

                continue;
            }

            $role = match ($rawRole) {
                'teacher', 'instructor', 'giang_vien' => 'teacher',
                'admin', 'super_admin' => 'admin',
                default => 'student',
            };

            $status = in_array($rawStatus, ['ACTIVE', 'PENDING', 'LOCKED'], true) ? $rawStatus : 'ACTIVE';
            $password = strlen($rawPassword) >= 6 ? $rawPassword : 'StudyPrep@123';

            $userName = Str::slug(explode('@', $email)[0]);
            $baseName = $userName;
            $ctr = 1;
            while (User::where('user_name', $userName)->exists()) {
                $userName = $baseName.$ctr;
                $ctr++;
            }

            User::create([
                'full_name' => $name,
                'email' => $email,
                'user_name' => $userName,
                'role' => $role,
                'status' => $status,
                'password_hash' => Hash::make($password),
                'energy' => $role === 'admin' ? 999 : 5,
                'created_at' => now(),
                'email_verified_at' => $status === 'PENDING' ? null : now(),
                'locked_until' => $status === 'LOCKED' ? now()->addDays(30) : null,
            ]);

            $importedCount++;
        }

        fclose($handle);

        $adminId = auth()->id() ?? 1;
        DB::table('activity_log')->insert([
            'user_id' => $adminId,
            'action' => 'USERS_IMPORTED',
            'description' => "Imported {$importedCount} users from CSV (skipped {$skippedCount})",
            'created_at' => now(),
        ]);

        $msg = "Successfully imported {$importedCount} new user(s).";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} row(s) were skipped due to duplicate or invalid emails).";
        }

        return redirect()->route('admin.users.index')->with('success', $msg);
    }
}
