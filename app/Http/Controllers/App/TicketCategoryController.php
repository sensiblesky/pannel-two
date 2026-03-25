<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('ticket_categories')->whereNull('deleted_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $categories = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('app.tickets.settings.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        DB::table('ticket_categories')->insert([
            'uuid' => Str::uuid(),
            'name' => $validated['name'],
            'code' => Str::slug($validated['name'], '_'),
            'description' => $validated['description'] ?? null,
            'status' => $request->boolean('status') ? 1 : 0,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('tickets/settings-categories')->with('success', 'Category created.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        DB::table('ticket_categories')->where('id', $id)->whereNull('deleted_at')->update([
            'name' => $validated['name'],
            'code' => Str::slug($validated['name'], '_'),
            'description' => $validated['description'] ?? null,
            'status' => $request->boolean('status') ? 1 : 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('tickets/settings-categories')->with('success', 'Category updated.');
    }

    public function destroy(int $id)
    {
        DB::table('ticket_categories')->where('id', $id)->update([
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        return redirect()->route('tickets/settings-categories')->with('success', 'Category deleted.');
    }
}
