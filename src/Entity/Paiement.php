namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Paiement
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $libellePay; // carte, virement, paypal...

    public function getId(): ?int { return $this->id; }
    public function getLibellePay(): string { return $this->libellePay; }
    public function setLibellePay(string $v): static { $this->libellePay = $v; return $this; }
    public function __toString(): string { return $this->libellePay; }
}