<?php

namespace App\Entity;

use App\Repository\AnnonceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnnonceRepository::class)]
class Annonce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $poste = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu_de_travail = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $id_candidat_valid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $id_candidat_invalid = null;

    #[ORM\Column(length: 255)]
    private ?string $id_recruteur_create = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $id_validaton_consultant = null;

    #[ORM\Column]
    private ?bool $isValid = null;

    public function __construct()
    {
        $this->createAt = new \DateTimeImmutable();
        $this->isValid = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(string $poste): self
    {
        $this->poste = $poste;

        return $this;
    }

    public function getLieuDeTravail(): ?string
    {
        return $this->lieu_de_travail;
    }

    public function setLieuDeTravail(string $lieu_de_travail): self
    {
        $this->lieu_de_travail = $lieu_de_travail;

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

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getIdCandidatValid(): ?string
    {
        return $this->id_candidat_valid;
    }

    public function setIdCandidatValid(?string $id_candidat_valid): self
    {
        $this->id_candidat_valid = $id_candidat_valid;

        return $this;
    }

    public function getIdCandidatInvalid(): ?string
    {
        return $this->id_candidat_invalid;
    }

    public function setIdCandidatInvalid(?string $id_candidat_invalid): self
    {
        $this->id_candidat_invalid = $id_candidat_invalid;

        return $this;
    }

    public function getIdRecruteurCreate(): ?string
    {
        return $this->id_recruteur_create;
    }

    public function setIdRecruteurCreate(string $id_recruteur_create): self
    {
        $this->id_recruteur_create = $id_recruteur_create;

        return $this;
    }

    public function getIdValidatonConsultant(): ?string
    {
        return $this->id_validaton_consultant;
    }

    public function setIdValidatonConsultant(string $id_validaton_consultant): self
    {
        $this->id_validaton_consultant = $id_validaton_consultant;

        return $this;
    }

            /**
     * Get the value of isValid
     */ 
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * Set the value of isValid
     *
     * @return  self
     */ 
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }
}
