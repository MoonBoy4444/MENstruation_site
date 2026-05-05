import { ApiClient } from "./core/api.js";
import { AppStore } from "./core/store.js";
import { ShellViewModel } from "./viewmodels/shellViewModel.js";
import { HomeViewModel } from "./viewmodels/homeViewModel.js";
import { AuthViewModel } from "./viewmodels/authViewModel.js";
import { CatalogViewModel } from "./viewmodels/catalogViewModel.js";
import { ProfileViewModel } from "./viewmodels/profileViewModel.js";
import { OrdersViewModel } from "./viewmodels/ordersViewModel.js";
import { AdminViewModel } from "./viewmodels/adminViewModel.js";
import {
  adminTemplate,
  authTemplate,
  cartTemplate,
  catalogTemplate,
  homeTemplate,
  ordersTemplate,
  productTemplate,
  profileTemplate,
  renderFlash,
  renderNav,
} from "./views.js";

const api = new ApiClient();
const store = new AppStore();
const shellVm = new ShellViewModel(store);
const homeVm = new HomeViewModel(api);
const authVm = new AuthViewModel(api, store);
const catalogVm = new CatalogViewModel(api, store);
const profileVm = new ProfileViewModel(api, store);
const ordersVm = new OrdersViewModel(api, store);
const adminVm = new AdminViewModel(api, store);

const pageTitle = document.getElementById("page-title");
const navMenu = document.getElementById("nav-menu");
const viewHost = document.getElementById("app-view");
const flashZone = document.getElementById("flash-zone");
const userBadge = document.getElementById("user-badge");

let currentCatalogFilters = {};
const guestPaymentMethods = [
  { IdPay: 1, LibellePay: "Carte bancaire" },
  { IdPay: 2, LibellePay: "PayPal" },
  { IdPay: 3, LibellePay: "Apple Pay" },
  { IdPay: 4, LibellePay: "Google Pay" },
];

store.subscribe(renderApp);
renderApp();

async function renderApp() {
  const { route, user, flash, selectedProductId, cart } = store.state;

  renderFlash(flashZone, flash);
  renderNav(
    navMenu,
    shellVm.navItems(),
    route,
    (nextRoute) => shellVm.setRoute(nextRoute),
    () => shellVm.logout(),
    user
  );

  pageTitle.textContent = shellVm.pageTitle();
  userBadge.textContent = user ? `${user.PrenomCli} • ${user.Role}` : "Visiteur";

  try {
    if (route === "home") {
      const data = await homeVm.load();
      viewHost.innerHTML = homeTemplate(data);
    } else if (route === "catalog") {
      const data = await catalogVm.load(currentCatalogFilters);
      viewHost.innerHTML = catalogTemplate(data, currentCatalogFilters);
    } else if (route === "product" && selectedProductId) {
      const product = await catalogVm.getProduct(selectedProductId);
      viewHost.innerHTML = productTemplate(product, user);
    } else if (route === "cart") {
      const data = user ? await ordersVm.load() : { paymentMethods: guestPaymentMethods, orders: [] };
      const subtotal = ordersVm.cartTotal();
      const shipping = cart.length ? ordersVm.shippingCost("Standard 72h") : 0;
      viewHost.innerHTML = cartTemplate(data, cart, {
        count: ordersVm.cartCount(),
        subtotal,
        shipping,
        total: subtotal + shipping,
      });
    } else if (route === "auth") {
      viewHost.innerHTML = authTemplate();
    } else if (route === "profile") {
      guardConnected();
      const data = await profileVm.load();
      viewHost.innerHTML = profileTemplate(data);
    } else if (route === "orders") {
      guardConnected();
      const data = await ordersVm.load();
      viewHost.innerHTML = ordersTemplate(data);
    } else if (route === "admin") {
      adminVm.assertAdmin();
      const data = await adminVm.loadDashboard();
      viewHost.innerHTML = adminTemplate(data);
    } else {
      shellVm.setRoute("home");
      return;
    }
  } catch (error) {
    store.setFlash("error", error.message);
    if (route !== "home") {
      viewHost.innerHTML = `<div class="empty-state">${error.message}</div>`;
    }
  }

  bindGlobalInteractions();
}

function guardConnected() {
  if (!store.state.user) {
    throw new Error("Connecte-toi pour acceder a cette page.");
  }
}

function formatPrice(value) {
  return new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "EUR",
  }).format(Number(value || 0));
}

function toIsoDate(offsetDays) {
  const baseDate = new Date();
  baseDate.setHours(0, 0, 0, 0);
  baseDate.setDate(baseDate.getDate() + offsetDays);
  return baseDate.toISOString().split("T")[0];
}

function bindCheckoutForm() {
  const form = document.getElementById("checkout-form");
  if (!form) return;

  const paymentSelect = document.getElementById("payment-method-select");
  const deliverySelect = document.getElementById("delivery-mode-select");
  const deliveryDateInput = form.querySelector('input[name="deliveryDate"]');
  const shippingLabel = document.getElementById("shipping-label");
  const shippingValue = document.getElementById("shipping-value");
  const totalValue = document.getElementById("cart-total-value");
  const cardFields = document.getElementById("card-payment-fields");
  const paypalFields = document.getElementById("paypal-fields");
  const walletFields = document.getElementById("wallet-device-fields");

  const cardInputs = Array.from(cardFields?.querySelectorAll("input") || []);
  const paypalInput = paypalFields?.querySelector("input");
  const walletInput = walletFields?.querySelector("input");
  const subtotal = ordersVm.cartTotal();
  const expiryYearInput = form.querySelector('input[name="expiryYear"]');
  const currentYear = new Date().getFullYear();

  if (expiryYearInput) {
    expiryYearInput.min = String(currentYear);
    expiryYearInput.max = String(currentYear + 15);
    expiryYearInput.placeholder = String(currentYear + 2);
  }

  const syncDelivery = () => {
    const mode = deliverySelect?.value || "Standard 72h";
    const shipping = ordersVm.shippingCost(mode);
    const total = subtotal + shipping;
    const window = ordersVm.deliveryWindow(mode);
    const minDate = toIsoDate(window.minDays);
    const maxDate = toIsoDate(window.maxDays);

    if (shippingLabel) {
      shippingLabel.textContent = mode === "Express 24h" ? "Livraison express" : "Livraison standard";
    }
    if (shippingValue) {
      shippingValue.textContent = formatPrice(shipping);
    }
    if (totalValue) {
      totalValue.textContent = formatPrice(total);
    }
    if (deliveryDateInput) {
      deliveryDateInput.min = minDate;
      deliveryDateInput.max = maxDate;
      if (!deliveryDateInput.value || deliveryDateInput.value < minDate || deliveryDateInput.value > maxDate) {
        deliveryDateInput.value = minDate;
      }
    }
  };

  const syncPaymentFields = () => {
    const paymentId = Number(paymentSelect?.value || 1);
    const showCard = paymentId === 1;
    const showPaypal = paymentId === 2;
    const showWallet = paymentId === 3 || paymentId === 4;

    if (cardFields) cardFields.hidden = !showCard;
    if (paypalFields) paypalFields.hidden = !showPaypal;
    if (walletFields) walletFields.hidden = !showWallet;

    cardInputs.forEach((input) => {
      input.required = showCard;
      if (!showCard) input.value = "";
    });
    if (paypalInput) {
      paypalInput.required = showPaypal;
      if (!showPaypal) paypalInput.value = "";
    }
    if (walletInput) {
      walletInput.required = showWallet;
      if (!showWallet) walletInput.value = "";
    }
  };

  paymentSelect?.addEventListener("change", syncPaymentFields);
  deliverySelect?.addEventListener("change", syncDelivery);
  syncPaymentFields();
  syncDelivery();
}

function bindGlobalInteractions() {
  document.querySelectorAll("[data-route]").forEach((button) => {
    button.addEventListener("click", () => {
      shellVm.setRoute(button.dataset.route);
    });
  });

  document.querySelectorAll("[data-open-product]").forEach((button) => {
    button.addEventListener("click", () => {
      shellVm.setRoute("product", Number(button.dataset.openProduct));
    });
  });

  document.querySelectorAll("[data-add-product]").forEach((button) => {
    button.addEventListener("click", async () => {
      const product = await catalogVm.getProduct(Number(button.dataset.addProduct));
      catalogVm.addToCart(product);
    });
  });

  document.querySelectorAll("[data-cart-change]").forEach((button) => {
    button.addEventListener("click", () => {
      const productId = Number(button.dataset.cartChange);
      const delta = Number(button.dataset.cartDelta);
      const item = store.state.cart.find((entry) => entry.IdProd === productId);
      if (!item) return;
      ordersVm.updateCartItem(productId, Number(item.Quantite) + delta);
    });
  });

  document.querySelectorAll("[data-cart-remove]").forEach((button) => {
    button.addEventListener("click", () => {
      ordersVm.removeFromCart(Number(button.dataset.cartRemove));
    });
  });

  document.querySelector("[data-cart-clear]")?.addEventListener("click", () => {
    ordersVm.clearCart();
    store.setFlash("success", "Panier vide.");
  });

  document.getElementById("catalog-filter-form")?.addEventListener("submit", async (event) => {
    event.preventDefault();
    currentCatalogFilters = Object.fromEntries(new FormData(event.currentTarget).entries());
    const data = await catalogVm.load(currentCatalogFilters);
    viewHost.innerHTML = catalogTemplate(data, currentCatalogFilters);
    bindGlobalInteractions();
  });

  document.getElementById("login-form")?.addEventListener("submit", async (event) => {
    event.preventDefault();
    try {
      await authVm.login(new FormData(event.currentTarget));
    } catch (error) {
      store.setFlash("error", error.message);
    }
  });

  document.getElementById("register-form")?.addEventListener("submit", async (event) => {
    event.preventDefault();
    try {
      await authVm.register(new FormData(event.currentTarget));
    } catch (error) {
      store.setFlash("error", error.message);
    }
  });

  document.getElementById("profile-form")?.addEventListener("submit", async (event) => {
    event.preventDefault();
    try {
      const data = await profileVm.save(new FormData(event.currentTarget));
      viewHost.innerHTML = profileTemplate(data);
      bindGlobalInteractions();
    } catch (error) {
      store.setFlash("error", error.message);
    }
  });

  document.getElementById("checkout-form")?.addEventListener("submit", async (event) => {
    event.preventDefault();
    try {
      await ordersVm.checkout(new FormData(event.currentTarget));
      store.setState({ route: "orders" });
    } catch (error) {
      store.setFlash("error", error.message);
    }
  });

  bindCheckoutForm();

  document.getElementById("review-form")?.addEventListener("submit", async (event) => {
    event.preventDefault();
    try {
      await catalogVm.addReview(new FormData(event.currentTarget));
      store.notify();
    } catch (error) {
      store.setFlash("error", error.message);
    }
  });

  document.getElementById("admin-product-form")?.addEventListener("submit", async (event) => {
    event.preventDefault();
    try {
      await adminVm.saveProduct(new FormData(event.currentTarget));
      store.setState({ route: "admin" });
    } catch (error) {
      store.setFlash("error", error.message);
    }
  });

  document.getElementById("admin-reset-product")?.addEventListener("click", () => {
    document.getElementById("admin-product-form")?.reset();
  });

  document.querySelectorAll("[data-edit-product]").forEach((button) => {
    button.addEventListener("click", () => {
      const product = JSON.parse(decodeURIComponent(button.dataset.editProduct));
      const form = document.getElementById("admin-product-form");
      if (!form) return;
      form.elements.id.value = product.IdProd;
      form.elements.categoryId.value = product.IdTypeProd;
      form.elements.name.value = product.NomProd;
      form.elements.price.value = product.PrixProd;
      form.elements.stock.value = product.StockProd;
      form.elements.image.value = product.ImageProd;
      form.elements.size.value = product.TailleProd;
      form.elements.ref.value = product.RefProd;
      form.elements.description.value = product.DescProd;
      form.elements.color.value = product.CouleurProd;
      form.elements.gamme.value = product.GammeProd;
      form.elements.absorption.value = product.AbsorptionProd;
      form.elements.usage.value = product.UsageProd;
      form.elements.badge.value = product.BadgeProd || "";
      form.elements.highlights.value = (product.PointsForts || []).join(", ");
    });
  });
}
