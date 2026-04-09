namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Avis
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    private string $titreAvis;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank, Assert\Length(min: 10)]
    private string $msgAvis;

    #[ORM\Column(type: 'smallint')]
    #[Assert\Range(min: 1, max: 5)]
    private int $noteAvis;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dateAvis;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'avis')]
    #[ORM\JoinColumn(nullable: false)]
    private Client $client;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'avis')]
    #[ORM\JoinColumn(nullable: false)]
    private Produit $produit;

    #[ORM\PrePersist]
    public function setDateAvisValue(): void { $this->dateAvis = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getTitreAvis(): string { return $this->titreAvis; }
    public function setTitreAvis(string $v): static { $this->titreAvis = $v; return $this; }
    public function getMsgAvis(): string { return $this->msgAvis; }
    public function setMsgAvis(string $v): static { $this->msgAvis = $v; return $this; }
    public function getNoteAvis(): int { return $this->noteAvis; }
    public function setNoteAvis(int $v): static { $this->noteAvis = $v; return $this; }
    public function getDateAvis(): \DateTimeImmutable { return $this->dateAvis; }
    public function getClient(): Client { return $this->client; }
    public function setClient(Client $v): static { $this->client = $v; return $this; }
    public function getProduit(): Produit { return $this->produit; }
    public function setProduit(Produit $v): static { $this->produit = $v; return $this; }
}