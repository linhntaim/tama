<?php

namespace App\Http\Controllers\Api\Auth\Sanctum;

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

    protected function setAuthUser(Request $request, User $user): void
    {
        $user->createToken($request->input('device_name', (static function (Agent $agent) {
            return ($device = $agent->device())
                ? $device . (static function ($infos) {
                    return count($infos) ? sprintf(' (%s)', implode(' - ', $infos)) : '';
                })(array_filter([
                    ($platform = $agent->platform()) ? $platform . (($version = $agent->version($platform)) ? " $version" : '') : null,
                    ($browser = $agent->browser()) ? $browser . (($version = $agent->version($browser)) ? " $version" : '') : null,
                ]))
                : 'Unknown';
        })(AgentFacade::getFacadeRoot())));
        parent::setAuthUser($request, $user);
    }
}
