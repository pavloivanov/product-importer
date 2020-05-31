<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, length=100)
     */
    private $sku;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     */
    private $normalPrice;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $specialPrice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getNormalPrice(): ?float
    {
        return $this->normalPrice;
    }

    public function setNormalPrice(float $normalPrice): self
    {
        $this->normalPrice = $normalPrice;

        return $this;
    }

    public function getSpecialPrice(): ?float
    {
        return $this->specialPrice;
    }

    public function setSpecialPrice(?float $specialPrice): self
    {
        $this->specialPrice = $specialPrice;

        return $this;
    }

    public function update(string $sku, string $description, float $normalPrice, ?float $specialPrice): self
    {
        $this->sku = $sku;
        $this->description = $description;
        $this->normalPrice = $normalPrice;
        $this->specialPrice = $specialPrice;

        return $this;
    }
}
