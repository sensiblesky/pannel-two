<?php

namespace App\Http\Controllers;

use App\Realtime\RealtimeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RealtimeController extends Controller
{
    /**
     * Return the active realtime configuration for the frontend.
     * GET /api/realtime/config
     */
    public function config(RealtimeManager $realtime)
    {
        return response()->json($realtime->frontendPayload());
    }

    /**
     * Authorize a private/presence channel for Pusher or Ably.
     * POST /api/realtime/auth
     */
    public function auth(Request $request, RealtimeManager $realtime)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $channelName = $request->input('channel_name', '');
        $socketId = $request->input('socket_id', '');

        // Validate the user has access to this channel.
        // Channel format: private-ticket.{ticketId} or private:ticket.{ticketId}
        $ticketId = null;
        if (preg_match('/(?:private[-:])?ticket\.(\d+)/', $channelName, $matches)) {
            $ticketId = (int) $matches[1];
        }

        if (!$ticketId) {
            return response()->json(['error' => 'Invalid channel'], 403);
        }

        // Authorization: check that the user owns or is assigned to this ticket.
        $ticket = DB::table('tickets')->where('id', $ticketId)->first();
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 403);
        }

        $authorized = false;
        if ($user->role === 'customer') {
            $authorized = (int) $ticket->customer_id === (int) $user->id;
        } elseif (in_array($user->role, ['admin', 'agent'])) {
            $authorized = true; // Admin/agents can access any ticket.
        }

        if (!$authorized) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $authData = $realtime->authorizeChannel($channelName, $socketId, $user);

        if ($authData === null) {
            return response()->json(['error' => 'Driver does not support channel auth'], 400);
        }

        return response()->json($authData);
    }
}
