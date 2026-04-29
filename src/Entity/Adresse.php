<?php
// src/Entity/Adresse.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Adresse
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(name: 'id')]
    private ?int $idAddr = null;

    #[ORM\Column(length: 50)]
    private string $typeAddr; // livraison, facturation

    #[ORM\Column(length: 200)]
    private string $rueAddr;

    #[ORM\Column(length: 100)]
    private string $villeAddr;

    #[ORM\Column(length: 10)]
    private string $cpAddr;

    #[ORM\Column(length: 100)]
    private string $paysAddr;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'adresses')]
    #[ORM\JoinColumn(nullable: false)]
    private Client $client;

    public function getIdAddr(): ?int { return $this->idAddr; }
    public function getTypeAddr(): string { return $this->typeAddr; }
    public function setTypeAddr(string $v): static { $this->typeAddr = $v; return $this; }
    public function getRueAddr(): string { return $this->rueAddr; }
    public function setRueAddr(string $v): static { $this->rueAddr = $v; return $this; }
    public function getVilleAddr(): string { return $this->villeAddr; }
    public function setVilleAddr(string $v): static { $this->villeAddr = $v; return $this; }
    public function getCpAddr(): string { return $this->cpAddr; }
    public function setCpAddr(string $v): static { $this->cpAddr = $v; return $this; }
    public function getPaysAddr(): string { return $this->paysAddr; }
    public function setPaysAddr(string $v): static { $this->paysAddr = $v; return $this; }
    public function getClient(): Client { return $this->client; }
    public function setClient(Client $v): static { $this->client = $v; return $this; }
    public function __toString(): string { return $this->rueAddr . ', ' . $this->cpAddr . ' ' . $this->villeAddr; }
}