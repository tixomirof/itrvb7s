<?php

namespace ITRvB\Models;

use ITRvB\Models\UUID;
use DateTimeImmutable;
use DateInterval;
use DateTimeInterface;

class AuthToken
{
    public function __construct(
        private string $token,
        private UUID $userUuid,
        private DateTimeImmutable $expiresOn
    )
    {
    }

    public function getToken() : string
    {
        return $this->token;
    }

    public function getUserUuid() : UUID
    {
        return $this->userUuid;
    }

    public function getExpirationDate() : DateTimeImmutable
    {
        return $this->expiresOn;
    }

    public function getAtomExpirationDate() : string
    {
        return $this->getExpirationDate()->format(DateTimeInterface::ATOM);
    }

    public function hasExpired() : bool
    {
        $currentTime = new DateTimeImmutable();
        return $currentTime > $this->getExpirationDate();
    }

    public static function getDefaultExpirationTime() : DateInterval
    {
        return DateInterval::createFromDateString('15 minutes');
    }
}