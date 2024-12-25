<?php

namespace ITRvB\UnitTests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use ITRvB\Exceptions\InvalidUUIDException;
use ITRvB\Models\UUID;

class UUIDTest extends TestCase
{
    #[DoesNotPerformAssertions]
    #[TestWith(["58bac195-951e-ffff-61bb-a517b2e84100"])]
    #[TestWith(["4a712205-d463-4b90-bc65-be98bb577b2e"])]
    #[TestWith(["00000000-0000-0000-0000-000000000000"])]
    #[TestWith(["ffffffff-eeee-dddd-cccc-aabababababb"])]
    public function testValidUuid(string $uuidString) : void
    {
        $uuid = new UUID($uuidString);
    }

    #[TestWith([""])]
    #[TestWith(["invalid UUID"])]
    #[TestWith(["4a712205-d463-4b90-bc65-be98bb577b2eR"])]
    #[TestWith(["4a712205-d463-4b90-bc65-be98bb577b2r"])]
    #[TestWith(["90000000-9000-9000-9000-9000000000000"])]
    #[TestWith(["900000000-9000-9000-9000-900000000000"])]
    #[TestWith(["a894b9a2-41g7-687b-9114-a8b1ff187ae"])]
    #[TestWith(["bbe81a90-4885-aa11-58b8d85c92e-3031"])]
    #[TestWith(["а0000000-a000-a000-a000-a00000000000"])] // first 'a' is replaced with cyrillic symbol
    public function testInvalidUuid(string $uuidString) : void
    {
        $this->expectException(InvalidUUIDException::class);
        $uuid = new UUID($uuidString);
    }

    #[DoesNotPerformAssertions]
    public function testRandomUuid() : void
    {
        $randomUuid = UUID::random();
        $sameUuid = new UUID("$randomUuid");
    }
}