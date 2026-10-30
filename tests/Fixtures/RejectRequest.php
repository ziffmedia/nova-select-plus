<?php

namespace Tests\Fixtures;

class RejectRequest
{
    public function handle($request, $next)
    {
        abort(418);
    }
}
