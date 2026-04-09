namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class LigneCommande
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $quantite = 1;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    private string $reduction = '0.00';

    #[ORM\ManyToOne(targetEntity: Commande::class, inversedBy: 'lignesCommande')]
    #[ORM\JoinColumn(nullable: false)]
    private Commande $commande;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'lignesCommande')]
    #[ORM\JoinColumn(nullable: false)]
    private Produit $produit;

    public function getId(): ?int { return $this->id; }
    public function getQuantite(): int { return $this->quantite; }
    public function setQuantite(int $v): static { $this->quantite = $v; return $this; }
    public function getReduction(): string { return $this->reduction; }
    public function setReduction(string $v): static { $this->reduction = $v; return $this; }
    public function getCommande(): Commande { return $this->commande; }
    public function setCommande(Commande $v): static { $this->commande = $v; return $this; }
    public function getProduit(): Produit { return $this->produit; }
    public function setProduit(Produit $v): static { $this->produit = $v; return $this; }

    public function getSousTotal(): string
    {
        $base = bcmul($this->produit->getPrixProd(), (string)$this->quantite, 2);
        return bcsub($base, $this->reduction, 2);
    }
}