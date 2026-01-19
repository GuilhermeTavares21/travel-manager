<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected $code = 400;

    public function render()
    {
        return response()->json([
            'error' => $this->getMessage(),
        ], $this->code);
    }
}
