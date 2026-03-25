<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageAlertController extends Controller
{
    public const TYPES = [
        'ticket' => 'Incoming Ticket',
        'message' => 'Incoming Message / Live Chat',
    ];

    public function index()
    {
        $alerts = DB::table('message_alerts')
            ->whereNull('deleted_at')
            ->orderBy('type')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $ticketAlerts = $alerts->where('type', 'ticket');
        $messageAlerts = $alerts->where('type', 'message');
        $types = self::TYPES;

        return view('app.settings.message-alerts', compact('alerts', 'ticketAlerts', 'messageAlerts', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:ticket,message'],
            'audio_file' => ['required', 'file', 'mimes:mp3,wav,ogg,aac,m4a', 'max:5120'],
        ]);

        $type = $request->input('type');
        $file = $request->file('audio_file');
        $path = $file->store('message-alerts', 'public');

        $isFirst = DB::table('message_alerts')->whereNull('deleted_at')->where('type', $type)->count() === 0;

        DB::table('message_alerts')->insert([
            'uuid' => Str::uuid(),
            'name' => $request->input('name'),
            'type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'is_default' => $isFirst,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($isFirst) {
            $this->cacheDefaultAlert($type, $path);
        }

        $label = self::TYPES[$type] ?? $type;

        return redirect()->route('config/message-alerts')->with('success', "Alert sound for '{$label}' uploaded successfully.");
    }

    public function setDefault(int $id)
    {
        $alert = DB::table('message_alerts')->whereNull('deleted_at')->where('id', $id)->first();
        if (!$alert) {
            return redirect()->route('config/message-alerts')->with('error', 'Alert not found.');
        }

        // Only unset default within the same type
        DB::table('message_alerts')->whereNull('deleted_at')->where('type', $alert->type)->update(['is_default' => false]);
        DB::table('message_alerts')->where('id', $id)->update(['is_default' => true, 'updated_at' => now()]);

        $this->cacheDefaultAlert($alert->type, $alert->file_path);

        return redirect()->route('config/message-alerts')->with('success', "'{$alert->name}' set as default for " . (self::TYPES[$alert->type] ?? $alert->type) . '.');
    }

    public function destroy(int $id)
    {
        $alert = DB::table('message_alerts')->whereNull('deleted_at')->where('id', $id)->first();
        if (!$alert) {
            return redirect()->route('config/message-alerts')->with('error', 'Alert not found.');
        }

        if ($alert->is_default) {
            return redirect()->route('config/message-alerts')->with('error', 'Cannot delete the default alert. Set another alert as default first.');
        }

        Storage::disk('public')->delete($alert->file_path);

        DB::table('message_alerts')->where('id', $id)->update([
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);

        return redirect()->route('config/message-alerts')->with('success', 'Alert sound deleted.');
    }

    /**
     * Return the default alert URL as JSON for a given type.
     */
    public function getDefault(Request $request)
    {
        $type = $request->input('type', 'ticket');
        if (!in_array($type, ['ticket', 'message'])) {
            $type = 'ticket';
        }

        $url = self::getDefaultAlertUrl($type);

        return response()->json(['type' => $type, 'url' => $url]);
    }

    /**
     * Cache the default alert file path per type.
     */
    private function cacheDefaultAlert(string $type, string $filePath): void
    {
        Cache::forever("default_message_alert_{$type}", $filePath);
    }

    /**
     * Get the cached default alert URL for a type (static helper for use anywhere).
     */
    public static function getDefaultAlertUrl(string $type = 'ticket'): ?string
    {
        $path = Cache::get("default_message_alert_{$type}");

        if (!$path) {
            $default = DB::table('message_alerts')
                ->whereNull('deleted_at')
                ->where('type', $type)
                ->where('is_default', true)
                ->value('file_path');

            if ($default) {
                Cache::forever("default_message_alert_{$type}", $default);
                $path = $default;
            }
        }

        return $path ? Storage::disk('public')->url($path) : null;
    }
}
