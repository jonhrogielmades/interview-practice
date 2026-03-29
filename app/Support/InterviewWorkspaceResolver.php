<?php

namespace App\Support;

use Illuminate\Support\Str;

class InterviewWorkspaceResolver
{
    public const SESSION_KEY = 'interview_workspace_token';

    public function currentToken(): string
    {
        $request = request();

        if (! $request->hasSession()) {
            return 'guest-workspace';
        }

        $session = $request->session();
        $token = $session->get(self::SESSION_KEY);

        if (! is_string($token) || $token === '') {
            $token = (string) Str::uuid();
            $session->put(self::SESSION_KEY, $token);
        }

        return $token;
    }
}
