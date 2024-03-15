<?php

namespace District5\ValidatorGroup\Handler;

class JSON implements HandlerInterface
{
    private array $source;

    public function __construct($data, bool $requiresDecoding)
    {
        if (true === $requiresDecoding) {
            $this->source = json_decode($data, true);
        } else {
            $this->source = $data;
        }
    }

    public function hasValue(string $name): bool
    {
        return isset($this->source[$name]);
    }

    public function getValue(string $name): mixed
    {
        return $this->source[$name] ?? null;
    }
}
