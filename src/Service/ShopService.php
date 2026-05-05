<?php

declare(strict_types=1);

namespace App\Service;

use App\Infrastructure\SqliteStore;
use DateInterval;
use DateTimeImmutable;
use PDO;
use RuntimeException;

final class ShopService
{
    private const PRODUCT_SELECT = <<<'SQL'
SELECT p.IdProd, p.IdTypeProd, p.NomProd, p.PrixProd, p.StockProd, p.ImageProd,
       p.TailleProd, p.RefProd, p.DescProd, p.CouleurProd, p.GammeProd,
       p.AbsorptionProd, p.UsageProd, p.PointsFortsProd, p.BadgeProd,
       tp.NomTypeProd,
       COALESCE(AVG(a.NoteAvis), 0) AS NoteMoyenne,
       COUNT(a.IdAvis) AS NombreAvis
FROM produits p
JOIN typeproduits tp ON tp.IdTypeProd = p.IdTypeProd
LEFT JOIN avis a ON a.IdProd = p.IdProd
SQL;

    private const DELIVERY_OPTIONS = [
        'Standard 72h' => [
            'carrier' => 'MENstruation Standard',
            'delay' => '72h',
            'fee' => 4.9,
            'min_days' => 3,
            'max_days' => 10,
        ],
        'Express 24h' => [
            'carrier' => 'MENstruation Express',
            'delay' => '24h',
            'fee' => 8.9,
            'min_days' => 1,
            'max_days' => 5,
        ],
    ];

    public function __construct(private readonly SqliteStore $store)
    {
    }

    public function homePayload(): array
    {
        $products = $this->listProducts();
        $categories = $this->listCategories();
        $reviews = $this->listReviews();

        return [
            'hero' => [
                'title' => 'MENstruation',
                'subtitle' => 'La boutique gamer adulte pensee pour les longues sessions.',
                'description' => 'Slips couches, calecons couches et leggings couches pour adultes, avec univers visuel gamer, versions premium et checkout complet.',
                'mission' => [
                    'goal' => 'Proposer des protections adultes pensees pour les gamers et gameuses qui veulent rester confortables pendant de longues sessions.',
                    'vision' => 'Montrer qu un produit intime peut etre utile, assume, esthetique et integre dans un univers e-commerce moderne.',
                    'method' => 'Nous le faisons avec une boutique simple a utiliser, un catalogue clair, des gammes Core et Premium, des avis clients, un panier et une livraison suivie.',
                ],
            ],
            'featuredProducts' => array_slice($products, 0, 6),
            'categories' => $categories,
            'latestReviews' => array_slice($reviews, 0, 4),
            'filters' => $this->listFilters(),
            'metrics' => [
                'products' => count($products),
                'categories' => count($categories),
                'reviews' => count($reviews),
            ],
        ];
    }

    public function login(string $email, string $password): array
    {
        $row = $this->fetchOne(
            'SELECT c.IdCli, c.NomCli, c.PrenomCli, c.MailCli, c.FavoriCli, c.TelCli, tc.NomTypeCli AS Role
             FROM client c
             JOIN typeclient tc ON tc.IdTypeCli = c.IdTypeCli
             WHERE c.MailCli = :email AND c.MdpCli = :password',
            [
                'email' => trim($email),
                'password' => $this->hashPassword($password),
            ]
        );

        if (!$row) {
            throw new RuntimeException('Email ou mot de passe incorrect.');
        }

        return $row;
    }

    public function register(array $payload): array
    {
        $pdo = $this->pdo();
        $statement = $pdo->prepare(
            'INSERT INTO client (IdTypeCli, NomCli, PrenomCli, DateNaissanceCli, MailCli, MdpCli, FavoriCli, TelCli)
             VALUES (:role, :nom, :prenom, :naissance, :mail, :password, :favori, :tel)'
        );
        $statement->execute([
            'role' => $payload['IdTypeCli'] ?? 1,
            'nom' => $this->requireNonEmpty($payload['NomCli'] ?? null, 'Le nom est requis.'),
            'prenom' => $this->requireNonEmpty($payload['PrenomCli'] ?? null, 'Le prenom est requis.'),
            'naissance' => $payload['DateNaissanceCli'] ?: '2000-01-01',
            'mail' => $this->requireNonEmpty($payload['MailCli'] ?? null, 'L email est requis.'),
            'password' => $this->hashPassword((string) ($payload['MdpCli'] ?? '')),
            'favori' => (string) ($payload['FavoriCli'] ?? ''),
            'tel' => (string) ($payload['TelCli'] ?? ''),
        ]);

        return $this->getClientProfile((int) $pdo->lastInsertId());
    }

    public function catalog(array $filters = []): array
    {
        return [
            'categories' => $this->listCategories(),
            'filters' => $this->listFilters(),
            'products' => $this->listProducts(
                (string) ($filters['search'] ?? ''),
                (string) ($filters['category'] ?? ''),
                (string) ($filters['gamme'] ?? ''),
                (string) ($filters['color'] ?? '')
            ),
        ];
    }

    public function productDetail(int $productId): array
    {
        $product = $this->getProduct($productId);
        if (!$product) {
            throw new RuntimeException('Produit introuvable.');
        }

        $product['reviews'] = $this->listReviews($productId, null);
        $product['relatedProducts'] = $this->relatedProducts($productId, (int) $product['IdTypeProd']);

        return $product;
    }

    public function addReview(array $payload): array
    {
        $pdo = $this->pdo();
        $exists = $this->fetchOne(
            'SELECT IdAvis FROM avis WHERE IdProd = :product AND IdCli = :client',
            ['product' => $payload['IdProd'], 'client' => $payload['IdCli']]
        );

        if ($exists) {
            throw new RuntimeException('Tu as deja laisse un avis sur ce produit.');
        }

        $statement = $pdo->prepare(
            'INSERT INTO avis (IdProd, IdCli, TitreAvis, MsgAvis, NoteAvis, DateAvis)
             VALUES (:product, :client, :title, :message, :rating, DATE("now"))'
        );
        $statement->execute([
            'product' => (int) $payload['IdProd'],
            'client' => (int) $payload['IdCli'],
            'title' => $this->requireNonEmpty($payload['TitreAvis'] ?? null, 'Le titre est requis.'),
            'message' => $this->requireNonEmpty($payload['MsgAvis'] ?? null, 'Le message est requis.'),
            'rating' => max(1, min(5, (int) ($payload['NoteAvis'] ?? 0))),
        ]);

        return $this->fetchOne(
            'SELECT a.IdAvis, a.TitreAvis, a.MsgAvis, a.NoteAvis, a.DateAvis,
                    c.PrenomCli || " " || c.NomCli AS Auteur,
                    p.NomProd, p.IdProd
             FROM avis a
             JOIN client c ON c.IdCli = a.IdCli
             JOIN produits p ON p.IdProd = a.IdProd
             WHERE a.IdAvis = :id',
            ['id' => (int) $pdo->lastInsertId()]
        ) ?? [];
    }

    public function profile(int $clientId): array
    {
        $client = $this->fetchOne(
            'SELECT c.IdCli, c.NomCli, c.PrenomCli, c.DateNaissanceCli, c.MailCli, c.FavoriCli, c.TelCli,
                    tc.NomTypeCli AS Role
             FROM client c
             JOIN typeclient tc ON tc.IdTypeCli = c.IdTypeCli
             WHERE c.IdCli = :id',
            ['id' => $clientId]
        );

        $addresses = $this->fetchAll(
            'SELECT a.IdAddr, a.TypeAddr, a.RueAddr, a.VilleAddr, a.CPAddr, a.PaysAddr
             FROM possede p
             JOIN adresse a ON a.IdAddr = p.IdAddr
             WHERE p.IdCli = :id
             ORDER BY a.TypeAddr, a.IdAddr DESC',
            ['id' => $clientId]
        );

        $stats = $this->fetchOne(
            'SELECT COUNT(*) AS OrderCount, COALESCE(SUM(MontantCde), 0) AS TotalSpent
             FROM commande WHERE IdCli = :id',
            ['id' => $clientId]
        ) ?? ['OrderCount' => 0, 'TotalSpent' => 0];

        $reviewCount = (int) $this->fetchValue('SELECT COUNT(*) FROM avis WHERE IdCli = :id', ['id' => $clientId]);

        return [
            'client' => $client,
            'addresses' => $addresses,
            'reviews' => $this->listReviews(null, $clientId),
            'stats' => [
                'orders' => (int) ($stats['OrderCount'] ?? 0),
                'spent' => (float) ($stats['TotalSpent'] ?? 0),
                'reviews' => $reviewCount,
            ],
        ];
    }

    public function updateProfile(int $clientId, array $payload): array
    {
        $statement = $this->pdo()->prepare(
            'UPDATE client SET NomCli = :nom, PrenomCli = :prenom, MailCli = :mail, TelCli = :tel, FavoriCli = :favori
             WHERE IdCli = :id'
        );
        $statement->execute([
            'nom' => $this->requireNonEmpty($payload['NomCli'] ?? null, 'Le nom est requis.'),
            'prenom' => $this->requireNonEmpty($payload['PrenomCli'] ?? null, 'Le prenom est requis.'),
            'mail' => $this->requireNonEmpty($payload['MailCli'] ?? null, 'L email est requis.'),
            'tel' => (string) ($payload['TelCli'] ?? ''),
            'favori' => (string) ($payload['FavoriCli'] ?? ''),
            'id' => $clientId,
        ]);

        return $this->profile($clientId);
    }

    public function orders(int $clientId): array
    {
        return [
            'paymentMethods' => $this->paymentMethods(),
            'orders' => $this->listOrders($clientId),
        ];
    }

    public function createOrder(array $payload): array
    {
        $items = $payload['items'] ?? [];
        if ([] === $items) {
            throw new RuntimeException('Le panier est vide.');
        }

        $clientId = (int) ($payload['IdCli'] ?? 0);
        $paymentId = (int) ($payload['IdPay'] ?? 0);
        $delivery = $this->validateDelivery($payload['delivery'] ?? []);
        $paymentMethod = $this->fetchOne('SELECT LibellePay FROM paiement WHERE IdPay = :id', ['id' => $paymentId]);
        if (!$paymentMethod) {
            throw new RuntimeException('La methode de paiement est introuvable.');
        }

        $paymentAudit = $this->buildPaymentAudit((string) $paymentMethod['LibellePay'], $payload['paymentData'] ?? []);
        $address = $this->normalizeAddress($payload['address'] ?? []);
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            if (!$this->fetchOne('SELECT IdCli FROM client WHERE IdCli = :id', ['id' => $clientId])) {
                throw new RuntimeException('Client introuvable.');
            }

            $subtotal = 0.0;
            $normalizedItems = [];
            foreach ($items as $item) {
                $quantity = $this->normalizeQuantity($item['Quantite'] ?? 0);
                $product = $this->fetchOne(
                    'SELECT PrixProd, StockProd, NomProd FROM produits WHERE IdProd = :id',
                    ['id' => (int) $item['IdProd']]
                );
                if (!$product) {
                    throw new RuntimeException('Produit introuvable.');
                }
                if ((int) $product['StockProd'] < $quantity) {
                    throw new RuntimeException(sprintf('Stock insuffisant pour %s.', $product['NomProd']));
                }

                $price = (float) $product['PrixProd'];
                $subtotal += $price * $quantity;
                $normalizedItems[] = [
                    'IdProd' => (int) $item['IdProd'],
                    'Quantite' => $quantity,
                    'Reduction' => (float) ($item['Reduction'] ?? 0),
                ];
            }

            $addressStatement = $pdo->prepare(
                'INSERT INTO adresse (TypeAddr, RueAddr, VilleAddr, CPAddr, PaysAddr)
                 VALUES (:type, :rue, :ville, :cp, :pays)'
            );
            $addressStatement->execute([
                'type' => $address['TypeAddr'],
                'rue' => $address['RueAddr'],
                'ville' => $address['VilleAddr'],
                'cp' => $address['CPAddr'],
                'pays' => $address['PaysAddr'],
            ]);
            $addressId = (int) $pdo->lastInsertId();

            $pdo->prepare('INSERT OR IGNORE INTO possede (IdCli, IdAddr) VALUES (:client, :address)')
                ->execute(['client' => $clientId, 'address' => $addressId]);

            $total = $subtotal + (float) $delivery['FraisLivr'];
            $pdo->prepare(
                'INSERT INTO commande (IdPay, IdCli, StatutCde, MontantCde, EstPayeCde, DateCde)
                 VALUES (:payment, :client, :status, :amount, 1, DATE("now"))'
            )->execute([
                'payment' => $paymentId,
                'client' => $clientId,
                'status' => 'En preparation',
                'amount' => $total,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $lineStatement = $pdo->prepare(
                'INSERT INTO lignecommande (IdCde, IdProd, Reduction, Quantite)
                 VALUES (:order, :product, :reduction, :quantity)'
            );
            $stockStatement = $pdo->prepare('UPDATE produits SET StockProd = StockProd - :quantity WHERE IdProd = :product');

            foreach ($normalizedItems as $item) {
                $lineStatement->execute([
                    'order' => $orderId,
                    'product' => $item['IdProd'],
                    'reduction' => $item['Reduction'],
                    'quantity' => $item['Quantite'],
                ]);
                $stockStatement->execute([
                    'quantity' => $item['Quantite'],
                    'product' => $item['IdProd'],
                ]);
            }

            $pdo->prepare(
                'INSERT INTO livraison (IdAddr, IdCde, NomLivr, ChoixLivr, DelaiLivr, FraisLivr, DateLivr)
                 VALUES (:address, :order, :carrier, :choice, :delay, :fee, :date)'
            )->execute([
                'address' => $addressId,
                'order' => $orderId,
                'carrier' => $delivery['NomLivr'],
                'choice' => $delivery['ChoixLivr'],
                'delay' => $delivery['DelaiLivr'],
                'fee' => $delivery['FraisLivr'],
                'date' => $delivery['DateLivr'],
            ]);

            $reference = sprintf('AUTH-%s-%04d', (new DateTimeImmutable())->format('YmdHis'), $orderId);
            $pdo->prepare(
                'INSERT INTO transactionpaiement (
                    IdCde, StatutTransac, MontantTransac, DeviseTransac, ReferenceTransac,
                    PorteurTransac, MarqueTransac, MasqueTransac, QuatreDerniersTransac
                 ) VALUES (:order, :status, :amount, :currency, :reference, :holder, :brand, :masked, :last4)'
            )->execute([
                'order' => $orderId,
                'status' => 'Autorisee',
                'amount' => $total,
                'currency' => 'EUR',
                'reference' => $reference,
                'holder' => $paymentAudit['holder'],
                'brand' => $paymentAudit['brand'],
                'masked' => $paymentAudit['masked'],
                'last4' => $paymentAudit['last4'],
            ]);

            $pdo->commit();

            return [
                'IdCde' => $orderId,
                'MontantCde' => round($total, 2),
                'PaymentStatus' => 'Autorisee',
                'PaymentReference' => $reference,
            ];
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function adminDashboard(): array
    {
        return [
            'stats' => [
                'clients' => (int) $this->fetchValue('SELECT COUNT(*) FROM client'),
                'products' => (int) $this->fetchValue('SELECT COUNT(*) FROM produits'),
                'orders' => (int) $this->fetchValue('SELECT COUNT(*) FROM commande'),
                'reviews' => (int) $this->fetchValue('SELECT COUNT(*) FROM avis'),
                'revenue' => (float) $this->fetchValue('SELECT COALESCE(SUM(MontantCde), 0) FROM commande WHERE EstPayeCde = 1'),
            ],
            'lowStock' => $this->fetchAll(
                'SELECT IdProd, NomProd, StockProd FROM produits WHERE StockProd <= 20 ORDER BY StockProd ASC, NomProd ASC'
            ),
            'latestOrders' => $this->fetchAll(
                'SELECT c.IdCde, c.StatutCde, c.MontantCde, cl.PrenomCli || " " || cl.NomCli AS ClientNom
                 FROM commande c
                 JOIN client cl ON cl.IdCli = c.IdCli
                 ORDER BY c.IdCde DESC
                 LIMIT 6'
            ),
        ];
    }

    public function adminClients(): array
    {
        return $this->fetchAll(
            'SELECT c.IdCli, c.NomCli, c.PrenomCli, c.MailCli, c.TelCli, tc.NomTypeCli AS Role
             FROM client c
             JOIN typeclient tc ON tc.IdTypeCli = c.IdTypeCli
             ORDER BY c.IdCli DESC'
        );
    }

    public function adminProducts(): array
    {
        return $this->listProducts();
    }

    public function saveProduct(array $payload): array
    {
        $fields = [
            'category' => (int) ($payload['IdTypeProd'] ?? 0),
            'name' => $this->requireNonEmpty($payload['NomProd'] ?? null, 'Le nom produit est requis.'),
            'price' => (float) ($payload['PrixProd'] ?? 0),
            'stock' => (int) ($payload['StockProd'] ?? 0),
            'image' => $this->normalizePublicPath(
                $this->requireNonEmpty($payload['ImageProd'] ?? null, 'L image est requise.')
            ),
            'size' => $this->requireNonEmpty($payload['TailleProd'] ?? null, 'La taille est requise.'),
            'ref' => $this->requireNonEmpty($payload['RefProd'] ?? null, 'La reference est requise.'),
            'description' => $this->requireNonEmpty($payload['DescProd'] ?? null, 'La description est requise.'),
            'color' => $this->requireNonEmpty($payload['CouleurProd'] ?? null, 'La couleur est requise.'),
            'gamme' => $this->requireNonEmpty($payload['GammeProd'] ?? null, 'La gamme est requise.'),
            'absorption' => $this->requireNonEmpty($payload['AbsorptionProd'] ?? null, 'Le niveau d absorption est requis.'),
            'usage' => $this->requireNonEmpty($payload['UsageProd'] ?? null, 'L usage est requis.'),
            'points' => $this->requireNonEmpty($payload['PointsFortsProd'] ?? null, 'Les points forts sont requis.'),
            'badge' => (string) ($payload['BadgeProd'] ?? ''),
        ];

        $pdo = $this->pdo();
        if (!empty($payload['IdProd'])) {
            $pdo->prepare(
                'UPDATE produits
                 SET IdTypeProd = :category, NomProd = :name, PrixProd = :price, StockProd = :stock,
                     ImageProd = :image, TailleProd = :size, RefProd = :ref, DescProd = :description,
                     CouleurProd = :color, GammeProd = :gamme, AbsorptionProd = :absorption, UsageProd = :usage,
                     PointsFortsProd = :points, BadgeProd = :badge
                 WHERE IdProd = :id'
            )->execute($fields + ['id' => (int) $payload['IdProd']]);
            $id = (int) $payload['IdProd'];
        } else {
            $pdo->prepare(
                'INSERT INTO produits (
                    IdTypeProd, NomProd, PrixProd, StockProd, ImageProd,
                    TailleProd, RefProd, DescProd, CouleurProd, GammeProd,
                    AbsorptionProd, UsageProd, PointsFortsProd, BadgeProd
                 ) VALUES (
                    :category, :name, :price, :stock, :image,
                    :size, :ref, :description, :color, :gamme,
                    :absorption, :usage, :points, :badge
                 )'
            )->execute($fields);
            $id = (int) $pdo->lastInsertId();
        }

        return $this->getProduct($id) ?? [];
    }

    private function listCategories(): array
    {
        return $this->fetchAll(
            'SELECT tp.IdTypeProd, tp.NomTypeProd, COUNT(p.IdProd) AS ProductCount
             FROM typeproduits tp
             LEFT JOIN produits p ON p.IdTypeProd = tp.IdTypeProd
             GROUP BY tp.IdTypeProd, tp.NomTypeProd
             ORDER BY tp.IdTypeProd'
        );
    }

    private function listFilters(): array
    {
        return [
            'colors' => array_map(static fn (array $row): string => (string) $row['CouleurProd'], $this->fetchAll('SELECT DISTINCT CouleurProd FROM produits ORDER BY CouleurProd')),
            'gammes' => array_map(static fn (array $row): string => (string) $row['GammeProd'], $this->fetchAll('SELECT DISTINCT GammeProd FROM produits ORDER BY GammeProd')),
            'absorptions' => array_map(static fn (array $row): string => (string) $row['AbsorptionProd'], $this->fetchAll('SELECT DISTINCT AbsorptionProd FROM produits ORDER BY AbsorptionProd')),
        ];
    }

    private function listProducts(string $search = '', string $categoryId = '', string $gamme = '', string $color = ''): array
    {
        $conditions = [];
        $params = [];

        if ('' !== trim($search)) {
            $conditions[] = '(p.NomProd LIKE :search OR p.DescProd LIKE :search OR p.RefProd LIKE :search OR p.CouleurProd LIKE :search)';
            $params['search'] = '%'.trim($search).'%';
        }
        if ('' !== trim($categoryId)) {
            $conditions[] = 'p.IdTypeProd = :category';
            $params['category'] = $categoryId;
        }
        if ('' !== trim($gamme)) {
            $conditions[] = 'p.GammeProd = :gamme';
            $params['gamme'] = $gamme;
        }
        if ('' !== trim($color)) {
            $conditions[] = 'p.CouleurProd = :color';
            $params['color'] = $color;
        }

        $sql = self::PRODUCT_SELECT.' '.([] !== $conditions ? 'WHERE '.implode(' AND ', $conditions) : '').' GROUP BY p.IdProd ORDER BY p.GammeProd DESC, p.NomProd ASC';

        return array_map(fn (array $row): array => $this->enrichProduct($row), $this->fetchAll($sql, $params));
    }

    private function getProduct(int $productId): ?array
    {
        $sql = self::PRODUCT_SELECT.' WHERE p.IdProd = :id GROUP BY p.IdProd';
        $row = $this->fetchOne($sql, ['id' => $productId]);

        return $row ? $this->enrichProduct($row) : null;
    }

    private function relatedProducts(int $productId, int $categoryId): array
    {
        $sql = self::PRODUCT_SELECT.' WHERE p.IdTypeProd = :category AND p.IdProd <> :id GROUP BY p.IdProd ORDER BY p.GammeProd DESC, p.StockProd DESC LIMIT 4';

        return array_map(
            fn (array $row): array => $this->enrichProduct($row),
            $this->fetchAll($sql, ['category' => $categoryId, 'id' => $productId])
        );
    }

    private function listReviews(?int $productId = null, ?int $clientId = null): array
    {
        $sql = 'SELECT a.IdAvis, a.TitreAvis, a.MsgAvis, a.NoteAvis, a.DateAvis,
                       c.PrenomCli || " " || c.NomCli AS Auteur,
                       p.NomProd, p.IdProd
                FROM avis a
                JOIN client c ON c.IdCli = a.IdCli
                JOIN produits p ON p.IdProd = a.IdProd';
        $conditions = [];
        $params = [];

        if (null !== $productId) {
            $conditions[] = 'a.IdProd = :product';
            $params['product'] = $productId;
        }
        if (null !== $clientId) {
            $conditions[] = 'a.IdCli = :client';
            $params['client'] = $clientId;
        }
        if ([] !== $conditions) {
            $sql .= ' WHERE '.implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY a.DateAvis DESC, a.IdAvis DESC';

        return $this->fetchAll($sql, $params);
    }

    private function getClientProfile(int $clientId): array
    {
        return $this->fetchOne(
            'SELECT c.IdCli, c.NomCli, c.PrenomCli, c.DateNaissanceCli, c.MailCli, c.FavoriCli, c.TelCli,
                    tc.NomTypeCli AS Role
             FROM client c
             JOIN typeclient tc ON tc.IdTypeCli = c.IdTypeCli
             WHERE c.IdCli = :id',
            ['id' => $clientId]
        ) ?? [];
    }

    private function paymentMethods(): array
    {
        return $this->fetchAll('SELECT IdPay, LibellePay FROM paiement ORDER BY IdPay');
    }

    private function listOrders(int $clientId): array
    {
        $orders = $this->fetchAll(
            'SELECT c.IdCde, c.StatutCde, c.MontantCde, c.EstPayeCde, c.DateCde,
                    p.LibellePay, l.NomLivr, l.ChoixLivr, l.DelaiLivr, l.FraisLivr, l.DateLivr,
                    tp.ReferenceTransac, tp.StatutTransac, tp.MarqueTransac, tp.MasqueTransac,
                    a.TypeAddr, a.RueAddr, a.VilleAddr, a.CPAddr, a.PaysAddr
             FROM commande c
             LEFT JOIN paiement p ON p.IdPay = c.IdPay
             LEFT JOIN livraison l ON l.IdCde = c.IdCde
             LEFT JOIN transactionpaiement tp ON tp.IdCde = c.IdCde
             LEFT JOIN adresse a ON a.IdAddr = l.IdAddr
             WHERE c.IdCli = :id
             ORDER BY c.IdCde DESC',
            ['id' => $clientId]
        );

        foreach ($orders as &$order) {
            $order['Lignes'] = $this->fetchAll(
                'SELECT lc.Quantite, lc.Reduction, pr.NomProd, pr.PrixProd, pr.ImageProd
                 FROM lignecommande lc
                 JOIN produits pr ON pr.IdProd = lc.IdProd
                 WHERE lc.IdCde = :id
                 ORDER BY pr.NomProd',
                ['id' => $order['IdCde']]
            );
            $order['TrackingSteps'] = $this->buildTrackingSteps($order);
        }

        return $orders;
    }

    private function buildTrackingSteps(array $order): array
    {
        if ((int) ($order['EstPayeCde'] ?? 0) !== 1) {
            return [];
        }

        $status = strtolower((string) ($order['StatutCde'] ?? ''));
        $deliveryDate = $order['DateLivr'] ?? null;
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');

        $currentStep = 1;
        if ('en preparation' === $status) {
            $currentStep = 2;
        } elseif ('expediee' === $status) {
            $currentStep = ($deliveryDate && $deliveryDate > $today) ? 4 : 3;
        } elseif ('livree' === $status) {
            $currentStep = 5;
        }

        $steps = [
            ['label' => 'Paiement confirme', 'description' => 'Ton paiement a bien ete valide.', 'state' => 'done'],
            ['label' => 'Commande preparee', 'description' => 'Les produits sont en cours de preparation en atelier.', 'state' => $currentStep >= 2 ? 'done' : 'todo'],
            ['label' => 'Commande expediee', 'description' => 'Transporteur: '.($order['NomLivr'] ?? 'MENstruation Logistics').'.', 'state' => $currentStep >= 3 ? 'done' : 'todo'],
            ['label' => 'Livraison en cours', 'description' => $deliveryDate ? 'Arrivee estimee le '.$deliveryDate.'.' : 'Le colis est en route vers ton adresse.', 'state' => $currentStep >= 4 ? 'done' : 'todo'],
            ['label' => 'Arrivee chez toi', 'description' => $deliveryDate ? 'Commande livree le '.$deliveryDate.'.' : 'La commande sera marquee recue une fois livree.', 'state' => $currentStep >= 5 ? 'done' : 'todo'],
        ];

        foreach ($steps as $index => &$step) {
            if ($index + 1 === $currentStep && $currentStep < 5) {
                $step['state'] = 'current';
            }
        }

        return $steps;
    }

    private function enrichProduct(array $product): array
    {
        $product['ImageProd'] = $this->normalizePublicPath($product['ImageProd'] ?? null);
        $product['PointsForts'] = $this->parsePoints($product['PointsFortsProd'] ?? '');
        $product['IsPremium'] = ($product['GammeProd'] ?? '') === 'Premium';
        $product['InStock'] = (int) ($product['StockProd'] ?? 0) > 0;

        return $product;
    }

    private function parsePoints(?string $value): array
    {
        if (!$value) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode('|', $value))));
    }

    private function normalizePublicPath(?string $path): string
    {
        return ltrim(trim((string) $path), '/');
    }

    private function normalizeAddress(array $address): array
    {
        $country = $this->requireNonEmpty($address['PaysAddr'] ?? null, 'Le pays de livraison est requis.');

        return [
            'TypeAddr' => $this->requireNonEmpty($address['TypeAddr'] ?? null, 'Le type d adresse est requis.'),
            'RueAddr' => $this->requireNonEmpty($address['RueAddr'] ?? null, 'La rue de livraison est requise.'),
            'VilleAddr' => $this->requireNonEmpty($address['VilleAddr'] ?? null, 'La ville de livraison est requise.'),
            'PaysAddr' => $country,
            'CPAddr' => $this->validatePostalCode($this->requireNonEmpty($address['CPAddr'] ?? null, 'Le code postal de livraison est requis.'), $country),
        ];
    }

    private function validatePostalCode(string $postalCode, string $country): string
    {
        $compact = str_replace(' ', '', $postalCode);
        if ('france' === strtolower($country)) {
            if (1 !== preg_match('/^\d{5}$/', $compact)) {
                throw new RuntimeException('Le code postal doit contenir 5 chiffres pour la France.');
            }

            return $compact;
        }

        if (1 !== preg_match('/^[A-Za-z0-9\- ]{4,10}$/', $postalCode)) {
            throw new RuntimeException('Le code postal saisi est invalide.');
        }

        return $postalCode;
    }

    private function validateDelivery(array $delivery): array
    {
        $choice = $this->requireNonEmpty($delivery['ChoixLivr'] ?? null, 'Le mode de livraison est requis.');
        $option = self::DELIVERY_OPTIONS[$choice] ?? null;
        if (!$option) {
            throw new RuntimeException('Le mode de livraison choisi est invalide.');
        }

        $selected = DateTimeImmutable::createFromFormat('Y-m-d', $this->requireNonEmpty($delivery['DateLivr'] ?? null, 'La date de livraison souhaitee est requise.'));
        if (!$selected) {
            throw new RuntimeException('La date de livraison est invalide.');
        }

        $today = new DateTimeImmutable('today');
        $minDate = $today->add(new DateInterval('P'.$option['min_days'].'D'));
        $maxDate = $today->add(new DateInterval('P'.$option['max_days'].'D'));

        if ($selected < $minDate) {
            throw new RuntimeException(sprintf('La premiere date disponible pour %s est le %s.', $choice, $minDate->format('Y-m-d')));
        }
        if ($selected > $maxDate) {
            throw new RuntimeException(sprintf('La date de livraison doit etre avant le %s pour %s.', $maxDate->format('Y-m-d'), $choice));
        }

        return [
            'NomLivr' => $option['carrier'],
            'ChoixLivr' => $choice,
            'DelaiLivr' => $option['delay'],
            'FraisLivr' => $option['fee'],
            'DateLivr' => $selected->format('Y-m-d'),
        ];
    }

    private function normalizeQuantity(mixed $value): int
    {
        $quantity = (int) $value;
        if ($quantity <= 0) {
            throw new RuntimeException('Chaque produit doit avoir une quantite positive.');
        }
        if ($quantity > 10) {
            throw new RuntimeException('Une meme ligne de commande ne peut pas depasser 10 unites.');
        }

        return $quantity;
    }

    private function buildPaymentAudit(string $paymentMethod, array $paymentData): array
    {
        $method = strtolower($paymentMethod);
        if ('carte bancaire' === $method) {
            return $this->validateCardPayment($paymentData);
        }
        if ('paypal' === $method) {
            $email = $this->requireNonEmpty($paymentData['walletEmail'] ?? null, 'L email PayPal est requis.');
            if (!str_contains($email, '@')) {
                throw new RuntimeException('L email PayPal est invalide.');
            }

            return ['brand' => 'PayPal', 'holder' => $email, 'last4' => '', 'masked' => $email];
        }
        if (in_array($method, ['apple pay', 'google pay'], true)) {
            $device = $this->requireNonEmpty($paymentData['walletDevice'] ?? null, sprintf('Un appareil %s doit etre indique.', $paymentMethod));

            return ['brand' => $paymentMethod, 'holder' => $device, 'last4' => '', 'masked' => $device];
        }

        throw new RuntimeException('La methode de paiement est invalide.');
    }

    private function validateCardPayment(array $paymentData): array
    {
        $holder = $this->requireNonEmpty($paymentData['cardholderName'] ?? null, 'Le nom du titulaire de la carte est requis.');
        if (strlen($holder) < 4) {
            throw new RuntimeException('Le nom du titulaire de la carte est trop court.');
        }

        $cardNumber = $this->digitsOnly($paymentData['cardNumber'] ?? '');
        $length = strlen($cardNumber);
        if ($length < 13 || $length > 19) {
            throw new RuntimeException('Le numero de carte bancaire doit contenir entre 13 et 19 chiffres.');
        }
        if (!$this->isValidLuhn($cardNumber)) {
            throw new RuntimeException('Le numero de carte bancaire est invalide.');
        }

        $expiryMonth = (int) ($paymentData['expiryMonth'] ?? 0);
        $expiryYear = (int) ($paymentData['expiryYear'] ?? 0);
        if ($expiryMonth < 1 || $expiryMonth > 12) {
            throw new RuntimeException('Le mois d expiration est invalide.');
        }

        $expiry = DateTimeImmutable::createFromFormat('Y-n-j', sprintf('%d-%d-%d', $expiryYear, $expiryMonth, cal_days_in_month(CAL_GREGORIAN, $expiryMonth, $expiryYear)));
        $today = new DateTimeImmutable('today');
        if (!$expiry || $expiry < $today) {
            throw new RuntimeException('La carte bancaire est expiree.');
        }
        if ($expiryYear > (int) $today->format('Y') + 15) {
            throw new RuntimeException('L annee d expiration de la carte est invalide.');
        }

        $brand = $this->inferCardBrand($cardNumber);
        $cvv = $this->digitsOnly($paymentData['cvv'] ?? '');
        $expectedLengths = 'American Express' === $brand ? [4] : [3, 4];
        if (!in_array(strlen($cvv), $expectedLengths, true)) {
            throw new RuntimeException('Le cryptogramme visuel est invalide.');
        }

        return [
            'brand' => $brand,
            'holder' => $holder,
            'last4' => substr($cardNumber, -4),
            'masked' => substr($cardNumber, 0, 4).' **** **** '.substr($cardNumber, -4),
        ];
    }

    private function digitsOnly(mixed $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    private function isValidLuhn(string $cardNumber): bool
    {
        $checksum = 0;
        $parity = strlen($cardNumber) % 2;
        foreach (str_split($cardNumber) as $index => $digit) {
            $value = (int) $digit;
            if ($index % 2 === $parity) {
                $value *= 2;
                if ($value > 9) {
                    $value -= 9;
                }
            }
            $checksum += $value;
        }

        return $checksum % 10 === 0;
    }

    private function inferCardBrand(string $cardNumber): string
    {
        return match (true) {
            1 === preg_match('/^4\d{12}(\d{3})?(\d{3})?$/', $cardNumber) => 'Visa',
            1 === preg_match('/^(5[1-5]\d{14}|2(2[2-9]\d{12}|[3-6]\d{13}|7([01]\d{12}|20\d{12})))$/', $cardNumber) => 'Mastercard',
            1 === preg_match('/^3[47]\d{13}$/', $cardNumber) => 'American Express',
            1 === preg_match('/^6(?:011|5\d{2})\d{12}$/', $cardNumber) => 'Discover',
            default => 'Carte',
        };
    }

    private function hashPassword(string $password): string
    {
        return hash('sha256', $password);
    }

    private function requireNonEmpty(mixed $value, string $message): string
    {
        $normalized = trim((string) $value);
        if ('' === $normalized) {
            throw new RuntimeException($message);
        }

        return $normalized;
    }

    private function pdo(): PDO
    {
        return $this->store->connection();
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll() ?: [];
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();

        return false === $row ? null : $row;
    }

    private function fetchValue(string $sql, array $params = []): mixed
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchColumn();
    }
}
