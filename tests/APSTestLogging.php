<?php

namespace Tests;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class APSTestLogging implements LoggerInterface
{
    use LoggerTrait;

    private array $logData;

    public function __construct()
    {
        $this->logData = [];
    }

    /**
     * The test log function
     *
     * @param mixed $level
     * @param \Stringable|string $message
     * @param array $context
     *
     * @return void
     */
    public function log(mixed $level, \Stringable|string $message, array $context = []): void
    {
        if (!isset($this->logData[$level])) {
            $this->logData[$level] = [];
        }

        $this->logData[$level][] = $message;
    }

    /**
     * @param mixed|null $level
     *
     * @return array
     */
    public function getLogData(mixed $level = null): array
    {
        if (null !== $level) {
            return $this->logData[$level] ?? [];
        }

        return $this->logData;
    }

    /**
     * @param mixed|null $level
     *
     * @return void
     */
    public function clearLogData(mixed $level = null): void
    {
        if (null !== $level) {
            $this->logData[$level] = [];
        }

        $this->logData = [];
    }

    /**
     * @param string $message
     * @param mixed|null $level
     *
     * @return bool
     */
    public function isMessageInLogData(string $message, mixed $level = null): bool
    {
        if (null !== $level) {
            return in_array($message, $this->logData[$level] ?? []);
        }

        return in_array($message, $this->logData);
    }

    /**
     * @param string $message
     * @param mixed|null $level
     *
     * @return bool
     */
    public function isMessageStartInLogData(string $message, mixed $level = null): bool
    {
        if (null !== $level) {
            foreach ($this->logData[$level] ?? [] as $index => $value) {
                if (str_contains($value, $message)) {
                    return true;
                }
            }

            return false;
        }

        foreach ($this->logData as $index => $value) {
            if ($this->isMessageStartInLogData($message, $index)) {
                return true;
            }
        }

        return false;
    }
}