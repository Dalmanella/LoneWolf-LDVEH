<?php

namespace App\Entity;

use App\Repository\ObjetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ObjetRepository::class)
 */
class Objet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $objName;

    /**
     * @ORM\Column(type="text")
     */
    private $objDescription;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $wTag;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $iTag;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $effect;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $objType;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $place;

    /**
     * @ORM\ManyToOne(targetEntity=Illustration::class, inversedBy="objets")
     */
    private $idIllu;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjName(): ?string
    {
        return $this->objName;
    }

    public function setObjName(string $objName): self
    {
        $this->objName = $objName;

        return $this;
    }

    public function getObjDescription(): ?string
    {
        return $this->objDescription;
    }

    public function setObjDescription(string $objDescription): self
    {
        $this->objDescription = $objDescription;

        return $this;
    }

    public function getWTag(): ?int
    {
        return $this->wTag;
    }

    public function setWTag(?int $wTag): self
    {
        $this->wTag = $wTag;

        return $this;
    }

    public function getITag(): ?int
    {
        return $this->iTag;
    }

    public function setITag(?int $iTag): self
    {
        $this->iTag = $iTag;

        return $this;
    }

    public function getEffect(): ?int
    {
        return $this->effect;
    }

    public function setEffect(?int $effect): self
    {
        $this->effect = $effect;

        return $this;
    }

    public function getObjType(): ?string
    {
        return $this->objType;
    }

    public function setObjType(string $objType): self
    {
        $this->objType = $objType;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getIdIllu(): ?Illustration
    {
        return $this->idIllu;
    }

    public function setIdIllu(?Illustration $idIllu): self
    {
        $this->idIllu = $idIllu;

        return $this;
    }
}
