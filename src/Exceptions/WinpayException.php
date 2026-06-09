<?php

namespace Winpay\Core\Exceptions;

class WinpayException extends \Exception
{
    protected ?array $errors = null;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, ?array $errors = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
