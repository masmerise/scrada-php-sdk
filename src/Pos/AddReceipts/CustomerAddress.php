<?php declare(strict_types=1);

namespace Scrada\Pos\AddReceipts;

use Scrada\Pos\Type\Primitive\City;
use Scrada\Pos\Type\Primitive\CountryCode;
use Scrada\Pos\Type\Primitive\CountrySubentity;
use Scrada\Pos\Type\Primitive\Street;
use Scrada\Pos\Type\Primitive\StreetBox;
use Scrada\Pos\Type\Primitive\StreetNumber;
use Scrada\Pos\Type\Primitive\ZipCode;

final readonly class CustomerAddress
{
    private function __construct(
        public ?Street $street = null,
        public ?StreetNumber $streetNumber = null,
        public ?StreetBox $streetBox = null,
        public ?City $city = null,
        public ?ZipCode $zipCode = null,
        public ?CountrySubentity $countrySubentity = null,
        public ?CountryCode $countryCode = null,
    ) {}

    public static function parameters(
        ?Street $street = null,
        ?StreetNumber $streetNumber = null,
        ?StreetBox $streetBox = null,
        ?City $city = null,
        ?ZipCode $zipCode = null,
        ?CountrySubentity $countrySubentity = null,
        ?CountryCode $countryCode = null,
    ): self {
        return new self(
            street: $street,
            streetNumber: $streetNumber,
            streetBox: $streetBox,
            city: $city,
            zipCode: $zipCode,
            countrySubentity: $countrySubentity,
            countryCode: $countryCode,
        );
    }

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return [
            'street' => $this->street?->toString(),
            'streetNumber' => $this->streetNumber?->toString(),
            'streetBox' => $this->streetBox?->toString(),
            'city' => $this->city?->toString(),
            'zipCode' => $this->zipCode?->toString(),
            'countrySubentity' => $this->countrySubentity?->toString(),
            'countryCode' => $this->countryCode?->toString(),
        ];
    }
}
