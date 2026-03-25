<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\App\CannedResponseController;
use Illuminate\Http\Request;

class AgentCannedResponseController extends CannedResponseController
{
    public function index(Request $request)
    {
        $view = parent::index($request);
        return view('agent.tickets.settings.canned-responses', $view->getData());
    }

    public function store(Request $request)
    {
        $response = parent::store($request);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/settings-canned-responses')->with('success', session('success'));
        }
        return $response;
    }

    public function update(Request $request, int $id)
    {
        $response = parent::update($request, $id);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/settings-canned-responses')->with('success', session('success'));
        }
        return $response;
    }

    public function destroy(int $id)
    {
        $response = parent::destroy($id);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.tickets/settings-canned-responses')->with('success', session('success'));
        }
        return $response;
    }
}
