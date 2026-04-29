<?php
// src/Entity/TypeClient.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class TypeClient
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(name: 'id')]
    private ?int $idTypeCli = null;

    #[ORM\Column(length: 100)]
    private string $nomTypeClient;

    #[ORM\OneToMany(mappedBy: 'typeClient', targetEntity: Client::class)]
    private Collection $clients;

    public function __construct() { $this->clients = new ArrayCollection(); }

    public function getIdTypeCli(): ?int { return $this->idTypeCli; }
    public function getNomTypeClient(): string { return $this->nomTypeClient; }
    public function setNomTypeClient(string $v): static { $this->nomTypeClient = $v; return $this; }
}