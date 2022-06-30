<?php

namespace App\Entity;

use App\Repository\HeroRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HeroRepository::class)
 */
class Hero
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $combatSkill;

    /**
     * @ORM\Column(type="integer")
     */
    private $endurance;

    /**
     * @ORM\Column(type="integer")
     */
    private $endMax;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $gold;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $page;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $weapon ;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $specialBag ;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $backpack ;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="heroes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $kaiSix;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $kaiTrack;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $kaiHeal;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $kaiWeapon;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $kaiMShield;

    /**
     * @ORM\Column(type="boolean")
     */
    private $kaiMBlast;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $kaiAnimal;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $kaiMoM;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $kaiCamou;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $kaiHunt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity=Backpack::class, mappedBy="heroId", cascade={"persist", "remove"})
     */
    private $backpackId;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $pagesVues = [];

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $lunchClick;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $GoldTake;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isItem;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEndMax(): ?int
    {
        return $this->endMax;
    }

    public function setEndMax(int $endMax): self
    {
        $this->endMax = $endMax;

        return $this;
    }

    public function getGold(): ?int
    {
        return $this->gold;
    }

    public function setGold(?int $gold): self
    {
        $this->gold = $gold;

        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getWeapon(): ?int
    {
        return $this->weapon;
    }

    public function setWeapon(?int $weapon): self
    {
        $this->weapon = $weapon;

        return $this;
    }

    public function getSpecialBag(): ?int
    {
        return $this->specialBag;
    }

    public function setSpecialBag(?int $specialBag): self
    {
        $this->specialBag = $specialBag;

        return $this;
    }

    public function getBackpack(): ?int
    {
        return $this->backpack;
    }

    public function setBackpack(?int $backpack): self
    {
        $this->backpack = $backpack;

        return $this;
    }

    public function getUserId(): ?Utilisateur
    {
        return $this->userId;
    }

    public function setUserId(?Utilisateur $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function isKaiSix(): ?bool
    {
        return $this->kaiSix;
    }

    public function setKaiSix(?bool $kaiSix): self
    {
        $this->kaiSix = $kaiSix;

        return $this;
    }

    public function isKaiTrack(): ?bool
    {
        return $this->kaiTrack;
    }

    public function setKaiTrack(?bool $kaiTrack): self
    {
        $this->kaiTrack = $kaiTrack;

        return $this;
    }

    public function isKaiHeal(): ?bool
    {
        return $this->kaiHeal;
    }

    public function setKaiHeal(?bool $kaiHeal): self
    {
        $this->kaiHeal = $kaiHeal;

        return $this;
    }

    public function isKaiWeapon(): ?bool
    {
        return $this->kaiWeapon;
    }

    public function setKaiWeapon(?bool $kaiWeapon): self
    {
        $this->kaiWeapon = $kaiWeapon;

        return $this;
    }

    public function isKaiMShield(): ?bool
    {
        return $this->kaiMShield;
    }

    public function setKaiMShield(?bool $kaiMShield): self
    {
        $this->kaiMShield = $kaiMShield;

        return $this;
    }

    public function isKaiMBlast(): ?bool
    {
        return $this->kaiMBlast;
    }

    public function setKaiMBlast(bool $kaiMBlast): self
    {
        $this->kaiMBlast = $kaiMBlast;

        return $this;
    }

    public function isKaiAnimal(): ?bool
    {
        return $this->kaiAnimal;
    }

    public function setKaiAnimal(?bool $kaiAnimal): self
    {
        $this->kaiAnimal = $kaiAnimal;

        return $this;
    }

    public function isKaiMoM(): ?bool
    {
        return $this->kaiMoM;
    }

    public function setKaiMoM(?bool $kaiMoM): self
    {
        $this->kaiMoM = $kaiMoM;

        return $this;
    }

    public function isKaiCamou(): ?bool
    {
        return $this->kaiCamou;
    }

    public function setKaiCamou(?bool $kaiCamou): self
    {
        $this->kaiCamou = $kaiCamou;

        return $this;
    }

    public function isKaiHunt(): ?bool
    {
        return $this->kaiHunt;
    }

    public function setKaiHunt(?bool $kaiHunt): self
    {
        $this->kaiHunt = $kaiHunt;

        return $this;
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

    public function getbackpackId(): ?Backpack
    {
        return $this->backpackId;
    }

    public function setbackpackId(Backpack $backpackId): self
    {
        // set the owning side of the relation if necessary
        if ($backpackId->getHeroId() !== $this) {
            $backpackId->setHeroId($this);
        }

        $this->backpackId = $backpackId;

        return $this;
    }

    public function getPagesVues(): ?array
    {
        return $this->pagesVues;
    }

    public function setPagesVues(?array $pagesVues): self
    {
        $this->pagesVues = $pagesVues;

        return $this;
    }

    public function isLunchClick(): ?bool
    {
        return $this->lunchClick;
    }

    public function setLunchClick(?bool $lunchClick): self
    {
        $this->lunchClick = $lunchClick;

        return $this;
    }

    public function isGoldTake(): ?bool
    {
        return $this->GoldTake;
    }

    public function setGoldTake(?bool $GoldTake): self
    {
        $this->GoldTake = $GoldTake;

        return $this;
    }

    public function isIsItem(): ?bool
    {
        return $this->isItem;
    }

    public function setIsItem(?bool $isItem): self
    {
        $this->isItem = $isItem;

        return $this;
    }
    
}
