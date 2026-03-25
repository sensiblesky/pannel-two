<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        $branches = $query->withCount(['users'])->latest()->paginate(20)->withQueryString();

        return view('app.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('app.branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:30', 'unique:branches'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'boolean'],
        ]);

        $branch = Branch::create(array_merge($validated, ['created_by' => auth()->id()]));

        ActivityLogService::created($branch);

        return redirect()->route('config/branches')->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch)
    {
        ActivityLogService::viewed($branch);

        return view('app.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:30', 'unique:branches,code,' . $branch->id],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'boolean'],
        ]);

        $oldValues = $branch->only(array_keys($validated));

        $branch->update($validated);

        ActivityLogService::updated($branch, $oldValues);

        return redirect()->route('config/branches')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $branch->update(['deleted_by' => auth()->id()]);

        ActivityLogService::deleted($branch);

        $branch->delete();

        return redirect()->route('config/branches')->with('success', 'Branch deleted successfully.');
    }
}
