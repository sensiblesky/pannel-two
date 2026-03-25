<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Models\UserSuspension;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('app.users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::where('status', true)->orderBy('name')->get();
        $departments = DB::table('departments')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name', 'status']);

        return view('app.users.create', compact('branches', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::min(7)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:admin,agent,customer'],
            'status' => ['required', 'boolean'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            // Customer fields
            'company' => ['nullable', 'string', 'max:150'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'customer_notes' => ['nullable', 'string', 'max:5000'],
            // Agent fields
            'display_name' => ['nullable', 'string', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:200'],
            'max_tickets' => ['nullable', 'integer', 'min:1', 'max:999'],
            'departments' => ['nullable', 'array'],
            'departments.*' => ['exists:departments,id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $userData = collect($validated)->only(['name', 'email', 'password', 'phone', 'role', 'status', 'branch_id'])->toArray();
        $user = User::create(array_merge($userData, ['created_by' => auth()->id()]));

        if ($validated['role'] === 'customer') {
            DB::table('users_customers')->insert([
                'user_id' => $user->id,
                'company' => $validated['company'] ?? null,
                'country' => $validated['country'] ?? null,
                'city' => $validated['city'] ?? null,
                'notes' => $validated['customer_notes'] ?? null,
                'source' => 'manual',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif ($validated['role'] === 'agent') {
            $agentId = DB::table('users_agents')->insertGetId([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'display_name' => $validated['display_name'] ?? null,
                'specialization' => $validated['specialization'] ?? null,
                'max_tickets' => $validated['max_tickets'] ?? null,
                'is_available' => true,
                'status' => true,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!empty($validated['departments'])) {
                foreach ($validated['departments'] as $deptId) {
                    DB::table('agent_department')->insert([
                        'agent_id' => $agentId,
                        'department_id' => $deptId,
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                    ]);
                }
            }
        }

        ActivityLogService::created($user);

        return redirect()->route('users/index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $user->load('activeSuspension', 'suspensions.suspendedByUser', 'suspensions.unsuspendedByUser');
        $branches = Branch::where('status', true)->orderBy('name')->get();
        $departments = DB::table('departments')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name', 'status']);

        $customerInfo = null;
        $agentInfo = null;
        $agentDepartments = [];
        if ($user->role === 'customer') {
            $customerInfo = DB::table('users_customers')->where('user_id', $user->id)->first();
        } elseif ($user->role === 'agent') {
            $agentInfo = DB::table('users_agents')->where('user_id', $user->id)->whereNull('deleted_at')->first();
            if ($agentInfo) {
                $agentDepartments = DB::table('agent_department')->where('agent_id', $agentInfo->id)->pluck('department_id')->toArray();
            }
        }

        ActivityLogService::viewed($user);

        return view('app.users.edit', compact('user', 'branches', 'departments', 'customerInfo', 'agentInfo', 'agentDepartments'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', Password::min(7)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:admin,agent,customer'],
            'status' => ['required', 'boolean'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            // Customer fields
            'company' => ['nullable', 'string', 'max:150'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'customer_notes' => ['nullable', 'string', 'max:5000'],
            // Agent fields
            'display_name' => ['nullable', 'string', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:200'],
            'max_tickets' => ['nullable', 'integer', 'min:1', 'max:999'],
            'departments' => ['nullable', 'array'],
            'departments.*' => ['exists:departments,id'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $userData = collect($validated)->only(['name', 'email', 'password', 'phone', 'role', 'status', 'branch_id'])->filter(fn ($v, $k) => $k !== 'password' || $v !== null)->toArray();
        $oldValues = $user->only(array_keys($userData));

        $user->update($userData);

        // Handle role-specific extended info
        // First: clean up records from previous role that no longer applies
        if ($validated['role'] !== 'customer') {
            DB::table('users_customers')->where('user_id', $user->id)->delete();
        }
        if ($validated['role'] !== 'agent') {
            $agentRecord = DB::table('users_agents')->where('user_id', $user->id)->first();
            if ($agentRecord) {
                DB::table('agent_department')->where('agent_id', $agentRecord->id)->delete();
                DB::table('users_agents')->where('id', $agentRecord->id)->delete();
            }
        }

        // Then: upsert the new role's extended info
        if ($validated['role'] === 'customer') {
            $customerData = [
                'company' => $validated['company'] ?? null,
                'country' => $validated['country'] ?? null,
                'city' => $validated['city'] ?? null,
                'notes' => $validated['customer_notes'] ?? null,
                'updated_at' => now(),
            ];
            $exists = DB::table('users_customers')->where('user_id', $user->id)->exists();
            if ($exists) {
                DB::table('users_customers')->where('user_id', $user->id)->update($customerData);
            } else {
                DB::table('users_customers')->insert(array_merge($customerData, [
                    'user_id' => $user->id,
                    'source' => 'manual',
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]));
            }
        } elseif ($validated['role'] === 'agent') {
            $agentData = [
                'display_name' => $validated['display_name'] ?? null,
                'specialization' => $validated['specialization'] ?? null,
                'max_tickets' => $validated['max_tickets'] ?? null,
                'updated_at' => now(),
            ];
            $existingAgent = DB::table('users_agents')->where('user_id', $user->id)->first();
            if ($existingAgent) {
                DB::table('users_agents')->where('id', $existingAgent->id)->update(array_merge($agentData, [
                    'deleted_at' => null,
                    'deleted_by' => null,
                ]));
                $agentId = $existingAgent->id;
            } else {
                $agentId = DB::table('users_agents')->insertGetId(array_merge($agentData, [
                    'uuid' => Str::uuid(),
                    'user_id' => $user->id,
                    'is_available' => true,
                    'status' => true,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]));
            }
            // Sync departments
            DB::table('agent_department')->where('agent_id', $agentId)->delete();
            if (!empty($validated['departments'])) {
                foreach ($validated['departments'] as $deptId) {
                    DB::table('agent_department')->insert([
                        'agent_id' => $agentId,
                        'department_id' => $deptId,
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                    ]);
                }
            }
        }

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
