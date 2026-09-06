<?php

namespace App\Services;

use RuntimeException;

class OllamaUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'ИИ-консультант временно недоступен', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
