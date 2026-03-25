<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\App\ProfileController;
use Illuminate\Http\Request;

class AgentProfileController extends ProfileController
{
    public function index(Request $request)
    {
        $view = parent::index($request);
        return view('agent.profile.index', $view->getData());
    }

    public function updateAccount(Request $request)
    {
        $response = parent::updateAccount($request);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.profile', ['tab' => $request->input('tab', 'account')]);
        }
        return $response;
    }

    public function updatePassword(Request $request)
    {
        $response = parent::updatePassword($request);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.profile', ['tab' => 'security']);
        }
        return $response;
    }

    public function confirmTwoFactor(Request $request)
    {
        $response = parent::confirmTwoFactor($request);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.profile', ['tab' => 'security']);
        }
        return $response;
    }

    public function confirmEmailTwoFactor(Request $request)
    {
        $response = parent::confirmEmailTwoFactor($request);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.profile', ['tab' => 'security']);
        }
        return $response;
    }

    public function disableTwoFactor(Request $request)
    {
        $response = parent::disableTwoFactor($request);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.profile', ['tab' => 'security']);
        }
        return $response;
    }

    public function verifyEmail(Request $request)
    {
        $response = parent::verifyEmail($request);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.profile');
        }
        return $response;
    }

    public function destroySession(Request $request, $session)
    {
        $response = parent::destroySession($request, $session);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.profile', ['tab' => 'sessions']);
        }
        return $response;
    }

    public function destroyOtherSessions(Request $request)
    {
        $response = parent::destroyOtherSessions($request);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return redirect()->route('agent.profile', ['tab' => 'sessions']);
        }
        return $response;
    }
}
