PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE typeclient (
    IdTypeCli INTEGER PRIMARY KEY AUTOINCREMENT,
    NomTypeCli TEXT NOT NULL UNIQUE
);
INSERT INTO typeclient VALUES(1,'Utilisateur');
INSERT INTO typeclient VALUES(2,'Administrateur');
CREATE TABLE client (
    IdCli INTEGER PRIMARY KEY AUTOINCREMENT,
    IdTypeCli INTEGER NOT NULL,
    NomCli TEXT NOT NULL,
    PrenomCli TEXT NOT NULL,
    DateNaissanceCli TEXT NOT NULL,
    MailCli TEXT NOT NULL UNIQUE,
    MdpCli TEXT NOT NULL,
    FavoriCli TEXT DEFAULT '',
    TelCli TEXT DEFAULT '',
    FOREIGN KEY (IdTypeCli) REFERENCES typeclient (IdTypeCli)
);
INSERT INTO client VALUES(1,2,'Admin','Master','1995-06-14','admin@gamerdry.local','240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9','Slip couche Apex Premium','0102030405');
INSERT INTO client VALUES(2,1,'Player','Nova','2001-09-08','player@gamerdry.local','e41abfd6daf8ad3289f41e5ed0cfe8f5c705dbb40531efdf63e110118b7594f2','Calecon couche Stealth','0607080910');
CREATE TABLE adresse (
    IdAddr INTEGER PRIMARY KEY AUTOINCREMENT,
    TypeAddr TEXT NOT NULL,
    RueAddr TEXT NOT NULL,
    VilleAddr TEXT NOT NULL,
    CPAddr TEXT NOT NULL,
    PaysAddr TEXT NOT NULL
);
INSERT INTO adresse VALUES(1,'Domicile','12 rue des Arcades','Paris','75011','France');
INSERT INTO adresse VALUES(2,'Livraison','4 avenue du Pixel','Lyon','69003','France');
CREATE TABLE possede (
    IdCli INTEGER NOT NULL,
    IdAddr INTEGER NOT NULL,
    PRIMARY KEY (IdCli, IdAddr),
    FOREIGN KEY (IdCli) REFERENCES client (IdCli) ON DELETE CASCADE,
    FOREIGN KEY (IdAddr) REFERENCES adresse (IdAddr) ON DELETE CASCADE
);
INSERT INTO possede VALUES(2,1);
INSERT INTO possede VALUES(2,2);
CREATE TABLE paiement (
    IdPay INTEGER PRIMARY KEY AUTOINCREMENT,
    LibellePay TEXT NOT NULL UNIQUE
);
INSERT INTO paiement VALUES(1,'Carte bancaire');
INSERT INTO paiement VALUES(2,'PayPal');
INSERT INTO paiement VALUES(3,'Apple Pay');
INSERT INTO paiement VALUES(4,'Google Pay');
CREATE TABLE typeproduits (
    IdTypeProd INTEGER PRIMARY KEY AUTOINCREMENT,
    NomTypeProd TEXT NOT NULL UNIQUE
);
INSERT INTO typeproduits VALUES(1,'Slip couche');
INSERT INTO typeproduits VALUES(2,'Calecon couche');
INSERT INTO typeproduits VALUES(3,'Legging couche');
CREATE TABLE produits (
    IdProd INTEGER PRIMARY KEY AUTOINCREMENT,
    IdTypeProd INTEGER NOT NULL,
    NomProd TEXT NOT NULL,
    PrixProd REAL NOT NULL,
    StockProd INTEGER NOT NULL,
    ImageProd TEXT NOT NULL,
    TailleProd TEXT NOT NULL,
    RefProd TEXT NOT NULL UNIQUE,
    DescProd TEXT NOT NULL,
    CouleurProd TEXT NOT NULL,
    GammeProd TEXT NOT NULL,
    AbsorptionProd TEXT NOT NULL,
    UsageProd TEXT NOT NULL,
    PointsFortsProd TEXT NOT NULL,
    BadgeProd TEXT NOT NULL DEFAULT '',
    FOREIGN KEY (IdTypeProd) REFERENCES typeproduits (IdTypeProd)
);
INSERT INTO produits VALUES(1,1,'Slip couche Apex Core Noir',29.89999999999999858,48,'assets/men-slip-core.svg','M/L','MEN-SC-NR-01','Sous-vetement absorbant pour longues sessions gaming, avec coeur multicouche, coupe ajustee et maintien discret sous un jogging ou un pantalon de stream.','Noir carbone','Core','6h','Jour et soiree','Ceinture souple|Absorption discrete|Toucher sec','Best-seller');
INSERT INTO produits VALUES(2,1,'Slip couche Apex Core Glacier',29.89999999999999858,41,'assets/men-slip-core.svg','M/L','MEN-SC-GL-02','Version claire et respirante du slip couche Apex avec interieur anti-fuite, finition douce et coupe stable pour les parties longues ou le teletravail.','Bleu glacier','Core','6h','Jour et soiree','Canaux anti-fuite|Voile respirant|Forme ergonomique','');
INSERT INTO produits VALUES(3,1,'Slip couche Apex Premium Rouge',39.89999999999999858,32,'assets/men-slip-premium.svg','L/XL','MEN-SC-RG-03','Edition premium inspiree des setups RGB, avec absorption renforcee, maintien plus ferme et textile interieur ultra doux pour les nuits LAN ou les trajets.','Rouge pulse','Premium','8h','Nuit et marathon','Toucher premium|Controle des odeurs|Maintien renforce','Premium');
INSERT INTO produits VALUES(4,1,'Slip couche Apex Premium Sable',39.89999999999999858,28,'assets/men-slip-premium.svg','L/XL','MEN-SC-SB-04','Slip couche premium discret sous un pantalon ample, pense pour les joueurs qui veulent du confort longue duree sans sacrifier la sensation textile.','Sable doux','Premium','8h','Nuit et marathon','Finition textile|Coutures plates|Absorption prolongee','');
INSERT INTO produits VALUES(5,2,'Calecon couche Stealth Core Onyx',34.89999999999999858,44,'assets/men-boxer-core.svg','L','MEN-CC-OX-05','Calecon couche taille haute pour sessions FPS, avec maintien plus enveloppant sur les cuisses et noyau absorbant adapte aux longues assises.','Onyx','Core','7h','Jour et gaming assis','Coupe boxer|Protection laterale|Sensation seche','Nouveau');
INSERT INTO produits VALUES(6,2,'Calecon couche Stealth Core Ice',34.89999999999999858,36,'assets/men-boxer-core.svg','L','MEN-CC-IC-06','Version claire du calecon couche Stealth avec maintien stable, interieur moelleux et hauteur de taille rassurante pour le gaming et les trajets.','Ice blue','Core','7h','Jour et gaming assis','Calecon discret|Doublure souple|Maintien confortable','');
INSERT INTO produits VALUES(7,2,'Calecon couche Stealth Premium Ember',44.89999999999999858,22,'assets/men-boxer-premium.svg','XL','MEN-CC-EM-07','Modele premium pour marathons competitifs et nuits blanches, avec couche interne plus epaisse, elastiques plats et absorption haute capacite.','Ember red','Premium','10h','Nuit, voyage, marathon','Absorption haute capacite|Ceinture confort|Controle des odeurs','Top confort');
INSERT INTO produits VALUES(8,2,'Calecon couche Stealth Premium Frost',44.89999999999999858,18,'assets/men-boxer-premium.svg','XL','MEN-CC-FR-08','Calecon absorbant premium inspire des sous-vetements sport, avec structure stable et maintien sec sur de tres longues periodes.','Frost','Premium','10h','Nuit, voyage, marathon','Canal anti-humidite|Matiere premium|Protection cuisses','');
INSERT INTO produits VALUES(9,3,'Legging couche Flux Core Midnight',49.89999999999999858,24,'assets/men-legging-core.svg','M/L','MEN-LG-MD-09','Legging absorbant integre pour streamer assis longtemps, avec maintien des jambes, coeur renforce au bassin et tombee sportive.','Midnight','Core','6h','Streaming et setup maison','Effet gainant|Maintien bassin|Silhouette sportive','Edition sport');
INSERT INTO produits VALUES(10,3,'Legging couche Flux Core Neon',49.89999999999999858,26,'assets/men-legging-core.svg','M/L','MEN-LG-NE-10','Legging couche moderne avec empiecements visuels gaming, maintien dynamique et confort adapte aux longues sessions assises ou debout.','Neon black','Core','6h','Streaming et setup maison','Style gaming|Compression douce|Absorption integree','');
INSERT INTO produits VALUES(11,3,'Legging couche Flux Premium Aurora',59.89999999999999858,17,'assets/men-legging-premium.svg','L/XL','MEN-LG-AU-11','Version premium du legging couche avec panneau absorbant allonge, maintien plus ferme et sensation textile plus dense pour les grosses sessions.','Aurora blue','Premium','8h','Streaming, voyage, nuit','Panneau allonge|Compression premium|Toucher seconde peau','Premium');
INSERT INTO produits VALUES(12,3,'Legging couche Flux Premium Carbon',59.89999999999999858,14,'assets/men-legging-premium.svg','L/XL','MEN-LG-CB-12','Legging absorbant premium au look sobre, concu pour celles et ceux qui veulent une base textile rassurante sans sacrifier l esthetique du setup.','Carbon','Premium','8h','Streaming, voyage, nuit','Look sobre|Absorption longue duree|Maintien bas du dos','');
CREATE TABLE commande (
    IdCde INTEGER PRIMARY KEY AUTOINCREMENT,
    IdPay INTEGER NOT NULL,
    IdCli INTEGER NOT NULL,
    StatutCde TEXT NOT NULL,
    MontantCde REAL NOT NULL,
    EstPayeCde INTEGER NOT NULL CHECK (EstPayeCde IN (0, 1)),
    DateCde TEXT NOT NULL DEFAULT (DATE('now')),
    FOREIGN KEY (IdPay) REFERENCES paiement (IdPay),
    FOREIGN KEY (IdCli) REFERENCES client (IdCli)
);
INSERT INTO commande VALUES(1,1,2,'Expediee',83.70000000000000284,1,'2026-03-28');
INSERT INTO commande VALUES(2,2,2,'Livree',64.79999999999999716,1,'2026-03-21');
CREATE TABLE lignecommande (
    IdCde INTEGER NOT NULL,
    IdProd INTEGER NOT NULL,
    Reduction REAL NOT NULL DEFAULT 0,
    Quantite INTEGER NOT NULL,
    PRIMARY KEY (IdCde, IdProd),
    FOREIGN KEY (IdCde) REFERENCES commande (IdCde) ON DELETE CASCADE,
    FOREIGN KEY (IdProd) REFERENCES produits (IdProd)
);
INSERT INTO lignecommande VALUES(1,1,0.0,1);
INSERT INTO lignecommande VALUES(1,5,0.0,1);
INSERT INTO lignecommande VALUES(1,9,0.0,1);
INSERT INTO lignecommande VALUES(2,3,0.0,1);
INSERT INTO lignecommande VALUES(2,6,0.0,1);
CREATE TABLE livraison (
    IdLivr INTEGER PRIMARY KEY AUTOINCREMENT,
    IdAddr INTEGER NOT NULL,
    IdCde INTEGER NOT NULL UNIQUE,
    NomLivr TEXT NOT NULL,
    ChoixLivr TEXT NOT NULL,
    DelaiLivr TEXT NOT NULL,
    FraisLivr REAL NOT NULL,
    DateLivr TEXT NOT NULL,
    FOREIGN KEY (IdAddr) REFERENCES adresse (IdAddr),
    FOREIGN KEY (IdCde) REFERENCES commande (IdCde) ON DELETE CASCADE
);
INSERT INTO livraison VALUES(1,2,1,'MENstruation Express','Express 24h','24h',8.900000000000000355,'2026-03-29');
INSERT INTO livraison VALUES(2,1,2,'MENstruation Standard','Standard 72h','72h',4.900000000000000355,'2026-03-24');
CREATE TABLE transactionpaiement (
    IdTransac INTEGER PRIMARY KEY AUTOINCREMENT,
    IdCde INTEGER NOT NULL UNIQUE,
    StatutTransac TEXT NOT NULL,
    MontantTransac REAL NOT NULL,
    DeviseTransac TEXT NOT NULL DEFAULT 'EUR',
    ReferenceTransac TEXT NOT NULL UNIQUE,
    PorteurTransac TEXT NOT NULL,
    MarqueTransac TEXT NOT NULL,
    MasqueTransac TEXT NOT NULL,
    QuatreDerniersTransac TEXT NOT NULL DEFAULT '',
    CreeLeTransac TEXT NOT NULL DEFAULT (DATETIME('now')),
    FOREIGN KEY (IdCde) REFERENCES commande (IdCde) ON DELETE CASCADE
);
CREATE TABLE avis (
    IdAvis INTEGER PRIMARY KEY AUTOINCREMENT,
    IdProd INTEGER NOT NULL,
    IdCli INTEGER NOT NULL,
    TitreAvis TEXT NOT NULL,
    MsgAvis TEXT NOT NULL,
    NoteAvis INTEGER NOT NULL CHECK (NoteAvis BETWEEN 1 AND 5),
    DateAvis TEXT NOT NULL,
    FOREIGN KEY (IdProd) REFERENCES produits (IdProd) ON DELETE CASCADE,
    FOREIGN KEY (IdCli) REFERENCES client (IdCli) ON DELETE CASCADE
);
INSERT INTO avis VALUES(1,5,2,'Enfin un vrai maintien','Le calecon couche tient bien pendant les longues ranked et je n ai pas eu besoin de me lever pendant ma session du soir.',5,'2026-03-27');
INSERT INTO avis VALUES(2,3,2,'Plus premium que prevu','La finition est nettement meilleure, la sensation est plus douce et la coupe reste stable meme sur une longue nuit de jeu.',4,'2026-03-25');
INSERT INTO avis VALUES(3,9,2,'Bonne surprise sur le legging','Le legging reste confortable assis longtemps et l effet visuel fait vraiment plus produit lifestyle que medical.',5,'2026-03-29');
DELETE FROM sqlite_sequence;
INSERT INTO sqlite_sequence VALUES('typeclient',2);
INSERT INTO sqlite_sequence VALUES('paiement',4);
INSERT INTO sqlite_sequence VALUES('typeproduits',3);
INSERT INTO sqlite_sequence VALUES('client',2);
INSERT INTO sqlite_sequence VALUES('adresse',2);
INSERT INTO sqlite_sequence VALUES('produits',12);
INSERT INTO sqlite_sequence VALUES('commande',2);
INSERT INTO sqlite_sequence VALUES('livraison',2);
INSERT INTO sqlite_sequence VALUES('avis',3);
COMMIT;
