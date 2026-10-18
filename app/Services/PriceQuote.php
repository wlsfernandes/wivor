<?php

namespace App\Services;

/** The frozen price breakdown for a selection of photos from one event. */
final class PriceQuote
{
    public function __construct(
        public readonly int $photoCount,
        public readonly int $listUnitPriceCents,
        public readonly int $unitPriceCents,
        public readonly ?string $discountSource,
        public readonly float $discountPercentage,
        public readonly float $commissionPercentage,
        public readonly int $itemCommissionCents,
        public readonly int $itemAllocationCents,
    ) {
    }

    /** Return the order amount before any discount. */
    public function subtotalCents(): int
    {
        return $this->listUnitPriceCents * $this->photoCount;
    }

    /** Return the order amount the customer pays. */
    public function totalCents(): int
    {
        return $this->unitPriceCents * $this->photoCount;
    }

    /** Return the total discount across every photo. */
    public function discountCents(): int
    {
        return $this->subtotalCents() - $this->totalCents();
    }

    public function hasDiscount(): bool
    {
        return $this->discountSource !== null;
    }
}
