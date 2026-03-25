<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('users_agents')
            ->whereNull('users_agents.deleted_at')
            ->leftJoin('users', 'users_agents.user_id', '=', 'users.id')
            ->select(
                'users_agents.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.phone as user_phone',
                'users.avatar as user_avatar',
                'users.role as user_role',
            );

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users_agents.display_name', 'like', "%{$search}%")
                  ->orWhere('users_agents.specialization', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('users_agents.status', $request->input('status'));
        }

        if ($request->filled('department_id')) {
            $query->whereExists(function ($q) use ($request) {
                $q->select(DB::raw(1))
                  ->from('agent_department')
                  ->whereColumn('agent_department.agent_id', 'users_agents.id')
                  ->where('agent_department.department_id', $request->input('department_id'));
            });
        }

        $agents = $query->orderByDesc('users_agents.created_at')->paginate(20)->withQueryString();

        // Attach departments to each agent
        $agentIds = $agents->pluck('id')->toArray();
        $agentDepartments = DB::table('agent_department')
            ->whereIn('agent_department.agent_id', $agentIds)
            ->join('departments', 'agent_department.department_id', '=', 'departments.id')
            ->select('agent_department.agent_id', 'departments.id as dept_id', 'departments.name as dept_name')
            ->get()
            ->groupBy('agent_id');

        $departments = DB::table('departments')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        // Available users (not already agents)
        $existingUserIds = DB::table('users_agents')->whereNull('deleted_at')->pluck('user_id')->toArray();
        $availableUsers = DB::table('users')
            ->whereNull('deleted_at')
            ->where('status', true)
            ->whereNotIn('id', $existingUserIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('app.tickets.settings.agents', compact('agents', 'agentDepartments', 'departments', 'availableUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', Rule::unique('users_agents', 'user_id')->whereNull('deleted_at')],
            'display_name' => ['nullable', 'string', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:200'],
            'max_tickets' => ['nullable', 'integer', 'min:1', 'max:999'],
            'departments' => ['required', 'array', 'min:1'],
            'departments.*' => ['exists:departments,id'],
            'status' => ['nullable', 'in:0,1'],
        ]);

        $agentId = DB::table('users_agents')->insertGetId([
            'uuid' => Str::uuid(),
            'user_id' => $validated['user_id'],
            'display_name' => $validated['display_name'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'max_tickets' => $validated['max_tickets'] ?? null,
            'is_available' => true,
            'status' => isset($validated['status']) ? (bool) $validated['status'] : true,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($validated['departments'] as $deptId) {
            DB::table('agent_department')->insert([
                'agent_id' => $agentId,
                'department_id' => $deptId,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        }

        return redirect()->route('tickets/settings-agents')->with('success', 'Agent created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $agent = DB::table('users_agents')->where('id', $id)->whereNull('deleted_at')->firstOrFail();

        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:200'],
            'max_tickets' => ['nullable', 'integer', 'min:1', 'max:999'],
            'departments' => ['required', 'array', 'min:1'],
            'departments.*' => ['exists:departments,id'],
            'status' => ['nullable', 'in:0,1'],
            'is_available' => ['nullable', 'in:0,1'],
        ]);

        DB::table('users_agents')->where('id', $id)->update([
            'display_name' => $validated['display_name'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'max_tickets' => $validated['max_tickets'] ?? null,
            'is_available' => isset($validated['is_available']) ? (bool) $validated['is_available'] : $agent->is_available,
            'status' => isset($validated['status']) ? (bool) $validated['status'] : true,
            'updated_at' => now(),
        ]);

        // Sync departments
        DB::table('agent_department')->where('agent_id', $id)->delete();
        foreach ($validated['departments'] as $deptId) {
            DB::table('agent_department')->insert([
                'agent_id' => $id,
                'department_id' => $deptId,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        }

        return redirect()->route('tickets/settings-agents')->with('success', 'Agent updated successfully.');
    }

    public function destroy(int $id)
    {
        DB::table('users_agents')->where('id', $id)->whereNull('deleted_at')->update([
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        return redirect()->route('tickets/settings-agents')->with('success', 'Agent removed successfully.');
    }
}
