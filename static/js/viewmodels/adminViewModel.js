export class AdminViewModel {
  constructor(api, store) {
    this.api = api;
    this.store = store;
  }

  assertAdmin() {
    if (this.store.state.user?.Role !== "Administrateur") {
      throw new Error("Acces reserve a l administrateur.");
    }
  }

  async loadDashboard() {
    this.assertAdmin();
    const [dashboard, clients, products, catalog] = await Promise.all([
      this.api.get("/api/admin/dashboard"),
      this.api.get("/api/admin/clients"),
      this.api.get("/api/admin/products"),
      this.api.get("/api/catalog"),
    ]);
    return {
      ...dashboard,
      clients,
      products,
      categories: catalog.categories,
      filters: catalog.filters,
    };
  }

  async saveProduct(formData) {
    this.assertAdmin();
    const payload = {
      IdProd: formData.get("id") ? Number(formData.get("id")) : null,
      IdTypeProd: Number(formData.get("categoryId")),
      NomProd: formData.get("name"),
      PrixProd: Number(formData.get("price")),
      StockProd: Number(formData.get("stock")),
      ImageProd: formData.get("image"),
      TailleProd: formData.get("size"),
      RefProd: formData.get("ref"),
      DescProd: formData.get("description"),
      CouleurProd: formData.get("color"),
      GammeProd: formData.get("gamme"),
      AbsorptionProd: formData.get("absorption"),
      UsageProd: formData.get("usage"),
      PointsFortsProd: formData
        .get("highlights")
        .split(",")
        .map((item) => item.trim())
        .filter(Boolean)
        .join("|"),
      BadgeProd: formData.get("badge"),
    };
    const product = payload.IdProd
      ? await this.api.put("/api/admin/products", payload)
      : await this.api.post("/api/admin/products", payload);
    this.store.setFlash("success", `Produit ${product.NomProd} enregistre.`);
    return product;
  }
}
