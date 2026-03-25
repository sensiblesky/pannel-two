<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HelpPageSettingsController extends Controller
{
    public function index()
    {
        $settings = DB::table('ticket_settings')->whereNull('branch_id')->pluck('value', 'key');

        return view('app.settings.help-page', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'help_page_enabled' => ['nullable', 'in:0,1'],
            'help_page_title' => ['nullable', 'string', 'max:200'],
            'help_page_subtitle' => ['nullable', 'string', 'max:500'],
            'help_page_show_categories' => ['nullable', 'in:0,1'],
            'help_page_show_priority' => ['nullable', 'in:0,1'],
            'help_page_show_attachments' => ['nullable', 'in:0,1'],
            'help_page_show_track' => ['nullable', 'in:0,1'],
            'help_page_success_message' => ['nullable', 'string', 'max:1000'],
            'help_page_custom_css' => ['nullable', 'string', 'max:5000'],
        ]);

        // Checkboxes default to '0' when unchecked (not sent by browser)
        $toggleKeys = ['help_page_enabled', 'help_page_show_categories', 'help_page_show_priority', 'help_page_show_attachments', 'help_page_show_track'];
        foreach ($toggleKeys as $tk) {
            if (!isset($validated[$tk])) {
                $validated[$tk] = '0';
            }
        }

        foreach ($validated as $key => $value) {
            $exists = DB::table('ticket_settings')->where('key', $key)->whereNull('branch_id')->exists();
            if ($exists) {
                DB::table('ticket_settings')->where('key', $key)->whereNull('branch_id')->update(['value' => $value ?? '', 'updated_at' => now()]);
            } else {
                DB::table('ticket_settings')->insert([
                    'uuid' => Str::uuid(),
                    'key' => $key,
                    'branch_id' => null,
                    'value' => $value ?? '',
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('config/help-page')->with('success', 'Help page settings updated.');
    }
}
