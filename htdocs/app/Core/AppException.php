<?php

declare(strict_types=1);

namespace App\Core;

use Exception;
use Throwable;

class AppException extends Exception
{
    protected string $errorCode;
    protected int $httpStatus;
    protected ?array $fields;

    public function __construct(
        string $errorCode,
        string $message,
        int $httpStatus = 400,
        ?array $fields = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $httpStatus, $previous);
        $this->errorCode = $errorCode;
        $this->httpStatus = $httpStatus;
        $this->fields = $fields;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getFields(): ?array
    {
        return $this->fields;
    }
}
