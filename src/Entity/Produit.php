namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private string $nomProd;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $prixProd;

    #[ORM\Column(type: 'integer')]
    private int $stockProd = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageProd = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $tailleProd = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $refProd;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descProd = null;

    #[ORM\ManyToOne(targetEntity: TypeProduit::class, inversedBy: 'produits')]
    private ?TypeProduit $typeProduit = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Avis::class)]
    private Collection $avis;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: LigneCommande::class)]
    private Collection $lignesCommande;

    public function __construct()
    {
        $this->avis           = new ArrayCollection();
        $this->lignesCommande = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getNomProd(): string { return $this->nomProd; }
    public function setNomProd(string $v): static { $this->nomProd = $v; return $this; }
    public function getPrixProd(): string { return $this->prixProd; }
    public function setPrixProd(string $v): static { $this->prixProd = $v; return $this; }
    public function getStockProd(): int { return $this->stockProd; }
    public function setStockProd(int $v): static { $this->stockProd = $v; return $this; }
    public function getImageProd(): ?string { return $this->imageProd; }
    public function setImageProd(?string $v): static { $this->imageProd = $v; return $this; }
    public function getTailleProd(): ?string { return $this->tailleProd; }
    public function setTailleProd(?string $v): static { $this->tailleProd = $v; return $this; }
    public function getRefProd(): string { return $this->refProd; }
    public function setRefProd(string $v): static { $this->refProd = $v; return $this; }
    public function getDescProd(): ?string { return $this->descProd; }
    public function setDescProd(?string $v): static { $this->descProd = $v; return $this; }
    public function getTypeProduit(): ?TypeProduit { return $this->typeProduit; }
    public function setTypeProduit(?TypeProduit $v): static { $this->typeProduit = $v; return $this; }
    public function getAvis(): Collection { return $this->avis; }
    public function getLignesCommande(): Collection { return $this->lignesCommande; }

    public function getNoteMoyenne(): ?float
    {
        $avis = $this->avis->filter(fn($a) => $a->getNoteAvis() !== null)->toArray();
        if (empty($avis)) return null;
        return array_sum(array_map(fn($a) => $a->getNoteAvis(), $avis)) / count($avis);
    }
}