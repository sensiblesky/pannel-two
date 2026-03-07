<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::with('branch');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $departments = $query->latest()->paginate(20)->withQueryString();
        $branches = Branch::where('status', true)->orderBy('name')->get();

        return view('app.departments.index', compact('departments', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('status', true)->orderBy('name')->get();

        return view('app.departments.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:30', 'unique:departments'],
            'description' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'status' => ['required', 'boolean'],
        ]);

        $department = Department::create(array_merge($validated, ['created_by' => auth()->id()]));

        ActivityLogService::created($department);

        return redirect()->route('config/departments')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $branches = Branch::where('status', true)->orderBy('name')->get();

        ActivityLogService::viewed($department);

        return view('app.departments.edit', compact('department', 'branches'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:30', 'unique:departments,code,' . $department->id],
            'description' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'status' => ['required', 'boolean'],
        ]);

        $oldValues = $department->only(array_keys($validated));

        $department->update($validated);

        ActivityLogService::updated($department, $oldValues);

        return redirect()->route('config/departments')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->update(['deleted_by' => auth()->id()]);

        ActivityLogService::deleted($department);

        $department->delete();

        return redirect()->route('config/departments')->with('success', 'Department deleted successfully.');
    }
}
