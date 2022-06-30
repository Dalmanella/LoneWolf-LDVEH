<?php

namespace App\Entity;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BackpackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @ORM\Entity(repositoryClass=BackpackRepository::class)
 */
class Backpack
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Hero::class, inversedBy="objetId", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $heroId;

    /**
     * @ORM\ManyToMany(targetEntity=Objet::class)
     */
    private $objetId;

    public function __construct()
    {
        $this->objetId = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    
    public function getHeroId(): ?Hero
    {
        return $this->heroId;
    }

    public function setHeroId(Hero $heroId): self
    {
        $this->heroId = $heroId;

        return $this;
    }

    /**
     * @return Collection<int, Objet>
     */
    public function getObjetId(): Collection
    {
        return $this->objetId;
    }

    public function addObjetId(Objet $objetId): self
    {
        if (!$this->objetId->contains($objetId)) {
            $this->objetId[] = $objetId;
        }

        return $this;
    }

    public function removeObjetId(Objet $objetId): self
    {
        $this->objetId->removeElement($objetId);

        return $this;
    }
}
