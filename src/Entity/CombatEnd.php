<?php

namespace App\Entity;

use App\Repository\CombatEndRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CombatEndRepository::class)
 */
class CombatEnd
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $endL;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $endE;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tour;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $potionS;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $startEnd;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEndL(): ?int
    {
        return $this->endL;
    }

    public function setEndL(?int $endL): self
    {
        $this->endL = $endL;

        return $this;
    }

    public function getEndE(): ?int
    {
        return $this->endE;
    }

    public function setEndE(?int $endE): self
    {
        $this->endE = $endE;

        return $this;
    }
    
    public function getTour(): ?int
    {
        return $this->tour;
    }

    public function setTour(?int $tour): self
    {
        $this->tour = $tour;

        return $this;
    }

    public function isPotionS(): ?bool
    {
        return $this->potionS;
    }

    public function setPotionS(?bool $potionS): self
    {
        $this->potionS = $potionS;

        return $this;
    }

    public function getStartEnd(): ?int
    {
        return $this->startEnd;
    }

    public function setStartEnd(?int $startEnd): self
    {
        $this->startEnd = $startEnd;

        return $this;
    }
}
