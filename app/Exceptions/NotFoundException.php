<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    protected $code = 404;

    public function render()
    {
        return response()->json([
            'error' => $this->getMessage(),
        ], $this->code);
    }
}
