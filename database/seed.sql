INSERT OR IGNORE INTO typeclient (IdTypeCli, NomTypeCli) VALUES
    (1, 'Utilisateur'),
    (2, 'Administrateur');

INSERT OR IGNORE INTO paiement (IdPay, LibellePay) VALUES
    (1, 'Carte bancaire'),
    (2, 'PayPal'),
    (3, 'Apple Pay'),
    (4, 'Google Pay');

INSERT OR IGNORE INTO typeproduits (IdTypeProd, NomTypeProd) VALUES
    (1, 'Slip couche'),
    (2, 'Calecon couche'),
    (3, 'Legging couche');

INSERT OR IGNORE INTO client (
    IdCli, IdTypeCli, NomCli, PrenomCli, DateNaissanceCli, MailCli, MdpCli, FavoriCli, TelCli
) VALUES
    (
        1, 2, 'Admin', 'Master', '1995-06-14', 'admin@gamerdry.local',
        '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9',
        'Slip couche Apex Premium', '0102030405'
    ),
    (
        2, 1, 'Player', 'Nova', '2001-09-08', 'player@gamerdry.local',
        'e41abfd6daf8ad3289f41e5ed0cfe8f5c705dbb40531efdf63e110118b7594f2',
        'Calecon couche Stealth', '0607080910'
    );

INSERT OR IGNORE INTO adresse (IdAddr, TypeAddr, RueAddr, VilleAddr, CPAddr, PaysAddr) VALUES
    (1, 'Domicile', '12 rue des Arcades', 'Paris', '75011', 'France'),
    (2, 'Livraison', '4 avenue du Pixel', 'Lyon', '69003', 'France');

INSERT OR IGNORE INTO possede (IdCli, IdAddr) VALUES
    (2, 1),
    (2, 2);

INSERT OR IGNORE INTO produits (
    IdProd, IdTypeProd, NomProd, PrixProd, StockProd, ImageProd, TailleProd, RefProd, DescProd,
    CouleurProd, GammeProd, AbsorptionProd, UsageProd, PointsFortsProd, BadgeProd
) VALUES
    (
        1, 1, 'Slip couche Apex Core Noir', 29.90, 48,
        'assets/men-slip-core.svg',
        'M/L', 'MEN-SC-NR-01',
        'Sous-vetement absorbant pour longues sessions gaming, avec coeur multicouche, coupe ajustee et maintien discret sous un jogging ou un pantalon de stream.',
        'Noir carbone', 'Core', '6h', 'Jour et soiree', 'Ceinture souple|Absorption discrete|Toucher sec', 'Best-seller'
    ),
    (
        2, 1, 'Slip couche Apex Core Glacier', 29.90, 41,
        'assets/men-slip-core.svg',
        'M/L', 'MEN-SC-GL-02',
        'Version claire et respirante du slip couche Apex avec interieur anti-fuite, finition douce et coupe stable pour les parties longues ou le teletravail.',
        'Bleu glacier', 'Core', '6h', 'Jour et soiree', 'Canaux anti-fuite|Voile respirant|Forme ergonomique', ''
    ),
    (
        3, 1, 'Slip couche Apex Premium Rouge', 39.90, 32,
        'assets/men-slip-premium.svg',
        'L/XL', 'MEN-SC-RG-03',
        'Edition premium inspiree des setups RGB, avec absorption renforcee, maintien plus ferme et textile interieur ultra doux pour les nuits LAN ou les trajets.',
        'Rouge pulse', 'Premium', '8h', 'Nuit et marathon', 'Toucher premium|Controle des odeurs|Maintien renforce', 'Premium'
    ),
    (
        4, 1, 'Slip couche Apex Premium Sable', 39.90, 28,
        'assets/men-slip-premium.svg',
        'L/XL', 'MEN-SC-SB-04',
        'Slip couche premium discret sous un pantalon ample, pense pour les joueurs qui veulent du confort longue duree sans sacrifier la sensation textile.',
        'Sable doux', 'Premium', '8h', 'Nuit et marathon', 'Finition textile|Coutures plates|Absorption prolongee', ''
    ),
    (
        5, 2, 'Calecon couche Stealth Core Onyx', 34.90, 44,
        'assets/men-boxer-core.svg',
        'L', 'MEN-CC-OX-05',
        'Calecon couche taille haute pour sessions FPS, avec maintien plus enveloppant sur les cuisses et noyau absorbant adapte aux longues assises.',
        'Onyx', 'Core', '7h', 'Jour et gaming assis', 'Coupe boxer|Protection laterale|Sensation seche', 'Nouveau'
    ),
    (
        6, 2, 'Calecon couche Stealth Core Ice', 34.90, 36,
        'assets/men-boxer-core.svg',
        'L', 'MEN-CC-IC-06',
        'Version claire du calecon couche Stealth avec maintien stable, interieur moelleux et hauteur de taille rassurante pour le gaming et les trajets.',
        'Ice blue', 'Core', '7h', 'Jour et gaming assis', 'Calecon discret|Doublure souple|Maintien confortable', ''
    ),
    (
        7, 2, 'Calecon couche Stealth Premium Ember', 44.90, 22,
        'assets/men-boxer-premium.svg',
        'XL', 'MEN-CC-EM-07',
        'Modele premium pour marathons competitifs et nuits blanches, avec couche interne plus epaisse, elastiques plats et absorption haute capacite.',
        'Ember red', 'Premium', '10h', 'Nuit, voyage, marathon', 'Absorption haute capacite|Ceinture confort|Controle des odeurs', 'Top confort'
    ),
    (
        8, 2, 'Calecon couche Stealth Premium Frost', 44.90, 18,
        'assets/men-boxer-premium.svg',
        'XL', 'MEN-CC-FR-08',
        'Calecon absorbant premium inspire des sous-vetements sport, avec structure stable et maintien sec sur de tres longues periodes.',
        'Frost', 'Premium', '10h', 'Nuit, voyage, marathon', 'Canal anti-humidite|Matiere premium|Protection cuisses', ''
    ),
    (
        9, 3, 'Legging couche Flux Core Midnight', 49.90, 24,
        'assets/men-legging-core.svg',
        'M/L', 'MEN-LG-MD-09',
        'Legging absorbant integre pour streamer assis longtemps, avec maintien des jambes, coeur renforce au bassin et tombee sportive.',
        'Midnight', 'Core', '6h', 'Streaming et setup maison', 'Effet gainant|Maintien bassin|Silhouette sportive', 'Edition sport'
    ),
    (
        10, 3, 'Legging couche Flux Core Neon', 49.90, 26,
        'assets/men-legging-core.svg',
        'M/L', 'MEN-LG-NE-10',
        'Legging couche moderne avec empiecements visuels gaming, maintien dynamique et confort adapte aux longues sessions assises ou debout.',
        'Neon black', 'Core', '6h', 'Streaming et setup maison', 'Style gaming|Compression douce|Absorption integree', ''
    ),
    (
        11, 3, 'Legging couche Flux Premium Aurora', 59.90, 17,
        'assets/men-legging-premium.svg',
        'L/XL', 'MEN-LG-AU-11',
        'Version premium du legging couche avec panneau absorbant allonge, maintien plus ferme et sensation textile plus dense pour les grosses sessions.',
        'Aurora blue', 'Premium', '8h', 'Streaming, voyage, nuit', 'Panneau allonge|Compression premium|Toucher seconde peau', 'Premium'
    ),
    (
        12, 3, 'Legging couche Flux Premium Carbon', 59.90, 14,
        'assets/men-legging-premium.svg',
        'L/XL', 'MEN-LG-CB-12',
        'Legging absorbant premium au look sobre, concu pour celles et ceux qui veulent une base textile rassurante sans sacrifier l esthetique du setup.',
        'Carbon', 'Premium', '8h', 'Streaming, voyage, nuit', 'Look sobre|Absorption longue duree|Maintien bas du dos', ''
    );

INSERT OR IGNORE INTO commande (
    IdCde, IdPay, IdCli, StatutCde, MontantCde, EstPayeCde, DateCde
) VALUES
    (1, 1, 2, 'Expediee', 83.70, 1, '2026-03-28'),
    (2, 2, 2, 'Livree', 64.80, 1, '2026-03-21');

INSERT OR IGNORE INTO lignecommande (IdCde, IdProd, Reduction, Quantite) VALUES
    (1, 1, 0, 1),
    (1, 5, 0, 1),
    (1, 9, 0, 1),
    (2, 3, 0, 1),
    (2, 6, 0, 1);

INSERT OR IGNORE INTO livraison (
    IdLivr, IdAddr, IdCde, NomLivr, ChoixLivr, DelaiLivr, FraisLivr, DateLivr
) VALUES
    (1, 2, 1, 'MENstruation Express', 'Express 24h', '24h', 8.90, '2026-03-29'),
    (2, 1, 2, 'MENstruation Standard', 'Standard 72h', '72h', 4.90, '2026-03-24');

INSERT OR IGNORE INTO avis (
    IdAvis, IdProd, IdCli, TitreAvis, MsgAvis, NoteAvis, DateAvis
) VALUES
    (
        1, 5, 2, 'Enfin un vrai maintien',
        'Le calecon couche tient bien pendant les longues ranked et je n ai pas eu besoin de me lever pendant ma session du soir.',
        5, '2026-03-27'
    ),
    (
        2, 3, 2, 'Plus premium que prevu',
        'La finition est nettement meilleure, la sensation est plus douce et la coupe reste stable meme sur une longue nuit de jeu.',
        4, '2026-03-25'
    ),
    (
        3, 9, 2, 'Bonne surprise sur le legging',
        'Le legging reste confortable assis longtemps et l effet visuel fait vraiment plus produit lifestyle que medical.',
        5, '2026-03-29'
    );

UPDATE produits SET ImageProd = 'assets/men-slip-core.svg', RefProd = 'MEN-SC-NR-01' WHERE IdProd = 1;
UPDATE produits SET ImageProd = 'assets/men-slip-core.svg', RefProd = 'MEN-SC-GL-02' WHERE IdProd = 2;
UPDATE produits SET ImageProd = 'assets/men-slip-premium.svg', RefProd = 'MEN-SC-RG-03' WHERE IdProd = 3;
UPDATE produits SET ImageProd = 'assets/men-slip-premium.svg', RefProd = 'MEN-SC-SB-04' WHERE IdProd = 4;
UPDATE produits SET ImageProd = 'assets/men-boxer-core.svg', RefProd = 'MEN-CC-OX-05' WHERE IdProd = 5;
UPDATE produits SET ImageProd = 'assets/men-boxer-core.svg', RefProd = 'MEN-CC-IC-06' WHERE IdProd = 6;
UPDATE produits SET ImageProd = 'assets/men-boxer-premium.svg', RefProd = 'MEN-CC-EM-07' WHERE IdProd = 7;
UPDATE produits SET ImageProd = 'assets/men-boxer-premium.svg', RefProd = 'MEN-CC-FR-08' WHERE IdProd = 8;
UPDATE produits SET ImageProd = 'assets/men-legging-core.svg', RefProd = 'MEN-LG-MD-09' WHERE IdProd = 9;
UPDATE produits SET ImageProd = 'assets/men-legging-core.svg', RefProd = 'MEN-LG-NE-10' WHERE IdProd = 10;
UPDATE produits SET ImageProd = 'assets/men-legging-premium.svg', RefProd = 'MEN-LG-AU-11' WHERE IdProd = 11;
UPDATE produits SET ImageProd = 'assets/men-legging-premium.svg', RefProd = 'MEN-LG-CB-12' WHERE IdProd = 12;

UPDATE livraison SET NomLivr = 'MENstruation Express' WHERE IdLivr = 1;
UPDATE livraison SET NomLivr = 'MENstruation Standard' WHERE IdLivr = 2;
