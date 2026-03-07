<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('model_type', 'like', '%' . $search . '%')
                  ->orWhere('model_id', 'like', '%' . $search . '%')
                  ->orWhere('ip_address', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($model = $request->input('model')) {
            $query->where('model_type', 'like', '%' . $model);
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('app.audit.index', compact('logs'));
    }
}
