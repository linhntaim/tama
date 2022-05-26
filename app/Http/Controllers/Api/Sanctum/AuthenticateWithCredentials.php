<?php

namespace App\Http\Controllers\Api\Sanctum;

use App\Actions\Fortify\AuthenticateWithCredentials as BaseAuthenticateWithCredentials;
use App\Models\User;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Jenssegers\Agent\Facades\Agent as AgentFacade;

class AuthenticateWithCredentials extends BaseAuthenticateWithCredentials
{
    protected function guard(): string
    {
        return 'sanctum';
    }

    protected function agent(): Agent
    {
        return AgentFacade::getFacadeRoot();
    }

    protected function setAuthUser(Request $request, User $user)
    {
        $user->createToken($request->input('device_name', (function (Agent $agent) {
            return sprintf(
                '%s (%s) - %s (%s)',
                $platform = $agent->platform(),
                $agent->version($platform),
                $browser = $agent->browser(),
                $agent->version($browser)
            );
        })(AgentFacade::getFacadeRoot())));
        parent::setAuthUser($request, $user);
    }
}
