<?php
// src/Entity/Livraison.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Livraison
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(name: 'id')]
    private ?int $idLivr = null;

    #[ORM\Column(length: 100)]
    private string $nomLivr;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $choixLivr = null; // standard, express...

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $delaiLivr = null; // jours

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, nullable: true)]
    private ?string $fraisLivr = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateLivr = null;

    public function getIdLivr(): ?int { return $this->idLivr; }
    public function getNomLivr(): string { return $this->nomLivr; }
    public function setNomLivr(string $v): static { $this->nomLivr = $v; return $this; }
    public function getChoixLivr(): ?string { return $this->choixLivr; }
    public function setChoixLivr(?string $v): static { $this->choixLivr = $v; return $this; }
    public function getDelaiLivr(): ?int { return $this->delaiLivr; }
    public function setDelaiLivr(?int $v): static { $this->delaiLivr = $v; return $this; }
    public function getFraisLivr(): ?string { return $this->fraisLivr; }
    public function setFraisLivr(?string $v): static { $this->fraisLivr = $v; return $this; }
    public function getDateLivr(): ?\DateTimeInterface { return $this->dateLivr; }
    public function setDateLivr(?\DateTimeInterface $v): static { $this->dateLivr = $v; return $this; }
    public function __toString(): string { return $this->nomLivr; }
}