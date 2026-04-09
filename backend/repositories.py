from __future__ import annotations

import hashlib
import re
import sqlite3
from calendar import monthrange
from dataclasses import dataclass, field
from datetime import date, datetime, timedelta
from typing import Any

from backend.database import get_connection


def hash_password(password: str) -> str:
    return hashlib.sha256(password.encode("utf-8")).hexdigest()


def row_to_dict(row: sqlite3.Row | None) -> dict[str, Any] | None:
    if row is None:
        return None
    return {key: row[key] for key in row.keys()}


def parse_points(value: str | None) -> list[str]:
    if not value:
        return []
    return [item.strip() for item in value.split("|") if item.strip()]


PRODUCT_SELECT = """
SELECT p.IdProd, p.IdTypeProd, p.NomProd, p.PrixProd, p.StockProd, p.ImageProd,
       p.TailleProd, p.RefProd, p.DescProd, p.CouleurProd, p.GammeProd,
       p.AbsorptionProd, p.UsageProd, p.PointsFortsProd, p.BadgeProd,
       tp.NomTypeProd,
       COALESCE(AVG(a.NoteAvis), 0) AS NoteMoyenne,
       COUNT(a.IdAvis) AS NombreAvis
FROM produits p
JOIN typeproduits tp ON tp.IdTypeProd = p.IdTypeProd
LEFT JOIN avis a ON a.IdProd = p.IdProd
"""


def enrich_product(product: dict[str, Any] | None) -> dict[str, Any] | None:
    if product is None:
        return None
    product["PointsForts"] = parse_points(product.get("PointsFortsProd"))
    product["IsPremium"] = product.get("GammeProd") == "Premium"
    product["InStock"] = int(product.get("StockProd", 0)) > 0
    return product


def build_tracking_steps(order: dict[str, Any]) -> list[dict[str, Any]]:
    if not int(order.get("EstPayeCde", 0)):
        return []

    status = (order.get("StatutCde") or "").lower()
    delivery_date = order.get("DateLivr")
    today = date.today().isoformat()

    current_step = 1
    if status == "en preparation":
        current_step = 2
    elif status == "expediee":
        current_step = 4 if delivery_date and delivery_date > today else 3
    elif status == "livree":
        current_step = 5

    steps = [
        {
            "label": "Paiement confirme",
            "description": "Ton paiement a bien ete valide.",
            "state": "done",
        },
        {
            "label": "Commande preparee",
            "description": "Les produits sont en cours de preparation en atelier.",
            "state": "done" if current_step >= 2 else "todo",
        },
        {
            "label": "Commande expediee",
            "description": f"Transporteur: {order.get('NomLivr') or 'MENstruation Logistics'}.",
            "state": "done" if current_step >= 3 else "todo",
        },
        {
            "label": "Livraison en cours",
            "description": (
                f"Arrivee estimee le {delivery_date}."
                if delivery_date
                else "Le colis est en route vers ton adresse."
            ),
            "state": "done" if current_step >= 4 else "todo",
        },
        {
            "label": "Arrivee chez toi",
            "description": (
                f"Commande livree le {delivery_date}."
                if delivery_date
                else "La commande sera marquee recue une fois livree."
            ),
            "state": "done" if current_step >= 5 else "todo",
        },
    ]

    for index, step in enumerate(steps, start=1):
        if index == current_step and current_step < 5:
            step["state"] = "current"

    return steps


DELIVERY_OPTIONS = {
    "Standard 72h": {
        "carrier": "MENstruation Standard",
        "delay": "72h",
        "fee": 4.9,
        "min_days": 3,
        "max_days": 10,
    },
    "Express 24h": {
        "carrier": "MENstruation Express",
        "delay": "24h",
        "fee": 8.9,
        "min_days": 1,
        "max_days": 5,
    },
}


def require_non_empty(value: Any, message: str) -> str:
    if value is None:
        raise ValueError(message)
    normalized = str(value).strip()
    if not normalized:
        raise ValueError(message)
    return normalized


def normalize_quantity(value: Any) -> int:
    quantity = int(value)
    if quantity <= 0:
        raise ValueError("Chaque produit doit avoir une quantite positive.")
    if quantity > 10:
        raise ValueError("Une meme ligne de commande ne peut pas depasser 10 unites.")
    return quantity


def validate_postal_code(postal_code: str, country: str) -> str:
    compact = postal_code.replace(" ", "")
    if country.lower() == "france":
        if not re.fullmatch(r"\d{5}", compact):
            raise ValueError("Le code postal doit contenir 5 chiffres pour la France.")
        return compact
    if not re.fullmatch(r"[A-Za-z0-9\- ]{4,10}", postal_code):
        raise ValueError("Le code postal saisi est invalide.")
    return postal_code


def validate_delivery(delivery: dict[str, Any]) -> dict[str, Any]:
    choice = require_non_empty(delivery.get("ChoixLivr"), "Le mode de livraison est requis.")
    option = DELIVERY_OPTIONS.get(choice)
    if option is None:
        raise ValueError("Le mode de livraison choisi est invalide.")

    selected_date_raw = require_non_empty(
        delivery.get("DateLivr"),
        "La date de livraison souhaitee est requise.",
    )
    try:
        selected_date = datetime.strptime(selected_date_raw, "%Y-%m-%d").date()
    except ValueError as error:
        raise ValueError("La date de livraison est invalide.") from error

    today = date.today()
    min_date = today + timedelta(days=option["min_days"])
    max_date = today + timedelta(days=option["max_days"])
    if selected_date < min_date:
        raise ValueError(
            f"La premiere date disponible pour {choice} est le {min_date.isoformat()}."
        )
    if selected_date > max_date:
        raise ValueError(
            f"La date de livraison doit etre avant le {max_date.isoformat()} pour {choice}."
        )

    return {
        "NomLivr": option["carrier"],
        "ChoixLivr": choice,
        "DelaiLivr": option["delay"],
        "FraisLivr": option["fee"],
        "DateLivr": selected_date.isoformat(),
    }


def digits_only(value: Any) -> str:
    return "".join(character for character in str(value or "") if character.isdigit())


def is_valid_luhn(card_number: str) -> bool:
    checksum = 0
    parity = len(card_number) % 2
    for index, digit in enumerate(card_number):
        value = int(digit)
        if index % 2 == parity:
            value *= 2
            if value > 9:
                value -= 9
        checksum += value
    return checksum % 10 == 0


def infer_card_brand(card_number: str) -> str:
    if re.match(r"^4\d{12}(\d{3})?(\d{3})?$", card_number):
        return "Visa"
    if re.match(r"^(5[1-5]\d{14}|2(2[2-9]\d{12}|[3-6]\d{13}|7([01]\d{12}|20\d{12})))$", card_number):
        return "Mastercard"
    if re.match(r"^3[47]\d{13}$", card_number):
        return "American Express"
    if re.match(r"^6(?:011|5\d{2})\d{12}$", card_number):
        return "Discover"
    return "Carte"


def validate_card_payment(payment_data: dict[str, Any]) -> dict[str, str]:
    holder = require_non_empty(
        payment_data.get("cardholderName"),
        "Le nom du titulaire de la carte est requis.",
    )
    if len(holder) < 4:
        raise ValueError("Le nom du titulaire de la carte est trop court.")

    card_number = digits_only(payment_data.get("cardNumber"))
    if len(card_number) < 13 or len(card_number) > 19:
        raise ValueError("Le numero de carte bancaire doit contenir entre 13 et 19 chiffres.")
    if not is_valid_luhn(card_number):
        raise ValueError("Le numero de carte bancaire est invalide.")

    expiry_month = int(require_non_empty(payment_data.get("expiryMonth"), "Le mois d expiration est requis."))
    expiry_year = int(require_non_empty(payment_data.get("expiryYear"), "L annee d expiration est requise."))
    if expiry_month < 1 or expiry_month > 12:
        raise ValueError("Le mois d expiration est invalide.")

    now = datetime.now()
    last_day = monthrange(expiry_year, expiry_month)[1]
    expiry_date = date(expiry_year, expiry_month, last_day)
    if expiry_date < now.date():
        raise ValueError("La carte bancaire est expiree.")
    if expiry_year > now.year + 15:
        raise ValueError("L annee d expiration de la carte est invalide.")

    cvv = digits_only(payment_data.get("cvv"))
    card_brand = infer_card_brand(card_number)
    expected_cvv_lengths = {4} if card_brand == "American Express" else {3, 4}
    if len(cvv) not in expected_cvv_lengths:
        raise ValueError("Le cryptogramme visuel est invalide.")

    return {
        "brand": card_brand,
        "holder": holder,
        "last4": card_number[-4:],
        "masked": f"{card_number[:4]} **** **** {card_number[-4:]}",
    }


def build_payment_audit(payment_method: str, payment_data: dict[str, Any]) -> dict[str, str]:
    method = payment_method.lower()
    if method == "carte bancaire":
        return validate_card_payment(payment_data)
    if method == "paypal":
        email = require_non_empty(payment_data.get("walletEmail"), "L email PayPal est requis.")
        if "@" not in email:
            raise ValueError("L email PayPal est invalide.")
        return {
            "brand": "PayPal",
            "holder": email,
            "last4": "",
            "masked": email,
        }
    if method in {"apple pay", "google pay"}:
        device = require_non_empty(
            payment_data.get("walletDevice"),
            f"Un appareil {payment_method} doit etre indique.",
        )
        return {
            "brand": payment_method,
            "holder": device,
            "last4": "",
            "masked": device,
        }
    raise ValueError("La methode de paiement est invalide.")


@dataclass
class AuthRepository:
    def login(self, email: str, password: str) -> dict[str, Any] | None:
        query = """
        SELECT c.IdCli, c.NomCli, c.PrenomCli, c.MailCli, c.FavoriCli, c.TelCli,
               tc.NomTypeCli AS Role
        FROM client c
        JOIN typeclient tc ON tc.IdTypeCli = c.IdTypeCli
        WHERE c.MailCli = ? AND c.MdpCli = ?
        """
        with get_connection() as connection:
            row = connection.execute(query, (email, hash_password(password))).fetchone()
            return row_to_dict(row)

    def register(self, payload: dict[str, Any]) -> dict[str, Any]:
        with get_connection() as connection:
            connection.execute(
                """
                INSERT INTO client (
                    IdTypeCli, NomCli, PrenomCli, DateNaissanceCli, MailCli,
                    MdpCli, FavoriCli, TelCli
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                """,
                (
                    payload.get("IdTypeCli", 1),
                    payload["NomCli"],
                    payload["PrenomCli"],
                    payload.get("DateNaissanceCli", "2000-01-01"),
                    payload["MailCli"],
                    hash_password(payload["MdpCli"]),
                    payload.get("FavoriCli", ""),
                    payload.get("TelCli", ""),
                ),
            )
            client_id = connection.execute("SELECT last_insert_rowid()").fetchone()[0]
            connection.commit()
            return self.get_client_profile(client_id)

    def get_client_profile(self, client_id: int) -> dict[str, Any]:
        with get_connection() as connection:
            row = connection.execute(
                """
                SELECT c.IdCli, c.NomCli, c.PrenomCli, c.DateNaissanceCli, c.MailCli,
                       c.FavoriCli, c.TelCli, tc.NomTypeCli AS Role
                FROM client c
                JOIN typeclient tc ON tc.IdTypeCli = c.IdTypeCli
                WHERE c.IdCli = ?
                """,
                (client_id,),
            ).fetchone()
            return row_to_dict(row) or {}


@dataclass
class CatalogRepository:
    def list_categories(self) -> list[dict[str, Any]]:
        with get_connection() as connection:
            rows = connection.execute(
                """
                SELECT tp.IdTypeProd, tp.NomTypeProd, COUNT(p.IdProd) AS ProductCount
                FROM typeproduits tp
                LEFT JOIN produits p ON p.IdTypeProd = tp.IdTypeProd
                GROUP BY tp.IdTypeProd, tp.NomTypeProd
                ORDER BY tp.IdTypeProd
                """
            ).fetchall()
            return [row_to_dict(row) for row in rows if row is not None]

    def list_filters(self) -> dict[str, list[str]]:
        with get_connection() as connection:
            colors = connection.execute(
                "SELECT DISTINCT CouleurProd FROM produits ORDER BY CouleurProd"
            ).fetchall()
            gammes = connection.execute(
                "SELECT DISTINCT GammeProd FROM produits ORDER BY GammeProd"
            ).fetchall()
            absorptions = connection.execute(
                "SELECT DISTINCT AbsorptionProd FROM produits ORDER BY AbsorptionProd"
            ).fetchall()
        return {
            "colors": [row[0] for row in colors],
            "gammes": [row[0] for row in gammes],
            "absorptions": [row[0] for row in absorptions],
        }

    def list_products(
        self,
        search: str = "",
        category_id: str = "",
        gamme: str = "",
        color: str = "",
    ) -> list[dict[str, Any]]:
        conditions = []
        params: list[Any] = []

        if search:
            conditions.append(
                """
                (
                    p.NomProd LIKE ? OR
                    p.DescProd LIKE ? OR
                    p.RefProd LIKE ? OR
                    p.CouleurProd LIKE ?
                )
                """
            )
            like_value = f"%{search}%"
            params.extend([like_value, like_value, like_value, like_value])

        if category_id:
            conditions.append("p.IdTypeProd = ?")
            params.append(category_id)

        if gamme:
            conditions.append("p.GammeProd = ?")
            params.append(gamme)

        if color:
            conditions.append("p.CouleurProd = ?")
            params.append(color)

        where_clause = f"WHERE {' AND '.join(conditions)}" if conditions else ""
        query = f"""
        {PRODUCT_SELECT}
        {where_clause}
        GROUP BY p.IdProd
        ORDER BY p.GammeProd DESC, p.NomProd ASC
        """
        with get_connection() as connection:
            rows = connection.execute(query, params).fetchall()
            return [enrich_product(row_to_dict(row)) for row in rows if row is not None]

    def get_product(self, product_id: int) -> dict[str, Any] | None:
        query = f"""
        {PRODUCT_SELECT}
        WHERE p.IdProd = ?
        GROUP BY p.IdProd
        """
        with get_connection() as connection:
            row = connection.execute(query, (product_id,)).fetchone()
            return enrich_product(row_to_dict(row))

    def related_products(self, product_id: int, category_id: int) -> list[dict[str, Any]]:
        query = f"""
        {PRODUCT_SELECT}
        WHERE p.IdTypeProd = ? AND p.IdProd <> ?
        GROUP BY p.IdProd
        ORDER BY p.GammeProd DESC, p.StockProd DESC
        LIMIT 4
        """
        with get_connection() as connection:
            rows = connection.execute(query, (category_id, product_id)).fetchall()
            return [enrich_product(row_to_dict(row)) for row in rows if row is not None]

    def list_reviews(self, product_id: int | None = None, client_id: int | None = None) -> list[dict[str, Any]]:
        query = """
        SELECT a.IdAvis, a.TitreAvis, a.MsgAvis, a.NoteAvis, a.DateAvis,
               c.PrenomCli || ' ' || c.NomCli AS Auteur,
               p.NomProd, p.IdProd
        FROM avis a
        JOIN client c ON c.IdCli = a.IdCli
        JOIN produits p ON p.IdProd = a.IdProd
        """
        conditions = []
        params: list[Any] = []

        if product_id is not None:
            conditions.append("a.IdProd = ?")
            params.append(product_id)

        if client_id is not None:
            conditions.append("a.IdCli = ?")
            params.append(client_id)

        if conditions:
            query += f" WHERE {' AND '.join(conditions)}"
        query += " ORDER BY a.DateAvis DESC, a.IdAvis DESC"

        with get_connection() as connection:
            rows = connection.execute(query, params).fetchall()
            return [row_to_dict(row) for row in rows if row is not None]

    def add_review(self, payload: dict[str, Any]) -> dict[str, Any]:
        with get_connection() as connection:
            existing = connection.execute(
                """
                SELECT IdAvis
                FROM avis
                WHERE IdProd = ? AND IdCli = ?
                """,
                (payload["IdProd"], payload["IdCli"]),
            ).fetchone()
            if existing is not None:
                raise ValueError("Tu as deja laisse un avis sur ce produit.")

            connection.execute(
                """
                INSERT INTO avis (IdProd, IdCli, TitreAvis, MsgAvis, NoteAvis, DateAvis)
                VALUES (?, ?, ?, ?, ?, DATE('now'))
                """,
                (
                    payload["IdProd"],
                    payload["IdCli"],
                    payload["TitreAvis"],
                    payload["MsgAvis"],
                    payload["NoteAvis"],
                ),
            )
            review_id = connection.execute("SELECT last_insert_rowid()").fetchone()[0]
            connection.commit()
            row = connection.execute(
                """
                SELECT a.IdAvis, a.TitreAvis, a.MsgAvis, a.NoteAvis, a.DateAvis,
                       c.PrenomCli || ' ' || c.NomCli AS Auteur,
                       p.NomProd, p.IdProd
                FROM avis a
                JOIN client c ON c.IdCli = a.IdCli
                JOIN produits p ON p.IdProd = a.IdProd
                WHERE a.IdAvis = ?
                """,
                (review_id,),
            ).fetchone()
            return row_to_dict(row) or {}


@dataclass
class ProfileRepository:
    catalog_repository: CatalogRepository = field(default_factory=CatalogRepository)

    def get_profile(self, client_id: int) -> dict[str, Any]:
        with get_connection() as connection:
            client_row = connection.execute(
                """
                SELECT c.IdCli, c.NomCli, c.PrenomCli, c.DateNaissanceCli, c.MailCli,
                       c.FavoriCli, c.TelCli, tc.NomTypeCli AS Role
                FROM client c
                JOIN typeclient tc ON tc.IdTypeCli = c.IdTypeCli
                WHERE c.IdCli = ?
                """,
                (client_id,),
            ).fetchone()
            address_rows = connection.execute(
                """
                SELECT a.IdAddr, a.TypeAddr, a.RueAddr, a.VilleAddr, a.CPAddr, a.PaysAddr
                FROM possede p
                JOIN adresse a ON a.IdAddr = p.IdAddr
                WHERE p.IdCli = ?
                ORDER BY a.TypeAddr, a.IdAddr DESC
                """,
                (client_id,),
            ).fetchall()
            order_stats = connection.execute(
                """
                SELECT COUNT(*) AS OrderCount, COALESCE(SUM(MontantCde), 0) AS TotalSpent
                FROM commande
                WHERE IdCli = ?
                """,
                (client_id,),
            ).fetchone()
            review_count = connection.execute(
                "SELECT COUNT(*) FROM avis WHERE IdCli = ?",
                (client_id,),
            ).fetchone()[0]

        return {
            "client": row_to_dict(client_row),
            "addresses": [row_to_dict(row) for row in address_rows if row is not None],
            "reviews": self.catalog_repository.list_reviews(client_id=client_id),
            "stats": {
                "orders": order_stats["OrderCount"] if order_stats is not None else 0,
                "spent": order_stats["TotalSpent"] if order_stats is not None else 0,
                "reviews": review_count,
            },
        }

    def update_profile(self, client_id: int, payload: dict[str, Any]) -> dict[str, Any]:
        with get_connection() as connection:
            connection.execute(
                """
                UPDATE client
                SET NomCli = ?, PrenomCli = ?, MailCli = ?, TelCli = ?, FavoriCli = ?
                WHERE IdCli = ?
                """,
                (
                    payload["NomCli"],
                    payload["PrenomCli"],
                    payload["MailCli"],
                    payload.get("TelCli", ""),
                    payload.get("FavoriCli", ""),
                    client_id,
                ),
            )
            connection.commit()
        return self.get_profile(client_id)


@dataclass
class OrderRepository:
    def list_payment_methods(self) -> list[dict[str, Any]]:
        with get_connection() as connection:
            rows = connection.execute(
                "SELECT IdPay, LibellePay FROM paiement ORDER BY IdPay"
            ).fetchall()
            return [row_to_dict(row) for row in rows if row is not None]

    def list_orders(self, client_id: int) -> list[dict[str, Any]]:
        with get_connection() as connection:
            orders = connection.execute(
                """
                SELECT c.IdCde, c.StatutCde, c.MontantCde, c.EstPayeCde, c.DateCde,
                       p.LibellePay, l.NomLivr, l.ChoixLivr, l.DelaiLivr, l.FraisLivr, l.DateLivr,
                       tp.ReferenceTransac, tp.StatutTransac, tp.MarqueTransac, tp.MasqueTransac,
                       a.TypeAddr, a.RueAddr, a.VilleAddr, a.CPAddr, a.PaysAddr
                FROM commande c
                LEFT JOIN paiement p ON p.IdPay = c.IdPay
                LEFT JOIN livraison l ON l.IdCde = c.IdCde
                LEFT JOIN transactionpaiement tp ON tp.IdCde = c.IdCde
                LEFT JOIN adresse a ON a.IdAddr = l.IdAddr
                WHERE c.IdCli = ?
                ORDER BY c.IdCde DESC
                """,
                (client_id,),
            ).fetchall()
            result = []
            for order in orders:
                order_dict = row_to_dict(order) or {}
                line_rows = connection.execute(
                    """
                    SELECT lc.Quantite, lc.Reduction, pr.NomProd, pr.PrixProd, pr.ImageProd
                    FROM lignecommande lc
                    JOIN produits pr ON pr.IdProd = lc.IdProd
                    WHERE lc.IdCde = ?
                    ORDER BY pr.NomProd
                    """,
                    (order["IdCde"],),
                ).fetchall()
                order_dict["Lignes"] = [row_to_dict(row) for row in line_rows if row is not None]
                order_dict["TrackingSteps"] = build_tracking_steps(order_dict)
                result.append(order_dict)
            return result

    def create_order(self, payload: dict[str, Any]) -> dict[str, Any]:
        items = payload["items"]
        address = payload["address"]
        client_id = int(payload["IdCli"])
        payment_id = int(payload["IdPay"])
        payment_data = payload.get("paymentData", {})
        validated_delivery = validate_delivery(payload["delivery"])

        if not items:
            raise ValueError("Le panier est vide.")

        with get_connection() as connection:
            client_exists = connection.execute(
                "SELECT IdCli FROM client WHERE IdCli = ?",
                (client_id,),
            ).fetchone()
            if client_exists is None:
                raise ValueError("Client introuvable.")

            payment_method = connection.execute(
                "SELECT LibellePay FROM paiement WHERE IdPay = ?",
                (payment_id,),
            ).fetchone()
            if payment_method is None:
                raise ValueError("La methode de paiement est introuvable.")

            payment_audit = build_payment_audit(payment_method["LibellePay"], payment_data)

            normalized_address = {
                "TypeAddr": require_non_empty(address.get("TypeAddr"), "Le type d adresse est requis."),
                "RueAddr": require_non_empty(address.get("RueAddr"), "La rue de livraison est requise."),
                "VilleAddr": require_non_empty(address.get("VilleAddr"), "La ville de livraison est requise."),
                "PaysAddr": require_non_empty(address.get("PaysAddr"), "Le pays de livraison est requis."),
            }
            normalized_address["CPAddr"] = validate_postal_code(
                require_non_empty(address.get("CPAddr"), "Le code postal de livraison est requis."),
                normalized_address["PaysAddr"],
            )

            subtotal = 0.0
            normalized_items: list[dict[str, Any]] = []
            for item in items:
                quantity = normalize_quantity(item["Quantite"])
                product = connection.execute(
                    "SELECT PrixProd, StockProd, NomProd FROM produits WHERE IdProd = ?",
                    (int(item["IdProd"]),),
                ).fetchone()
                if product is None:
                    raise ValueError("Produit introuvable.")
                if product["StockProd"] < quantity:
                    raise ValueError(f"Stock insuffisant pour {product['NomProd']}.")
                unit_price = float(product["PrixProd"])
                subtotal += unit_price * quantity
                normalized_items.append(
                    {
                        "IdProd": int(item["IdProd"]),
                        "Quantite": quantity,
                        "Reduction": float(item.get("Reduction", 0) or 0),
                        "PrixProd": unit_price,
                    }
                )

            delivery_fee = float(validated_delivery["FraisLivr"])
            total = subtotal + delivery_fee

            connection.execute(
                """
                INSERT INTO adresse (TypeAddr, RueAddr, VilleAddr, CPAddr, PaysAddr)
                VALUES (?, ?, ?, ?, ?)
                """,
                (
                    normalized_address["TypeAddr"],
                    normalized_address["RueAddr"],
                    normalized_address["VilleAddr"],
                    normalized_address["CPAddr"],
                    normalized_address["PaysAddr"],
                ),
            )
            address_id = connection.execute("SELECT last_insert_rowid()").fetchone()[0]

            connection.execute(
                """
                INSERT OR IGNORE INTO possede (IdCli, IdAddr)
                VALUES (?, ?)
                """,
                (client_id, address_id),
            )

            connection.execute(
                """
                INSERT INTO commande (IdPay, IdCli, StatutCde, MontantCde, EstPayeCde, DateCde)
                VALUES (?, ?, ?, ?, ?, DATE('now'))
                """,
                (payment_id, client_id, "En preparation", total, 1),
            )
            order_id = connection.execute("SELECT last_insert_rowid()").fetchone()[0]

            for item in normalized_items:
                connection.execute(
                    """
                    INSERT INTO lignecommande (IdCde, IdProd, Reduction, Quantite)
                    VALUES (?, ?, ?, ?)
                    """,
                    (order_id, item["IdProd"], item.get("Reduction", 0), item["Quantite"]),
                )
                connection.execute(
                    """
                    UPDATE produits
                    SET StockProd = StockProd - ?
                    WHERE IdProd = ?
                    """,
                    (item["Quantite"], item["IdProd"]),
                )

            connection.execute(
                """
                INSERT INTO livraison (
                    IdAddr, IdCde, NomLivr, ChoixLivr, DelaiLivr, FraisLivr, DateLivr
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
                """,
                (
                    address_id,
                    order_id,
                    validated_delivery["NomLivr"],
                    validated_delivery["ChoixLivr"],
                    validated_delivery["DelaiLivr"],
                    validated_delivery["FraisLivr"],
                    validated_delivery["DateLivr"],
                ),
            )
            authorization_code = f"AUTH-{datetime.now().strftime('%Y%m%d%H%M%S')}-{order_id:04d}"
            connection.execute(
                """
                INSERT INTO transactionpaiement (
                    IdCde, StatutTransac, MontantTransac, DeviseTransac, ReferenceTransac,
                    PorteurTransac, MarqueTransac, MasqueTransac, QuatreDerniersTransac
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                """,
                (
                    order_id,
                    "Autorisee",
                    total,
                    "EUR",
                    authorization_code,
                    payment_audit["holder"],
                    payment_audit["brand"],
                    payment_audit["masked"],
                    payment_audit["last4"],
                ),
            )
            connection.commit()

        return {
            "IdCde": order_id,
            "MontantCde": round(total, 2),
            "PaymentStatus": "Autorisee",
            "PaymentReference": authorization_code,
        }


@dataclass
class AdminRepository:
    def dashboard(self) -> dict[str, Any]:
        with get_connection() as connection:
            stats = {
                "clients": connection.execute("SELECT COUNT(*) FROM client").fetchone()[0],
                "products": connection.execute("SELECT COUNT(*) FROM produits").fetchone()[0],
                "orders": connection.execute("SELECT COUNT(*) FROM commande").fetchone()[0],
                "reviews": connection.execute("SELECT COUNT(*) FROM avis").fetchone()[0],
                "revenue": connection.execute(
                    "SELECT COALESCE(SUM(MontantCde), 0) FROM commande WHERE EstPayeCde = 1"
                ).fetchone()[0],
            }

            low_stock = connection.execute(
                """
                SELECT IdProd, NomProd, StockProd
                FROM produits
                WHERE StockProd <= 20
                ORDER BY StockProd ASC, NomProd ASC
                """
            ).fetchall()

            latest_orders = connection.execute(
                """
                SELECT c.IdCde, c.StatutCde, c.MontantCde, cl.PrenomCli || ' ' || cl.NomCli AS ClientNom
                FROM commande c
                JOIN client cl ON cl.IdCli = c.IdCli
                ORDER BY c.IdCde DESC
                LIMIT 6
                """
            ).fetchall()

        return {
            "stats": stats,
            "lowStock": [row_to_dict(row) for row in low_stock if row is not None],
            "latestOrders": [row_to_dict(row) for row in latest_orders if row is not None],
        }

    def list_clients(self) -> list[dict[str, Any]]:
        with get_connection() as connection:
            rows = connection.execute(
                """
                SELECT c.IdCli, c.NomCli, c.PrenomCli, c.MailCli, c.TelCli, tc.NomTypeCli AS Role
                FROM client c
                JOIN typeclient tc ON tc.IdTypeCli = c.IdTypeCli
                ORDER BY c.IdCli DESC
                """
            ).fetchall()
            return [row_to_dict(row) for row in rows if row is not None]

    def list_products(self) -> list[dict[str, Any]]:
        query = f"""
        {PRODUCT_SELECT}
        GROUP BY p.IdProd
        ORDER BY p.IdProd DESC
        """
        with get_connection() as connection:
            rows = connection.execute(query).fetchall()
            return [enrich_product(row_to_dict(row)) for row in rows if row is not None]

    def save_product(self, payload: dict[str, Any]) -> dict[str, Any]:
        fields = (
            payload["IdTypeProd"],
            payload["NomProd"],
            payload["PrixProd"],
            payload["StockProd"],
            payload["ImageProd"],
            payload["TailleProd"],
            payload["RefProd"],
            payload["DescProd"],
            payload["CouleurProd"],
            payload["GammeProd"],
            payload["AbsorptionProd"],
            payload["UsageProd"],
            payload["PointsFortsProd"],
            payload.get("BadgeProd", ""),
        )

        with get_connection() as connection:
            if payload.get("IdProd"):
                connection.execute(
                    """
                    UPDATE produits
                    SET IdTypeProd = ?, NomProd = ?, PrixProd = ?, StockProd = ?,
                        ImageProd = ?, TailleProd = ?, RefProd = ?, DescProd = ?,
                        CouleurProd = ?, GammeProd = ?, AbsorptionProd = ?, UsageProd = ?,
                        PointsFortsProd = ?, BadgeProd = ?
                    WHERE IdProd = ?
                    """,
                    fields + (payload["IdProd"],),
                )
                product_id = payload["IdProd"]
            else:
                connection.execute(
                    """
                    INSERT INTO produits (
                        IdTypeProd, NomProd, PrixProd, StockProd, ImageProd,
                        TailleProd, RefProd, DescProd, CouleurProd, GammeProd,
                        AbsorptionProd, UsageProd, PointsFortsProd, BadgeProd
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    """,
                    fields,
                )
                product_id = connection.execute("SELECT last_insert_rowid()").fetchone()[0]

            connection.commit()
            row = connection.execute(
                f"""
                {PRODUCT_SELECT}
                WHERE p.IdProd = ?
                GROUP BY p.IdProd
                """,
                (product_id,),
            ).fetchone()
            return enrich_product(row_to_dict(row)) or {}
