<?php

namespace Dpeuscher\BahnSearch\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @category  lib-bahn-search
 * @copyright Copyright (c) 2018 Dominik Peuscher
 * @ORM\Entity(repositoryClass="Dpeuscher\BahnSearch\Repository\ConnectionRepository")
 */
class Connection
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
    private $fromLocationId;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $toLocation;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $toLocationId;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTimeInterface
     */
    private $fromTime;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTimeInterface
     */
    private $toTime;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $duration;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $changes;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @var float
     */
    private $minimumFare;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $minimumFareCabinClass;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $minimumFareText;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @var float
     */
    private $cheapFare;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @var float
     */
    private $flexFare;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $products;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $cheapFareText;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $cheapFareCabinClass;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $flexFareText;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $flexFareCabinClass;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTimeInterface
     */
    private $resultTime;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFromLocation(): string
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
    public function getToLocation(): string
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
     * @return string
     */
    public function getFromLocationId(): string
    {
        return $this->fromLocationId;
    }

    /**
     * @param string $fromLocationId
     */
    public function setFromLocationId(string $fromLocationId): void
    {
        $this->fromLocationId = $fromLocationId;
    }

    /**
     * @return string
     */
    public function getToLocationId(): string
    {
        return $this->toLocationId;
    }

    /**
     * @param string $toLocationId
     */
    public function setToLocationId(string $toLocationId): void
    {
        $this->toLocationId = $toLocationId;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getResultTime(): \DateTimeInterface
    {
        return $this->resultTime;
    }

    /**
     * @param \DateTimeInterface $resultTime
     */
    public function setResultTime(\DateTimeInterface $resultTime): void
    {
        $this->resultTime = $resultTime;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getFromTime(): \DateTimeInterface
    {
        return $this->fromTime;
    }

    /**
     * @param \DateTimeInterface $fromTime
     */
    public function setFromTime(\DateTimeInterface $fromTime): void
    {
        $this->fromTime = $fromTime;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getToTime(): \DateTimeInterface
    {
        return $this->toTime;
    }

    /**
     * @param \DateTimeInterface $toTime
     */
    public function setToTime(\DateTimeInterface $toTime): void
    {
        $this->toTime = $toTime;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return int
     */
    public function getChanges(): int
    {
        return $this->changes;
    }

    /**
     * @param int $changes
     */
    public function setChanges(int $changes): void
    {
        $this->changes = $changes;
    }

    /**
     * @return float
     */
    public function getMinimumFare(): float
    {
        return $this->minimumFare;
    }

    /**
     * @param float $minimumFare
     */
    public function setMinimumFare(?float $minimumFare): void
    {
        $this->minimumFare = $minimumFare;
    }

    /**
     * @return string
     */
    public function getMinimumFareCabinClass(): string
    {
        return $this->minimumFareCabinClass;
    }

    /**
     * @param string $minimumFareCabinClass
     */
    public function setMinimumFareCabinClass(?string $minimumFareCabinClass): void
    {
        $this->minimumFareCabinClass = $minimumFareCabinClass;
    }

    /**
     * @return string
     */
    public function getMinimumFareText(): string
    {
        return $this->minimumFareText;
    }

    /**
     * @param string $minimumFareText
     */
    public function setMinimumFareText(?string $minimumFareText): void
    {
        $this->minimumFareText = $minimumFareText;
    }


    /**
     * @return float
     */
    public function getCheapFare(): float
    {
        return $this->cheapFare;
    }

    /**
     * @param float $cheapFare
     */
    public function setCheapFare(?float $cheapFare): void
    {
        $this->cheapFare = $cheapFare;
    }

    /**
     * @return float
     */
    public function getFlexFare(): float
    {
        return $this->flexFare;
    }

    /**
     * @param float $flexFare
     */
    public function setFlexFare(?float $flexFare): void
    {
        $this->flexFare = $flexFare;
    }

    /**
     * @return string
     */
    public function getProducts(): string
    {
        return $this->products;
    }

    /**
     * @param string $products
     */
    public function setProducts(string $products): void
    {
        $this->products = $products;
    }

    /**
     * @return string
     */
    public function getCheapFareText(): string
    {
        return $this->cheapFareText;
    }

    /**
     * @param string $cheapFareText
     */
    public function setCheapFareText(string $cheapFareText): void
    {
        $this->cheapFareText = $cheapFareText;
    }

    /**
     * @return string
     */
    public function getFlexFareText(): string
    {
        return $this->flexFareText;
    }

    /**
     * @param string $flexFareText
     */
    public function setFlexFareText(string $flexFareText): void
    {
        $this->flexFareText = $flexFareText;
    }

    /**
     * @return string
     */
    public function getCheapFareCabinClass(): string
    {
        return $this->cheapFareCabinClass;
    }

    /**
     * @param string $cheapFareCabinClass
     */
    public function setCheapFareCabinClass(?string $cheapFareCabinClass): void
    {
        $this->cheapFareCabinClass = $cheapFareCabinClass;
    }

    /**
     * @return string
     */
    public function getFlexFareCabinClass(): string
    {
        return $this->flexFareCabinClass;
    }

    /**
     * @param string $flexFareCabinClass
     */
    public function setFlexFareCabinClass(?string $flexFareCabinClass): void
    {
        $this->flexFareCabinClass = $flexFareCabinClass;
    }

    public function __toString(): string
    {
        return $this->getFromLocation() . ' (' . $this->getFromTime()->format('d.m.Y H:i') . ') -> ' .
            $this->getToLocation() . ' (' . $this->getToTime()->format('d.m.Y H:i') . ') ' .
            '[' . $this->getDuration() . ' Minuten] ' . $this->getChanges() . ' Umstiege - ' .
            'Preis: ' . number_format($this->getMinimumFare(), 2, ',', '.') . ' € ' .
            '(' . $this->getMinimumFareCabinClass() . '. Kl.) [' . $this->getProducts() . '] ' .
            $this->getMinimumFareText();
    }

}
