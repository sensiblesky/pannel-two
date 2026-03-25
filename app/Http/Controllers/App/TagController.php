<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('tags')->whereNull('deleted_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $tags = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('app.tickets.settings.tags', compact('tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        DB::table('tags')->insert([
            'uuid' => Str::uuid(),
            'name' => $validated['name'],
            'code' => Str::slug($validated['name'], '_'),
            'color' => $validated['color'] ?? '#6366f1',
            'status' => $request->boolean('status') ? 1 : 0,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('tickets/settings-tags')->with('success', 'Tag created.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        DB::table('tags')->where('id', $id)->whereNull('deleted_at')->update([
            'name' => $validated['name'],
            'code' => Str::slug($validated['name'], '_'),
            'color' => $validated['color'] ?? '#6366f1',
            'status' => $request->boolean('status') ? 1 : 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('tickets/settings-tags')->with('success', 'Tag updated.');
    }

    public function destroy(int $id)
    {
        DB::table('tags')->where('id', $id)->update([
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        return redirect()->route('tickets/settings-tags')->with('success', 'Tag deleted.');
    }
}
