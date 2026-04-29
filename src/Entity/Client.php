<?php
// src/Entity/Client.php
namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(name: 'client')]
class Client implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(name: 'id')]
    private ?int $idCli = null;

    #[ORM\Column(length: 100)]
    private string $nomCli;

    #[ORM\Column(length: 100)]
    private string $prenomCli;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateNaissanceCli = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $mailCli;

    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telCli = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\ManyToOne(targetEntity: TypeClient::class, inversedBy: 'clients')]
    private ?TypeClient $typeClient = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Adresse::class, cascade: ['persist', 'remove'])]
    private Collection $adresses;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Commande::class)]
    private Collection $commandes;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Avis::class, cascade: ['persist', 'remove'])]
    private Collection $avis;

    #[ORM\ManyToMany(targetEntity: Produit::class)]
    #[ORM\JoinTable(name: 'client_favoris')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'NO ACTION')]
    #[ORM\InverseJoinColumn(name: 'produit_id', referencedColumnName: 'id', onDelete: 'NO ACTION')]
    private Collection $favoris;

    public function __construct()
    {
        $this->adresses  = new ArrayCollection();
        $this->commandes = new ArrayCollection();
        $this->avis      = new ArrayCollection();
        $this->favoris   = new ArrayCollection();
    }

    // UserInterface
    public function getUserIdentifier(): string { return $this->mailCli; }
    public function getRoles(): array { $r = $this->roles; $r[] = 'ROLE_USER'; return array_unique($r); }
    public function setRoles(array $r): static { $this->roles = $r; return $this; }
    public function getPassword(): string { return $this->password; }
    public function setPassword(string $p): static { $this->password = $p; return $this; }
    public function eraseCredentials(): void {}

    // Getters / Setters
    public function getIdCli(): ?int { return $this->idCli; }
    public function getNomCli(): string { return $this->nomCli; }
    public function setNomCli(string $v): static { $this->nomCli = $v; return $this; }
    public function getPrenomCli(): string { return $this->prenomCli; }
    public function setPrenomCli(string $v): static { $this->prenomCli = $v; return $this; }
    public function getMailCli(): string { return $this->mailCli; }
    public function setMailCli(string $v): static { $this->mailCli = $v; return $this; }
    public function getTelCli(): ?string { return $this->telCli; }
    public function setTelCli(?string $v): static { $this->telCli = $v; return $this; }
    public function getDateNaissanceCli(): ?\DateTimeInterface { return $this->dateNaissanceCli; }
    public function setDateNaissanceCli(?\DateTimeInterface $v): static { $this->dateNaissanceCli = $v; return $this; }
    public function getTypeClient(): ?TypeClient { return $this->typeClient; }
    public function setTypeClient(?TypeClient $v): static { $this->typeClient = $v; return $this; }
    public function getAdresses(): Collection { return $this->adresses; }
    public function getCommandes(): Collection { return $this->commandes; }
    public function getAvis(): Collection { return $this->avis; }
    public function getFavoris(): Collection { return $this->favoris; }
    public function getFullName(): string { return $this->prenomCli . ' ' . $this->nomCli; }
}