from __future__ import annotations

from dataclasses import dataclass, field
from typing import Any

from backend.repositories import (
    AdminRepository,
    AuthRepository,
    CatalogRepository,
    OrderRepository,
    ProfileRepository,
)


@dataclass
class AppService:
    auth_repository: AuthRepository = field(default_factory=AuthRepository)
    catalog_repository: CatalogRepository = field(default_factory=CatalogRepository)
    profile_repository: ProfileRepository = field(default_factory=ProfileRepository)
    order_repository: OrderRepository = field(default_factory=OrderRepository)
    admin_repository: AdminRepository = field(default_factory=AdminRepository)

    def home_payload(self) -> dict[str, Any]:
        products = self.catalog_repository.list_products()
        categories = self.catalog_repository.list_categories()
        reviews = self.catalog_repository.list_reviews()
        return {
            "hero": {
                "title": "MENstruation",
                "subtitle": "La boutique gamer adulte pensee pour les longues sessions.",
                "description": (
                    "Slips couches, calecons couches et leggings couches pour adultes, "
                    "avec univers visuel gamer, versions premium et checkout complet."
                ),
                "mission": {
                    "goal": (
                        "Proposer des protections adultes pensees pour les gamers et gameuses "
                        "qui veulent rester confortables pendant de longues sessions."
                    ),
                    "vision": (
                        "Montrer qu un produit intime peut etre utile, assume, esthetique "
                        "et integre dans un univers e-commerce moderne."
                    ),
                    "method": (
                        "Nous le faisons avec une boutique simple a utiliser, un catalogue clair, "
                        "des gammes Core et Premium, des avis clients, un panier et une livraison suivie."
                    ),
                },
            },
            "featuredProducts": products[:6],
            "categories": categories,
            "latestReviews": reviews[:4],
            "filters": self.catalog_repository.list_filters(),
            "metrics": {
                "products": len(products),
                "categories": len(categories),
                "reviews": len(reviews),
            },
        }

    def login(self, payload: dict[str, Any]) -> dict[str, Any]:
        user = self.auth_repository.login(payload["email"], payload["password"])
        if user is None:
            raise ValueError("Email ou mot de passe incorrect.")
        return user

    def register(self, payload: dict[str, Any]) -> dict[str, Any]:
        return self.auth_repository.register(payload)

    def get_catalog(
        self,
        search: str = "",
        category_id: str = "",
        gamme: str = "",
        color: str = "",
    ) -> dict[str, Any]:
        return {
            "categories": self.catalog_repository.list_categories(),
            "filters": self.catalog_repository.list_filters(),
            "products": self.catalog_repository.list_products(search, category_id, gamme, color),
        }

    def get_product_detail(self, product_id: int) -> dict[str, Any]:
        product = self.catalog_repository.get_product(product_id)
        if product is None:
            raise ValueError("Produit introuvable.")
        product["reviews"] = self.catalog_repository.list_reviews(product_id)
        product["relatedProducts"] = self.catalog_repository.related_products(
            product_id,
            product["IdTypeProd"],
        )
        return product

    def add_review(self, payload: dict[str, Any]) -> dict[str, Any]:
        return self.catalog_repository.add_review(payload)

    def get_profile(self, client_id: int) -> dict[str, Any]:
        return self.profile_repository.get_profile(client_id)

    def update_profile(self, client_id: int, payload: dict[str, Any]) -> dict[str, Any]:
        return self.profile_repository.update_profile(client_id, payload)

    def get_orders(self, client_id: int) -> dict[str, Any]:
        return {
            "paymentMethods": self.order_repository.list_payment_methods(),
            "orders": self.order_repository.list_orders(client_id),
        }

    def create_order(self, payload: dict[str, Any]) -> dict[str, Any]:
        return self.order_repository.create_order(payload)

    def admin_dashboard(self) -> dict[str, Any]:
        return self.admin_repository.dashboard()

    def admin_clients(self) -> list[dict[str, Any]]:
        return self.admin_repository.list_clients()

    def admin_products(self) -> list[dict[str, Any]]:
        return self.admin_repository.list_products()

    def save_product(self, payload: dict[str, Any]) -> dict[str, Any]:
        return self.admin_repository.save_product(payload)
