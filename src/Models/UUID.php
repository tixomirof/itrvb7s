<?php

namespace ITRvB\Models;

use ITRvB\Exceptions\InvalidUUIDException;
use JsonSerializable;

class UUID implements JsonSerializable
{
    public function __construct(
        private readonly string $uuid,
    )
    {
        if (!uuid_is_valid($uuid)) {
            throw new InvalidUUIDException("Malformed UUID: $uuid");
        }
    }

    public function __toString()
    {
        return $this->uuid;
    }

    public static function random() : self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }

    public function jsonSerialize() : string
    {
        return $this->__tostring();
    }
}