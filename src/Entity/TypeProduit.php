<?php
// src/Entity/TypeProduit.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class TypeProduit
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(name: 'id')]
    private ?int $idTypeProd = null;

    #[ORM\Column(length: 100)]
    private string $nomTypeProd;

    #[ORM\OneToMany(mappedBy: 'typeProduit', targetEntity: Produit::class)]
    private Collection $produits;

    public function __construct() { $this->produits = new ArrayCollection(); }

    public function getIdTypeProd(): ?int { return $this->idTypeProd; }
    public function getNomTypeProd(): string { return $this->nomTypeProd; }
    public function setNomTypeProd(string $v): static { $this->nomTypeProd = $v; return $this; }
    public function getProduits(): Collection { return $this->produits; }
    public function __toString(): string { return $this->nomTypeProd; }
}