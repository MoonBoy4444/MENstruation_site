<?php

// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\Adresse;
use App\Entity\Avis;
use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Livraison;
use App\Entity\Paiement;
use App\Entity\Produit;
use App\Entity\TypeClient;
use App\Entity\TypeProduit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ── 1. TYPES CLIENT ──────────────────────────────────────────────────
        $tcParticulier   = new TypeClient();
        $tcParticulier->setNomTypeClient('Particulier');

        $tcProfessionnel = new TypeClient();
        $tcProfessionnel->setNomTypeClient('Professionnel');

        $tcVIP = new TypeClient();
        $tcVIP->setNomTypeClient('VIP');

        $manager->persist($tcParticulier);
        $manager->persist($tcProfessionnel);
        $manager->persist($tcVIP);

        // ── 2. TYPES PRODUIT ─────────────────────────────────────────────────
        $tpHauts       = new TypeProduit(); $tpHauts->setNomTypeProd('Hauts');
        $tpPantalons   = new TypeProduit(); $tpPantalons->setNomTypeProd('Pantalons');
        $tpChaussures  = new TypeProduit(); $tpChaussures->setNomTypeProd('Chaussures');
        $tpAccessoires = new TypeProduit(); $tpAccessoires->setNomTypeProd('Accessoires');

        $manager->persist($tpHauts);
        $manager->persist($tpPantalons);
        $manager->persist($tpChaussures);
        $manager->persist($tpAccessoires);

        // ── 3. PRODUITS ──────────────────────────────────────────────────────
        $produitsData = [
            [
                'nom'    => 'T-shirt Blanc Basique',
                'ref'    => 'TSH-BLC-001',
                'prix'   => '19.99',
                'stock'  => 150,
                'taille' => 'XS / S / M / L / XL',
                'image'  => 'tshirt-blanc.png',
                'desc'   => "Le classique indémodable de toute garde-robe. Fabriqué en 100% coton bio, ce t-shirt blanc offre un confort optimal au quotidien. Sa coupe légèrement droite convient à toutes les morphologies. Lavable en machine à 30°C.",
                'type'   => $tpHauts,
            ],
            [
                'nom'    => 'Pull Oversize Gris',
                'ref'    => 'PUL-GRS-002',
                'prix'   => '45.99',
                'stock'  => 80,
                'taille' => 'S / M / L / XL',
                'image'  => 'pull-oversize-gris.png',
                'desc'   => "Pull oversize en mélange laine et acrylique pour un look cosy et tendance. Sa coupe ample et ses manches longues en font un indispensable des saisons froides. Disponible en gris chiné, compatible avec toutes les tenues.",
                'type'   => $tpHauts,
            ],
            [
                'nom'    => 'Veste en Jean Délavée',
                'ref'    => 'VES-JEA-003',
                'prix'   => '69.99',
                'stock'  => 45,
                'taille' => 'XS / S / M / L / XL',
                'image'  => 'veste-jean.png',
                'desc'   => "Veste en jean délavée à la coupe droite, idéale pour habiller ou casual-iser n'importe quelle tenue. Deux poches poitrines boutonnées, col classique, doublure légère. Référence intemporelle du vestiaire streetwear.",
                'type'   => $tpHauts,
            ],
            [
                'nom'    => 'Robe Fleurie Printemps',
                'ref'    => 'ROB-FLR-004',
                'prix'   => '54.99',
                'stock'  => 60,
                'taille' => 'XS / S / M / L',
                'image'  => 'robe-fleurie.png',
                'desc'   => "Robe légère à imprimé fleuri, parfaite pour la belle saison. Tissu fluide en viscose douce, col V, longueur mi-mollet. Ceinture fine amovible pour moduler la silhouette. Entretien délicat recommandé.",
                'type'   => $tpHauts,
            ],
            [
                'nom'    => 'Jean Slim Bleu Brut',
                'ref'    => 'JEA-SLM-005',
                'prix'   => '59.99',
                'stock'  => 120,
                'taille' => '36 / 38 / 40 / 42 / 44',
                'image'  => 'jean-slim-bleu.png',
                'desc'   => "Jean slim 5 poches en denim brut 98% coton 2% élasthane. Coupe ajustée qui épouse la silhouette sans la contraindre. Taille mi-haute, braguette à zip. Référence incontournable du dressing masculin et féminin.",
                'type'   => $tpPantalons,
            ],
            [
                'nom'    => 'Jogging Premium Noir',
                'ref'    => 'JOG-NOR-006',
                'prix'   => '39.99',
                'stock'  => 200,
                'taille' => 'S / M / L / XL / XXL',
                'image'  => 'jogging-noir.png',
                'desc'   => "Pantalon de jogging en molleton grattée ultra-doux, 80% coton 20% polyester. Ceinture élastiquée avec cordon, deux poches latérales, une poche arrière zippée. Idéal sport et détente.",
                'type'   => $tpPantalons,
            ],
            [
                'nom'    => 'Basket Blanche Classic',
                'ref'    => 'BAS-BLC-007',
                'prix'   => '89.99',
                'stock'  => 75,
                'taille' => '38 / 39 / 40 / 41 / 42 / 43 / 44',
                'image'  => 'basket-blanche.png',
                'desc'   => "Sneaker basse en cuir synthétique blanc avec semelle en caoutchouc vulcanisé. Design épuré et minimaliste, lacets plats, doublure textile respirante. Polyvalente et indémodable, s'associe avec toutes les tenues.",
                'type'   => $tpChaussures,
            ],
            [
                'nom'    => 'Sac à Dos Cuir Marron',
                'ref'    => 'SAC-CUI-008',
                'prix'   => '129.90',
                'stock'  => 30,
                'taille' => 'Taille unique',
                'image'  => 'sac-cuir-marron.png',
                'desc'   => "Sac à dos en cuir véritable pleine fleur, coloris cognac. Compartiment principal spacieux (env. 20L), poche frontale zippée, bretelles rembourrées réglables. Fermeture à boucle cuir. Contenance : ordinateur 15 pouces.",
                'type'   => $tpAccessoires,
            ],
            [
                'nom'    => 'Casquette Snapback Noire',
                'ref'    => 'CAS-NOR-009',
                'prix'   => '24.99',
                'stock'  => 0,
                'taille' => 'Taille unique (ajustable)',
                'image'  => 'casquette-noire.png',
                'desc'   => "Casquette snapback 6 panneaux en coton twill noir. Visière pré-courbée, fermeture réglable à clip plastique. Taille universelle. Broderie ton sur ton discrète sur la façade.",
                'type'   => $tpAccessoires,
            ],
            [
                'nom'    => 'Pack 5 Paires de Chaussettes',
                'ref'    => 'CHS-PCK-010',
                'prix'   => '14.99',
                'stock'  => 300,
                'taille' => '35-38 / 39-42 / 43-46',
                'image'  => 'chaussettes-pack.png',
                'desc'   => "Lot de 5 paires de chaussettes unies en coton mélangé (80% coton, 17% polyamide, 3% élasthane). Coloris assortis : noir ×2, blanc ×2, gris ×1. Renforts talon et pointe pour une durabilité accrue.",
                'type'   => $tpAccessoires,
            ],
        ];

        $produits = [];
        foreach ($produitsData as $data) {
            $p = new Produit();
            $p->setNomProd($data['nom']);
            $p->setRefProd($data['ref']);
            $p->setPrixProd($data['prix']);
            $p->setStockProd($data['stock']);
            $p->setTailleProd($data['taille']);
            $p->setImageProd($data['image']);
            $p->setDescProd($data['desc']);
            $p->setTypeProduit($data['type']);
            $manager->persist($p);
            $produits[] = $p;
        }

        // ── 4. MODES DE PAIEMENT ─────────────────────────────────────────────
        $payCart = new Paiement(); $payCart->setLibellePay('Carte bancaire');
        $payPP   = new Paiement(); $payPP->setLibellePay('PayPal');
        $payVir  = new Paiement(); $payVir->setLibellePay('Virement bancaire');

        $manager->persist($payCart);
        $manager->persist($payPP);
        $manager->persist($payVir);

        // ── 5. OPTIONS DE LIVRAISON ──────────────────────────────────────────
        $livStd = new Livraison();
        $livStd->setNomLivr('Colissimo Standard');
        $livStd->setChoixLivr('standard');
        $livStd->setDelaiLivr(5);
        $livStd->setFraisLivr('4.99');

        $livExp = new Livraison();
        $livExp->setNomLivr('Chronopost Express');
        $livExp->setChoixLivr('express');
        $livExp->setDelaiLivr(1);
        $livExp->setFraisLivr('9.99');

        $livGrat = new Livraison();
        $livGrat->setNomLivr('Livraison offerte');
        $livGrat->setChoixLivr('gratuite');
        $livGrat->setDelaiLivr(7);
        $livGrat->setFraisLivr('0.00');

        $manager->persist($livStd);
        $manager->persist($livExp);
        $manager->persist($livGrat);

        // ── 6. CLIENTS ───────────────────────────────────────────────────────
        $clientsData = [
            ['prenom' => 'Alice',   'nom' => 'Martin',   'mail' => 'alice.martin@email.com',   'tel' => '0612345678', 'type' => $tcParticulier,   'role' => ['ROLE_USER']],
            ['prenom' => 'Bob',     'nom' => 'Dupont',   'mail' => 'bob.dupont@email.com',     'tel' => '0623456789', 'type' => $tcParticulier,   'role' => ['ROLE_USER']],
            ['prenom' => 'Claire',  'nom' => 'Lefèvre',  'mail' => 'claire.lefevre@email.com', 'tel' => '0634567890', 'type' => $tcVIP,           'role' => ['ROLE_USER']],
            ['prenom' => 'David',   'nom' => 'Bernard',  'mail' => 'david.bernard@shop.fr',    'tel' => '0645678901', 'type' => $tcProfessionnel, 'role' => ['ROLE_USER']],
            ['prenom' => 'Emma',    'nom' => 'Rousseau', 'mail' => 'emma.rousseau@email.com',  'tel' => '0656789012', 'type' => $tcParticulier,   'role' => ['ROLE_USER']],
            ['prenom' => 'Admin',   'nom' => 'Shop',     'mail' => 'admin@boutique.fr',        'tel' => '0100000000', 'type' => $tcProfessionnel, 'role' => ['ROLE_USER', 'ROLE_ADMIN']],
        ];

        $clients = [];
        foreach ($clientsData as $data) {
            $c = new Client();
            $c->setPrenomCli($data['prenom']);
            $c->setNomCli($data['nom']);
            $c->setMailCli($data['mail']);
            $c->setTelCli($data['tel']);
            $c->setTypeClient($data['type']);
            $c->setRoles($data['role']);
            $c->setPassword($this->hasher->hashPassword($c, 'Password1!'));
            $c->setDateNaissanceCli(new \DateTime(sprintf('-%d years', rand(20, 50))));
            $manager->persist($c);
            $clients[] = $c;
        }

        // ── 7. ADRESSES ──────────────────────────────────────────────────────
        $adressesData = [
            [$clients[0], 'livraison',   '12 rue des Lilas',        'Paris',     '75011', 'France'],
            [$clients[0], 'facturation', '12 rue des Lilas',        'Paris',     '75011', 'France'],
            [$clients[1], 'livraison',   '34 avenue Victor Hugo',   'Lyon',      '69003', 'France'],
            [$clients[2], 'livraison',   '8 boulevard de la Mer',   'Marseille', '13008', 'France'],
            [$clients[3], 'livraison',   '56 rue du Commerce',      'Bordeaux',  '33000', 'France'],
            [$clients[4], 'livraison',   '2 impasse des Rossignols', 'Nantes',   '44000', 'France'],
        ];

        $adresses = [];
        foreach ($adressesData as [$client, $type, $rue, $ville, $cp, $pays]) {
            $a = new Adresse();
            $a->setClient($client);
            $a->setTypeAddr($type);
            $a->setRueAddr($rue);
            $a->setVilleAddr($ville);
            $a->setCpAddr($cp);
            $a->setPaysAddr($pays);
            $manager->persist($a);
            $adresses[] = $a;
        }

        // ── 8. AVIS ──────────────────────────────────────────────────────────
        $avisData = [
            // T-shirt blanc
            [$clients[0], $produits[0], 'Parfait pour l\'été', 5, 'Super qualité, coton doux et agréable. La coupe est bien et ne rétrécit pas au lavage. Je recommande vivement !'],
            [$clients[1], $produits[0], 'Bon rapport qualité-prix', 4, 'T-shirt correct pour le prix. Légèrement transparent en blanc mais rien d\'inhabituel pour ce type d\'article.'],
            [$clients[2], $produits[0], 'Excellent !', 5, 'Matière top, coupe nickel. J\'en ai commandé 3 tellement j\'adore. Livraison rapide en plus.'],

            // Pull oversize
            [$clients[0], $produits[1], 'Très confortable', 5, 'Pull incroyablement doux, parfait pour les soirées fraîches. La coupe oversize est exactement comme sur les photos.'],
            [$clients[3], $produits[1], 'Bien mais taille grand', 3, 'La qualité est là mais il faut prendre une taille en dessous. Un peu déçu de la couleur légèrement différente des photos.'],

            // Veste en jean
            [$clients[1], $produits[2], 'Style au rendez-vous', 5, 'Veste parfaite, l\'effet délavé est très beau en vrai. La coupe est flatteuse et le tissu de bonne qualité. Coup de cœur !'],
            [$clients[4], $produits[2], 'Bien mais coutures fragiles', 3, 'Jolie veste mais une couture a lâché après 2 semaines. Le SAV a été réactif heureusement.'],

            // Jean slim
            [$clients[2], $produits[4], 'Le jean parfait', 5, 'Enfin un jean slim qui s\'étire sans perdre sa forme ! Coupe impeccable, taille exacte. Je passe ma commande habituelle.'],
            [$clients[4], $produits[4], 'Très satisfaite', 4, 'Jean de qualité, belle couleur. Légèrement raide au début mais s\'assouplit vite après quelques lavages.'],

            // Jogging noir
            [$clients[0], $produits[5], 'Le jogging ultime', 5, 'J\'en cherchais un confortable pour le télétravail et les sessions sport. C\'est exactement ça. Molleton parfait, pas bouloché après 10 lavages.'],
            [$clients[3], $produits[5], 'Très bon', 4, 'Bon jogging, maintien correct et toucher agréable. La poche arrière zippée est très pratique.'],

            // Basket blanche
            [$clients[1], $produits[6], 'Les meilleures baskets blanches', 5, 'Design minimaliste au top, très confortables dès le premier jour. Se nettoient facilement. Je les porte avec tout.'],
            [$clients[2], $produits[6], 'Jolies mais manque de soutien', 3, 'Esthétiquement superbes mais le soutien de la voûte plantaire est insuffisant pour une utilisation intensive.'],

            // Sac à dos
            [$clients[4], $produits[7], 'Sac magnifique', 5, 'Le cuir est d\'une qualité exceptionnelle, l\'odeur est délicieuse. Suffisamment grand pour l\'université et le quotidien. Investissement durable.'],

            // Chaussettes
            [$clients[3], $produits[9], 'Pratique et qualitatif', 4, 'Bon pack de chaussettes, le coton est doux et elles tiennent bien en place. Le lot de 5 paires est très avantageux.'],
            [$clients[0], $produits[9], 'Rapport qualité-prix imbattable', 5, 'Pour ce prix, on a des chaussettes vraiment correctes qui ne filent pas. Je renouvelle ma commande chaque année.'],
        ];

        foreach ($avisData as [$client, $produit, $titre, $note, $msg]) {
            $avis = new Avis();
            $avis->setClient($client);
            $avis->setProduit($produit);
            $avis->setTitreAvis($titre);
            $avis->setNoteAvis($note);
            $avis->setMsgAvis($msg);
            // Simuler des dates variées (lifecycle callback sera ignoré ici car on force)
            $manager->persist($avis);
        }

        // ── 9. COMMANDES ─────────────────────────────────────────────────────

        // Commande 1 : Alice — livrée, payée
        $cmd1 = new Commande();
        $cmd1->setClient($clients[0]);
        $cmd1->setStatutCde('livree');
        $cmd1->setEstPayeCde(true);
        $cmd1->setLivraison($livStd);
        $cmd1->setPaiement($payCart);
        $cmd1->setAdresseLivraison($adresses[0]);

        $lc1a = new LigneCommande();
        $lc1a->setCommande($cmd1); $lc1a->setProduit($produits[0]); $lc1a->setQuantite(2); $lc1a->setReduction('0.00');
        $lc1b = new LigneCommande();
        $lc1b->setCommande($cmd1); $lc1b->setProduit($produits[5]); $lc1b->setQuantite(1); $lc1b->setReduction('5.00');

        $cmd1->setMontantCde(bcadd(bcmul('19.99', '2', 2), bcsub('39.99', '5.00', 2), 2));
        $manager->persist($cmd1); $manager->persist($lc1a); $manager->persist($lc1b);

        // Commande 2 : Bob — expédiée, payée
        $cmd2 = new Commande();
        $cmd2->setClient($clients[1]);
        $cmd2->setStatutCde('expediee');
        $cmd2->setEstPayeCde(true);
        $cmd2->setLivraison($livExp);
        $cmd2->setPaiement($payPP);
        $cmd2->setAdresseLivraison($adresses[2]);

        $lc2a = new LigneCommande();
        $lc2a->setCommande($cmd2); $lc2a->setProduit($produits[6]); $lc2a->setQuantite(1); $lc2a->setReduction('0.00');
        $cmd2->setMontantCde(bcadd('89.99', '9.99', 2));
        $manager->persist($cmd2); $manager->persist($lc2a);

        // Commande 3 : Claire — confirmée, payée
        $cmd3 = new Commande();
        $cmd3->setClient($clients[2]);
        $cmd3->setStatutCde('confirmee');
        $cmd3->setEstPayeCde(true);
        $cmd3->setLivraison($livGrat);
        $cmd3->setPaiement($payCart);
        $cmd3->setAdresseLivraison($adresses[3]);

        $lc3a = new LigneCommande();
        $lc3a->setCommande($cmd3); $lc3a->setProduit($produits[1]); $lc3a->setQuantite(1); $lc3a->setReduction('0.00');
        $lc3b = new LigneCommande();
        $lc3b->setCommande($cmd3); $lc3b->setProduit($produits[3]); $lc3b->setQuantite(1); $lc3b->setReduction('0.00');
        $cmd3->setMontantCde(bcadd('45.99', '54.99', 2));
        $manager->persist($cmd3); $manager->persist($lc3a); $manager->persist($lc3b);

        // Commande 4 : David — en attente, non payée
        $cmd4 = new Commande();
        $cmd4->setClient($clients[3]);
        $cmd4->setStatutCde('en_attente');
        $cmd4->setEstPayeCde(false);
        $cmd4->setLivraison($livStd);
        $cmd4->setPaiement($payVir);
        $cmd4->setAdresseLivraison($adresses[4]);

        $lc4a = new LigneCommande();
        $lc4a->setCommande($cmd4); $lc4a->setProduit($produits[7]); $lc4a->setQuantite(1); $lc4a->setReduction('10.00');
        $lc4b = new LigneCommande();
        $lc4b->setCommande($cmd4); $lc4b->setProduit($produits[9]); $lc4b->setQuantite(3); $lc4b->setReduction('0.00');
        $cmd4->setMontantCde(bcadd(bcsub('129.90', '10.00', 2), bcmul('14.99', '3', 2), 2));
        $manager->persist($cmd4); $manager->persist($lc4a); $manager->persist($lc4b);

        // Commande 5 : Emma — annulée
        $cmd5 = new Commande();
        $cmd5->setClient($clients[4]);
        $cmd5->setStatutCde('annulee');
        $cmd5->setEstPayeCde(false);
        $cmd5->setLivraison($livStd);
        $cmd5->setPaiement($payPP);
        $cmd5->setAdresseLivraison($adresses[5]);

        $lc5a = new LigneCommande();
        $lc5a->setCommande($cmd5); $lc5a->setProduit($produits[2]); $lc5a->setQuantite(1); $lc5a->setReduction('0.00');
        $cmd5->setMontantCde(bcadd('69.99', '4.99', 2));
        $manager->persist($cmd5); $manager->persist($lc5a);

        $manager->flush();

        echo "✓ Jeu d'essai chargé avec succès !\n";
        echo "  - 3 types de clients\n";
        echo "  - 4 catégories de produits\n";
        echo "  - 10 produits\n";
        echo "  - 6 clients (dont 1 admin)\n";
        echo "  - 16 avis\n";
        echo "  - 5 commandes\n";
        echo "\n  Connexion :\n";
        echo "  → Tous les comptes : mot de passe = Password1!\n";
        echo "  → Admin            : admin@boutique.fr\n";
        echo "  → Client VIP       : claire.lefevre@email.com\n";
    }
}
