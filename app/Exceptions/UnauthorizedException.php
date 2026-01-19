<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    protected $code = 403;

    public function render()
    {
        return response()->json([
            'error' => $this->getMessage(),
        ], $this->code);
    }
}
