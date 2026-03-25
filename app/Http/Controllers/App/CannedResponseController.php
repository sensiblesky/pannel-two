<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CannedResponseController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('canned_responses')
            ->leftJoin('users', 'canned_responses.created_by', '=', 'users.id')
            ->whereNull('canned_responses.deleted_at')
            ->select('canned_responses.*', 'users.name as creator_name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('canned_responses.title', 'like', "%{$search}%")
                  ->orWhere('canned_responses.shortcut', 'like', "%{$search}%")
                  ->orWhere('canned_responses.message', 'like', "%{$search}%")
                  ->orWhere('canned_responses.category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('canned_responses.category', $request->input('category'));
        }

        $responses = $query->orderBy('canned_responses.title')->paginate(15)->withQueryString();

        $categories = DB::table('canned_responses')
            ->whereNull('canned_responses.deleted_at')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return view('app.tickets.settings.canned-responses', compact('responses', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'shortcut' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_shared' => ['nullable'],
        ]);

        DB::table('canned_responses')->insert([
            'uuid' => Str::uuid(),
            'title' => $validated['title'],
            'shortcut' => $validated['shortcut'] ?? null,
            'message' => $validated['message'],
            'category' => $validated['category'] ?? null,
            'is_shared' => $request->boolean('is_shared', true),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('tickets/settings-canned-responses')->with('success', 'Canned response created.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'shortcut' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_shared' => ['nullable'],
        ]);

        DB::table('canned_responses')->where('id', $id)->whereNull('deleted_at')->update([
            'title' => $validated['title'],
            'shortcut' => $validated['shortcut'] ?? null,
            'message' => $validated['message'],
            'category' => $validated['category'] ?? null,
            'is_shared' => $request->boolean('is_shared', true),
            'updated_at' => now(),
        ]);

        return redirect()->route('tickets/settings-canned-responses')->with('success', 'Canned response updated.');
    }

    public function destroy(int $id)
    {
        DB::table('canned_responses')->where('id', $id)->whereNull('deleted_at')->update([
            'deleted_at' => now(),
            'deleted_by' => auth()->id(),
        ]);

        return redirect()->route('tickets/settings-canned-responses')->with('success', 'Canned response deleted.');
    }

    /**
     * JSON search endpoint for the ticket reply box.
     * GET /app/tickets/canned-responses/search?q=keyword
     */
    public function search(Request $request)
    {
        $q = $request->input('q', '');
        $userId = auth()->id();

        $query = DB::table('canned_responses')
            ->whereNull('deleted_at')
            ->where(function ($qb) use ($userId) {
                $qb->where('is_shared', true)
                   ->orWhere('created_by', $userId);
            })
            ->select('id', 'title', 'shortcut', 'message', 'category');

        if ($q !== '') {
            $query->where(function ($qb) use ($q) {
                $qb->where('title', 'like', "%{$q}%")
                   ->orWhere('shortcut', 'like', "%{$q}%")
                   ->orWhere('message', 'like', "%{$q}%")
                   ->orWhere('category', 'like', "%{$q}%");
            });
        }

        $results = $query->orderBy('title')->limit(20)->get();

        return response()->json($results);
    }
}
