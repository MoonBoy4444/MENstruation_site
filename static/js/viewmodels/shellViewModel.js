export class ShellViewModel {
  constructor(store) {
    this.store = store;
  }

  navItems() {
    const user = this.store.state.user;
    const cartCount = this.store.state.cart.reduce((sum, item) => sum + Number(item.Quantite), 0);
    const items = [
      { key: "home", label: "Accueil" },
      { key: "catalog", label: "Produits" },
      { key: "cart", label: `Panier${cartCount ? ` (${cartCount})` : ""}` },
      { key: "orders", label: "Commandes" },
      { key: "profile", label: "Profil" },
    ];

    if (!user) {
      items.push({ key: "auth", label: "Connexion" });
    }

    if (user?.Role === "Administrateur") {
      items.push({ key: "admin", label: "Administration" });
    }

    return items;
  }

  setRoute(route, selectedProductId = null) {
    this.store.setState({ route, selectedProductId });
  }

  logout() {
    this.store.setState({ user: null, route: "home" });
    this.store.setFlash("success", "Session fermee.");
  }

  pageTitle() {
    const titles = {
      home: "Accueil",
      catalog: "Produits",
      product: "Fiche produit",
      cart: "Panier",
      auth: "Connexion",
      profile: "Profil",
      orders: "Commandes",
      admin: "Administration",
    };
    return titles[this.store.state.route] || "MENstruation";
  }
}
