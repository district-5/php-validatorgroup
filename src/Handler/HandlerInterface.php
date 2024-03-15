<?php

namespace District5\ValidatorGroup\Handler;

interface HandlerInterface
{
    public function hasValue(string $name): bool;
    public function getValue(string $name): mixed;
}
