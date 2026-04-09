namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Commande
{
    const STATUTS = ['en_attente', 'confirmee', 'expediee', 'livree', 'annulee'];

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $statutCde = 'en_attente';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $montantCde = '0.00';

    #[ORM\Column(type: 'boolean')]
    private bool $estPayeCde = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateCommande;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private Client $client;

    #[ORM\ManyToOne(targetEntity: Livraison::class)]
    private ?Livraison $livraison = null;

    #[ORM\ManyToOne(targetEntity: Paiement::class)]
    private ?Paiement $paiement = null;

    #[ORM\ManyToOne(targetEntity: Adresse::class)]
    private ?Adresse $adresseLivraison = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: LigneCommande::class, cascade: ['persist', 'remove'])]
    private Collection $lignesCommande;

    #[ORM\PrePersist]
    public function setDateCommandeValue(): void { $this->dateCommande = new \DateTimeImmutable(); }

    public function __construct() { $this->lignesCommande = new ArrayCollection(); }

    public function getId(): ?int { return $this->id; }
    public function getStatutCde(): string { return $this->statutCde; }
    public function setStatutCde(string $v): static { $this->statutCde = $v; return $this; }
    public function getMontantCde(): string { return $this->montantCde; }
    public function setMontantCde(string $v): static { $this->montantCde = $v; return $this; }
    public function isEstPayeCde(): bool { return $this->estPayeCde; }
    public function setEstPayeCde(bool $v): static { $this->estPayeCde = $v; return $this; }
    public function getDateCommande(): \DateTimeImmutable { return $this->dateCommande; }
    public function getClient(): Client { return $this->client; }
    public function setClient(Client $v): static { $this->client = $v; return $this; }
    public function getLivraison(): ?Livraison { return $this->livraison; }
    public function setLivraison(?Livraison $v): static { $this->livraison = $v; return $this; }
    public function getPaiement(): ?Paiement { return $this->paiement; }
    public function setPaiement(?Paiement $v): static { $this->paiement = $v; return $this; }
    public function getAdresseLivraison(): ?Adresse { return $this->adresseLivraison; }
    public function setAdresseLivraison(?Adresse $v): static { $this->adresseLivraison = $v; return $this; }
    public function getLignesCommande(): Collection { return $this->lignesCommande; }

    public function recalculerMontant(): void
    {
        $total = '0.00';
        foreach ($this->lignesCommande as $ligne) {
            $total = bcadd($total, bcmul($ligne->getProduit()->getPrixProd(), (string)$ligne->getQuantite(), 2), 2);
            $total = bcsub($total, (string)$ligne->getReduction(), 2);
        }
        $this->montantCde = $total;
    }
}