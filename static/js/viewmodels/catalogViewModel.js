export class CatalogViewModel {
  constructor(api, store) {
    this.api = api;
    this.store = store;
  }

  async load(filters = {}) {
    const params = new URLSearchParams();
    if (filters.search) params.set("search", filters.search);
    if (filters.category) params.set("category", filters.category);
    if (filters.gamme) params.set("gamme", filters.gamme);
    if (filters.color) params.set("color", filters.color);
    const query = params.toString();
    return this.api.get(`/api/catalog${query ? `?${query}` : ""}`);
  }

  async getProduct(productId) {
    return this.api.get(`/api/products/${productId}`);
  }

  async addReview(formData) {
    const user = this.store.state.user;
    if (!user) {
      throw new Error("Connecte-toi pour publier un avis.");
    }

    const result = await this.api.post("/api/reviews", {
      IdProd: Number(formData.get("productId")),
      IdCli: user.IdCli,
      TitreAvis: formData.get("title"),
      MsgAvis: formData.get("message"),
      NoteAvis: Number(formData.get("rating")),
    });

    this.store.setFlash("success", "Avis enregistre.");
    return result;
  }

  addToCart(product, quantity = 1) {
    const cart = [...this.store.state.cart];
    const existing = cart.find((item) => item.IdProd === product.IdProd);
    if (existing) {
      existing.Quantite += quantity;
    } else {
      cart.push({
        IdProd: product.IdProd,
        NomProd: product.NomProd,
        PrixProd: Number(product.PrixProd),
        Quantite: quantity,
        ImageProd: product.ImageProd,
        CouleurProd: product.CouleurProd,
        GammeProd: product.GammeProd,
        TailleProd: product.TailleProd,
      });
    }
    this.store.setState({ cart });
    this.store.setFlash("success", `${product.NomProd} ajoute au panier.`);
  }
}
