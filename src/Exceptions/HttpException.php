<?php

namespace Winpay\Core\Exceptions;

class HttpException extends WinpayException
{
    private ?int $statusCode;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, ?int $statusCode = null, ?array $errors = null)
    {
        parent::__construct($message, $code, $previous, $errors);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
