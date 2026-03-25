<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketFeatureSettingsController extends Controller
{
    public function index()
    {
        $settings = DB::table('ticket_settings')->whereNull('branch_id')->pluck('value', 'key');

        return view('app.tickets.settings.general', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_status' => ['nullable', 'string'],
            'default_priority' => ['nullable', 'string'],
            'auto_assign' => ['nullable', 'in:0,1'],
            'allow_customer_close' => ['nullable', 'in:0,1'],
            'allow_customer_reopen' => ['nullable', 'in:0,1'],
            'require_category' => ['nullable', 'in:0,1'],
            'max_attachments' => ['nullable', 'integer', 'min:0', 'max:20'],
            'max_attachment_size_mb' => ['nullable', 'integer', 'min:1', 'max:50'],
            'auto_close_days' => ['nullable', 'integer', 'min:0'],
            'sla_response_hours' => ['nullable', 'integer', 'min:0'],
            'sla_resolution_hours' => ['nullable', 'integer', 'min:0'],
            'ticket_number_prefix' => ['nullable', 'string', 'max:20'],
            'enable_customer_portal' => ['nullable', 'in:0,1'],
            'enable_satisfaction_survey' => ['nullable', 'in:0,1'],
            'allow_customer_attachments' => ['nullable', 'in:0,1'],
        ]);

        $toggleKeys = ['auto_assign', 'allow_customer_close', 'allow_customer_reopen', 'require_category', 'enable_customer_portal', 'enable_satisfaction_survey', 'allow_customer_attachments'];
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

        return redirect()->route('tickets/settings-general')->with('success', 'Settings updated.');
    }
}
