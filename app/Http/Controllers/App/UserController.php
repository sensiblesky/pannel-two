<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Models\UserSuspension;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('app.users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::where('status', true)->orderBy('name')->get();

        return view('app.users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::min(7)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:admin,manager,customer'],
            'status' => ['required', 'boolean'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create(array_merge($validated, ['created_by' => auth()->id()]));

        ActivityLogService::created($user);

        return redirect()->route('users/index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $user->load('activeSuspension', 'suspensions.suspendedByUser', 'suspensions.unsuspendedByUser');
        $branches = Branch::where('status', true)->orderBy('name')->get();

        ActivityLogService::viewed($user);

        return view('app.users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', Password::min(7)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:admin,manager,customer'],
            'status' => ['required', 'boolean'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $oldValues = $user->only(array_keys($validated));

        $user->update($validated);

        ActivityLogService::updated($user, $oldValues);

        return redirect()->route('users/index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->update(['deleted_by' => auth()->id()]);

        ActivityLogService::deleted($user);

        $user->delete();

        return redirect()->route('users/index')->with('success', 'User deleted successfully.');
    }

    public function suspend(Request $request, User $user)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($user->is_suspended) {
            return back()->with('error', 'User is already suspended.');
        }

        UserSuspension::create([
            'user_id' => $user->id,
            'reason' => $request->input('reason'),
            'suspended_by' => auth()->id(),
            'suspended_at' => now(),
        ]);

        $user->update(['is_suspended' => true]);

        ActivityLogService::log('SUSPEND', $user, null, ['reason' => $request->input('reason')]);

        return back()->with('success', 'User has been suspended.');
    }

    public function unsuspend(User $user)
    {
        if (!$user->is_suspended) {
            return back()->with('error', 'User is not suspended.');
        }

        $activeSuspension = $user->activeSuspension;
        if ($activeSuspension) {
            $activeSuspension->update([
                'unsuspended_by' => auth()->id(),
                'unsuspended_at' => now(),
            ]);
        }

        $user->update(['is_suspended' => false]);

        ActivityLogService::log('UNSUSPEND', $user);

        return back()->with('success', 'User has been unsuspended.');
    }
}
