<?php
// src/Entity/Paiement.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Paiement
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(name: 'id')]
    private ?int $idPay = null;

    #[ORM\Column(length: 100)]
    private string $libellePay; // carte, virement, paypal...

    public function getIdPay(): ?int { return $this->idPay; }
    public function getLibellePay(): string { return $this->libellePay; }
    public function setLibellePay(string $v): static { $this->libellePay = $v; return $this; }
    public function __toString(): string { return $this->libellePay; }
}