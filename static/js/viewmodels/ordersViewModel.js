export class OrdersViewModel {
  constructor(api, store) {
    this.api = api;
    this.store = store;
  }

  async load() {
    const user = this.store.state.user;
    if (!user) {
      throw new Error("Connecte-toi pour voir tes commandes.");
    }
    return this.api.get(`/api/orders/${user.IdCli}`);
  }

  cartTotal() {
    return this.store.state.cart.reduce(
      (sum, item) => sum + Number(item.PrixProd) * Number(item.Quantite),
      0
    );
  }

  cartCount() {
    return this.store.state.cart.reduce((sum, item) => sum + Number(item.Quantite), 0);
  }

  shippingCost(deliveryMode) {
    return deliveryMode === "Express 24h" ? 8.9 : 4.9;
  }

  deliveryWindow(deliveryMode) {
    if (deliveryMode === "Express 24h") {
      return { minDays: 1, maxDays: 5 };
    }
    return { minDays: 3, maxDays: 10 };
  }

  updateCartItem(productId, nextQuantity) {
    const cart = this.store.state.cart
      .map((item) =>
        item.IdProd === productId ? { ...item, Quantite: Math.max(1, nextQuantity) } : item
      )
      .filter(Boolean);
    this.store.setState({ cart });
  }

  removeFromCart(productId) {
    const cart = this.store.state.cart.filter((item) => item.IdProd !== productId);
    this.store.setState({ cart });
    this.store.setFlash("success", "Produit retire du panier.");
  }

  clearCart() {
    this.store.setState({ cart: [] });
  }

  buildPaymentData(formData) {
    const paymentId = Number(formData.get("paymentId"));
    if (paymentId === 1) {
      return {
        cardholderName: String(formData.get("cardholderName") || "").trim(),
        cardNumber: String(formData.get("cardNumber") || "").trim(),
        expiryMonth: Number(formData.get("expiryMonth")),
        expiryYear: Number(formData.get("expiryYear")),
        cvv: String(formData.get("cvv") || "").trim(),
      };
    }

    if (paymentId === 2) {
      return {
        walletEmail: String(formData.get("walletEmail") || "").trim(),
      };
    }

    return {
      walletDevice: String(formData.get("walletDevice") || "").trim(),
    };
  }

  async checkout(formData) {
    const user = this.store.state.user;
    if (!user) {
      throw new Error("Connecte-toi pour commander.");
    }
    if (this.store.state.cart.length === 0) {
      throw new Error("Le panier est vide.");
    }

    const deliveryMode = formData.get("deliveryMode");
    const deliveryFee = this.shippingCost(deliveryMode);

    const result = await this.api.post("/api/orders", {
      IdCli: user.IdCli,
      IdPay: Number(formData.get("paymentId")),
      items: this.store.state.cart,
      address: {
        TypeAddr: "Livraison",
        RueAddr: formData.get("rue"),
        VilleAddr: formData.get("ville"),
        CPAddr: formData.get("cp"),
        PaysAddr: formData.get("pays"),
      },
      delivery: {
        NomLivr: "MENstruation Logistics",
        ChoixLivr: deliveryMode,
        DelaiLivr: deliveryMode === "Express 24h" ? "24h" : "72h",
        FraisLivr: deliveryFee,
        DateLivr: formData.get("deliveryDate"),
      },
      paymentData: this.buildPaymentData(formData),
    });

    this.clearCart();
    this.store.setFlash("success", `Commande #${result.IdCde} confirmee.`);
    return result;
  }
}
