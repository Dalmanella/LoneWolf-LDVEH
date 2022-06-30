<?php

namespace App\Entity;

use App\Repository\EnnemyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EnnemyRepository::class)
 */
class Ennemy
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $combatSkill;

    /**
     * @ORM\Column(type="integer")
     */
    private $endurance;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $mindforce;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $mindshield;

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

    public function getCombatSkill(): ?int
    {
        return $this->combatSkill;
    }

    public function setCombatSkill(int $combatSkill): self
    {
        $this->combatSkill = $combatSkill;

        return $this;
    }

    public function getEndurance(): ?int
    {
        return $this->endurance;
    }

    public function setEndurance(int $endurance): self
    {
        $this->endurance = $endurance;

        return $this;
    }

    public function isMindforce(): ?bool
    {
        return $this->mindforce;
    }

    public function setMindforce(?bool $mindforce): self
    {
        $this->mindforce = $mindforce;

        return $this;
    }

    public function isMindshield(): ?bool
    {
        return $this->mindshield;
    }

    public function setMindshield(?bool $mindshield): self
    {
        $this->mindshield = $mindshield;

        return $this;
    }
}
