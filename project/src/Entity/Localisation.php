<?php

namespace App\Entity;

use App\Repository\LocalisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LocalisationRepository::class)
 */
class Localisation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $zipcode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @ORM\OneToMany(targetEntity=Geolocalisation::class, mappedBy="localisation")
     */
    private $geolocalisations;

    /**
     * @ORM\ManyToOne(targetEntity=shop::class, inversedBy="localisations")
     */
    private $shop;

    public function __construct()
    {
        $this->geolocalisations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection<int, Geolocalisation>
     */
    public function getGeolocalisations(): Collection
    {
        return $this->geolocalisations;
    }

    public function addGeolocalisation(Geolocalisation $geolocalisation): self
    {
        if (!$this->geolocalisations->contains($geolocalisation)) {
            $this->geolocalisations[] = $geolocalisation;
            $geolocalisation->setLocalisation($this);
        }

        return $this;
    }

    public function removeGeolocalisation(Geolocalisation $geolocalisation): self
    {
        if ($this->geolocalisations->removeElement($geolocalisation)) {
            // set the owning side to null (unless already changed)
            if ($geolocalisation->getLocalisation() === $this) {
                $geolocalisation->setLocalisation(null);
            }
        }

        return $this;
    }

    public function getShop(): ?shop
    {
        return $this->shop;
    }

    public function setShop(?shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }
}
