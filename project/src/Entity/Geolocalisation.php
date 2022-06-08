<?php

namespace App\Entity;

use App\Repository\GeolocalisationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GeolocalisationRepository::class)
 */
class Geolocalisation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Localisation::class, inversedBy="geolocalisations")
     */
    private $localisation;

    /**
     * @ORM\Column(type="float")
     */
    private $lat;

    /**
     * @ORM\Column(type="float")
     */
    private $lng;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocalisation(): ?localisation
    {
        return $this->localisation;
    }

    public function setLocalisation(?localisation $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?float
    {
        return $this->lng;
    }

    public function setLng(float $lng): self
    {
        $this->lng = $lng;

        return $this;
    }
}
