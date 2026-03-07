<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('company', 'like', '%' . $search . '%');
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        return view('app.customers.index', compact('customers'));
    }

    public function create()
    {
        $branches = Branch::where('status', true)->orderBy('name')->get();

        return view('app.customers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:150'],
            'source' => ['required', 'in:widget,ticket,import,api,manual'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,blocked,deleted'],
            'notes' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $validated['tenant_id'] = auth()->id();

        $customer = Customer::create(array_merge($validated, ['created_by' => auth()->id()]));

        ActivityLogService::created($customer);

        return redirect()->route('users/customers')->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        $branches = Branch::where('status', true)->orderBy('name')->get();

        ActivityLogService::viewed($customer);

        return view('app.customers.edit', compact('customer', 'branches'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:150'],
            'source' => ['required', 'in:widget,ticket,import,api,manual'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,blocked,deleted'],
            'notes' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $oldValues = $customer->only(array_keys($validated));

        $customer->update($validated);

        ActivityLogService::updated($customer, $oldValues);

        return redirect()->route('users/customers')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->update(['deleted_by' => auth()->id()]);

        ActivityLogService::deleted($customer);

        $customer->delete();

        return redirect()->route('users/customers')->with('success', 'Customer deleted successfully.');
    }
}
