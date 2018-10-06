<?php

namespace Dpeuscher\BahnSearch\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @category  lib-bahn-search
 * @copyright Copyright (c) 2018 Dominik Peuscher
 * @ORM\Entity(repositoryClass="Dpeuscher\BahnSearch\Repository\RoundTripRepository")
 */
class RoundTrip
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $fromLocation;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $toLocation;

    /**
     * @ORM\Column(type="datetime", length=255)
     * @var \DateTime
     */
    private $fromDepDateTime;

    /**
     * @ORM\Column(type="datetime", length=255)
     * @var \DateTime
     */
    private $toDepDateTime;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $programId;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @var float
     */
    private $fullPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @var float
     */
    private $fullPriceFirstClass;

    /**
     * @var Connection
     * @ORM\ManyToOne(targetEntity="Dpeuscher\BahnSearch\Entity\Connection", cascade={"persist"})
     */
    private $cheapestFirstLeg;

    /**
     * @var Connection
     * @ORM\ManyToOne(targetEntity="Dpeuscher\BahnSearch\Entity\Connection", cascade={"persist"})
     */
    private $cheapestFirstLegFirstClass;

    /**
     * @var Connection
     * @ORM\ManyToOne(targetEntity="Dpeuscher\BahnSearch\Entity\Connection", cascade={"persist"})
     */
    private $cheapestLastLeg;

    /**
     * @var Connection
     * @ORM\ManyToOne(targetEntity="Dpeuscher\BahnSearch\Entity\Connection", cascade={"persist"})
     */
    private $cheapestLastLegFirstClass;

    // @codeCoverageIgnoreStart

    /**
     * @return string
     */
    public function getFromLocation(): ?string
    {
        return $this->fromLocation;
    }

    /**
     * @param string $fromLocation
     */
    public function setFromLocation(string $fromLocation): void
    {
        $this->fromLocation = $fromLocation;
    }

    /**
     * @return string
     */
    public function getToLocation(): ?string
    {
        return $this->toLocation;
    }

    /**
     * @param string $toLocation
     */
    public function setToLocation(string $toLocation): void
    {
        $this->toLocation = $toLocation;
    }

    /**
     * @return \DateTime
     */
    public function getFromDepDateTime(): ?\DateTime
    {
        return $this->fromDepDateTime;
    }

    /**
     * @param \DateTime $fromDepDateTime
     */
    public function setFromDepDateTime(\DateTime $fromDepDateTime): void
    {
        $this->fromDepDateTime = $fromDepDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getToDepDateTime(): ?\DateTime
    {
        return $this->toDepDateTime;
    }

    /**
     * @param \DateTime $toDepDateTime
     */
    public function setToDepDateTime(\DateTime $toDepDateTime): void
    {
        $this->toDepDateTime = $toDepDateTime;
    }

    /**
     * @return string
     */
    public function getProgramId(): ?string
    {
        return $this->programId;
    }

    /**
     * @param string $programId
     */
    public function setProgramId(string $programId): void
    {
        $this->programId = $programId;
    }

    /**
     * @return float
     */
    public function getFullPrice(): ?float
    {
        return $this->fullPrice;
    }

    /**
     * @param float $fullPrice
     */
    public function setFullPrice(?float $fullPrice): void
    {
        $this->fullPrice = $fullPrice;
    }

    /**
     * @return float
     */
    public function getFullPriceFirstClass(): ?float
    {
        return $this->fullPriceFirstClass;
    }

    /**
     * @param float $fullPriceFirstClass
     */
    public function setFullPriceFirstClass(?float $fullPriceFirstClass): void
    {
        $this->fullPriceFirstClass = $fullPriceFirstClass;
    }

    /**
     * @return Connection
     */
    public function getCheapestFirstLeg(): ?Connection
    {
        return $this->cheapestFirstLeg;
    }

    /**
     * @param Connection $cheapestFirstLeg
     */
    public function setCheapestFirstLeg(?Connection $cheapestFirstLeg): void
    {
        $this->cheapestFirstLeg = $cheapestFirstLeg;
    }

    /**
     * @return Connection
     */
    public function getCheapestFirstLegFirstClass(): ?Connection
    {
        return $this->cheapestFirstLegFirstClass;
    }

    /**
     * @param Connection $cheapestFirstLegFirstClass
     */
    public function setCheapestFirstLegFirstClass(?Connection $cheapestFirstLegFirstClass): void
    {
        $this->cheapestFirstLegFirstClass = $cheapestFirstLegFirstClass;
    }

    /**
     * @return Connection
     */
    public function getCheapestLastLeg(): ?Connection
    {
        return $this->cheapestLastLeg;
    }

    /**
     * @param Connection $cheapestLastLeg
     */
    public function setCheapestLastLeg(?Connection $cheapestLastLeg): void
    {
        $this->cheapestLastLeg = $cheapestLastLeg;
    }

    /**
     * @return Connection
     */
    public function getCheapestLastLegFirstClass(): ?Connection
    {
        return $this->cheapestLastLegFirstClass;
    }

    /**
     * @param Connection $cheapestLastLegFirstClass
     */
    public function setCheapestLastLegFirstClass(?Connection $cheapestLastLegFirstClass): void
    {
        $this->cheapestLastLegFirstClass = $cheapestLastLegFirstClass;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    // @codeCoverageIgnoreEnd

    public function __toString(): string
    {
        $string = '';
        if ($this->cheapestFirstLeg !== null) {
            $string .= 'Hinfahrt (' . $this->cheapestFirstLeg->getMinimumFareCabinClass() . '. Kl.): ' . "\n";
            $string .= \chr(9) . $this->cheapestFirstLeg . "\n";
            if ($this->cheapestFirstLeg !== $this->cheapestFirstLegFirstClass && $this->cheapestFirstLegFirstClass !== null) {
                $string .= 'Hinfahrt (' . $this->cheapestFirstLegFirstClass->getMinimumFareCabinClass() . '. Kl.): ' . "\n";
                $string .= \chr(9) . $this->cheapestFirstLegFirstClass . "\n";
            }
        }
        if ($this->cheapestLastLeg !== null) {
            $string .= 'Rückfahrt (' . $this->cheapestLastLeg->getMinimumFareCabinClass() . '. Kl.): ' . "\n";
            $string .= \chr(9) . $this->cheapestLastLeg . "\n";
            if ($this->cheapestLastLeg !== $this->cheapestLastLegFirstClass && $this->cheapestLastLegFirstClass !== null) {
                $string .= 'Rückfahrt (' . $this->cheapestLastLegFirstClass->getMinimumFareCabinClass() . '. Kl.): ' . "\n";
                $string .= \chr(9) . $this->cheapestLastLegFirstClass . "\n";
            }
        }
        if ($this->fullPriceFirstClass !== null) {
            $string .= 'Preis (1. Kl.): ' .
                number_format($this->fullPriceFirstClass, 2, ',', '.') . ' €' . "\n";
        }
        if ($this->fullPrice !== null && $this->fullPrice < $this->fullPriceFirstClass) {
            $string .= 'Preis (2. Kl.): ' .
                number_format($this->fullPrice, 2, ',', '.') . ' €' . "\n";
        }
        return $string;
    }
}
