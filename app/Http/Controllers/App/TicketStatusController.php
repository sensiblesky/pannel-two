<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketStatusController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('ticket_statuses')->whereNull('deleted_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $statuses = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        return view('app.tickets.settings.statuses', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:ticket_statuses,code'],
            'color' => ['required', 'string', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        DB::table('ticket_statuses')->insert([
            'uuid' => Str::uuid(),
            'name' => $validated['name'],
            'code' => $validated['code'],
            'color' => $validated['color'],
            'is_closed' => $request->boolean('is_closed') ? 1 : 0,
            'sort_order' => $validated['sort_order'],
            'status' => $request->boolean('status') ? 1 : 0,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('tickets/settings-statuses')->with('success', 'Status created.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', "unique:ticket_statuses,code,{$id}"],
            'color' => ['required', 'string', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        DB::table('ticket_statuses')->where('id', $id)->whereNull('deleted_at')->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'color' => $validated['color'],
            'is_closed' => $request->boolean('is_closed') ? 1 : 0,
            'sort_order' => $validated['sort_order'],
            'status' => $request->boolean('status') ? 1 : 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('tickets/settings-statuses')->with('success', 'Status updated.');
    }

    public function destroy(int $id)
    {
        DB::table('ticket_statuses')->where('id', $id)->update([
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        return redirect()->route('tickets/settings-statuses')->with('success', 'Status deleted.');
    }
}
