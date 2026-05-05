function formatPrice(value) {
  return new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "EUR",
  }).format(Number(value || 0));
}

function stars(note) {
  const rounded = Math.round(Number(note || 0));
  return "★★★★★".slice(0, rounded) + "☆☆☆☆☆".slice(0, 5 - rounded);
}

function formatDate(value) {
  if (!value) return "";
  return new Date(value).toLocaleDateString("fr-FR");
}

function emptyState(message) {
  return `<div class="empty-state">${message}</div>`;
}

function assetPath(value) {
  return String(value || "").replace(/^\/+/, "");
}

function statPill(label, value) {
  return `
    <div class="stat-pill">
      <span>${label}</span>
      <strong>${value}</strong>
    </div>
  `;
}

function categoryCard(category) {
  return `
    <article class="mini-card">
      <p class="eyebrow">Categorie</p>
      <h3>${category.NomTypeProd}</h3>
      <p class="muted">${category.ProductCount} reference(s)</p>
    </article>
  `;
}

function reviewCard(review) {
  return `
    <article class="review-card">
      <div class="list-row">
        <div>
          <strong>${review.TitreAvis}</strong>
          <div class="muted">${review.Auteur || "Client"}${review.NomProd ? ` • ${review.NomProd}` : ""}</div>
        </div>
        <span class="badge">${stars(review.NoteAvis)}</span>
      </div>
      <p>${review.MsgAvis}</p>
      <p class="small">${formatDate(review.DateAvis)}</p>
    </article>
  `;
}

function productCard(product) {
  return `
    <article class="product-card">
      <div class="product-image-wrap">
        <img src="${assetPath(product.ImageProd)}" alt="${product.NomProd}" />
        ${product.BadgeProd ? `<span class="badge badge-overlay">${product.BadgeProd}</span>` : ""}
      </div>
      <div class="product-content">
        <div class="tag-row">
          <span class="tag">${product.NomTypeProd}</span>
          <span class="tag">${product.GammeProd}</span>
          <span class="tag">${product.CouleurProd}</span>
        </div>
        <div>
          <h3>${product.NomProd}</h3>
          <p class="muted clamp-3">${product.DescProd}</p>
        </div>
        <div class="meta-row">
          <span class="price">${formatPrice(product.PrixProd)}</span>
          <span class="tag">${stars(product.NoteMoyenne)} (${product.NombreAvis})</span>
        </div>
        <div class="inline-actions">
          <button class="btn" data-open-product="${product.IdProd}">Voir</button>
          <button class="btn-ghost" data-add-product="${product.IdProd}">Ajouter</button>
        </div>
      </div>
    </article>
  `;
}

function orderCard(order) {
  return `
    <article class="panel-card order-card">
      <div class="list-row">
        <div>
          <strong>Commande #${order.IdCde}</strong>
          <div class="muted">${formatDate(order.DateCde)} • ${order.StatutCde}</div>
        </div>
        <span class="badge">${formatPrice(order.MontantCde)}</span>
      </div>
      <div class="meta-stack">
        <span>Paiement: ${order.LibellePay || "Non defini"}</span>
        ${
          order.ReferenceTransac
            ? `<span>Autorisation: ${order.ReferenceTransac} • ${order.MarqueTransac || "Paiement"} ${order.MasqueTransac || ""}</span>`
            : ""
        }
        <span>Livraison: ${order.ChoixLivr || "A definir"}${order.DateLivr ? ` • ${formatDate(order.DateLivr)}` : ""}</span>
        <span>Adresse: ${order.RueAddr || ""} ${order.CPAddr || ""} ${order.VilleAddr || ""}</span>
      </div>
      ${
        order.TrackingSteps?.length
          ? `
            <div class="tracking-card">
              <div class="tracking-head">
                <strong>Suivi de commande</strong>
                <span class="tag">${order.StatutCde}</span>
              </div>
              <div class="tracking-list">
                ${order.TrackingSteps.map(
                  (step) => `
                    <div class="tracking-step ${step.state}">
                      <div class="tracking-dot"></div>
                      <div>
                        <strong>${step.label}</strong>
                        <p>${step.description}</p>
                      </div>
                    </div>
                  `
                ).join("")}
              </div>
            </div>
          `
          : ""
      }
      <div class="order-lines">
        ${order.Lignes.map(
          (line) => `
            <div class="order-line">
              <span>${line.NomProd} x${line.Quantite}</span>
              <strong>${formatPrice(Number(line.PrixProd) * Number(line.Quantite))}</strong>
            </div>
          `
        ).join("")}
      </div>
    </article>
  `;
}

function cartLine(item) {
  return `
    <article class="cart-line">
      <img src="${assetPath(item.ImageProd)}" alt="${item.NomProd}" />
      <div class="cart-line-copy">
        <strong>${item.NomProd}</strong>
        <p class="muted">${item.GammeProd} • ${item.CouleurProd} • Taille ${item.TailleProd}</p>
        <div class="quantity-row">
          <button class="qty-btn" data-cart-change="${item.IdProd}" data-cart-delta="-1">-</button>
          <span>${item.Quantite}</span>
          <button class="qty-btn" data-cart-change="${item.IdProd}" data-cart-delta="1">+</button>
          <button class="btn-link" data-cart-remove="${item.IdProd}">Retirer</button>
        </div>
      </div>
      <strong>${formatPrice(Number(item.PrixProd) * Number(item.Quantite))}</strong>
    </article>
  `;
}

function filterOptions(values, selectedValue = "") {
  return values
    .map(
      (value) =>
        `<option value="${value}" ${value === selectedValue ? "selected" : ""}>${value}</option>`
    )
    .join("");
}

export function renderFlash(container, flash) {
  container.innerHTML = flash
    ? `<div class="flash ${flash.type}">${flash.message}</div>`
    : "";
}

export function renderNav(container, items, activeRoute, onNavigate, onLogout, user) {
  container.innerHTML = "";
  for (const item of items) {
    const button = document.createElement("button");
    button.className = `nav-link ${item.key === activeRoute ? "active" : ""}`;
    button.textContent = item.label;
    button.addEventListener("click", () => onNavigate(item.key));
    container.appendChild(button);
  }

  if (user) {
    const logout = document.createElement("button");
    logout.className = "nav-link nav-link-soft";
    logout.textContent = "Deconnexion";
    logout.addEventListener("click", onLogout);
    container.appendChild(logout);
  }
}

export function homeTemplate(data) {
  return `
    <section class="hero-split">
      <article class="hero-card hero-card-large">
        <p class="eyebrow">MENstruation</p>
        <h3>${data.hero.title}</h3>
        <p class="hero-subtitle">${data.hero.subtitle}</p>
        <p class="muted">Une boutique plus directe, plus lisible et pensee pour des besoins quotidiens reels.</p>
        <div class="mission-grid">
          <article class="mission-card">
            <p class="eyebrow">Selection</p>
            <p>Des pieces absorbantes adultes choisies pour le confort, le maintien et la discretion.</p>
          </article>
          <article class="mission-card">
            <p class="eyebrow">Approche</p>
            <p>Un site plus calme, avec des informations utiles, une lecture simple et des visuels plus nets.</p>
          </article>
          <article class="mission-card">
            <p class="eyebrow">Usage</p>
            <p>Comparer rapidement les coupes, les niveaux d absorption et les references disponibles.</p>
          </article>
        </div>
        <div class="hero-actions">
          <button class="btn" data-route="catalog">Voir la collection</button>
          <button class="btn-ghost" data-route="cart">Panier</button>
        </div>
        <div class="pill-grid">
          ${statPill("Produits", data.metrics.products)}
          ${statPill("Categories", data.metrics.categories)}
          ${statPill("Avis", data.metrics.reviews)}
        </div>
      </article>
      <article class="hero-photo-card">
        <img src="${assetPath("assets/setup-noir.jpg")}" alt="Selection MENstruation en environnement sobre" />
        <div class="hero-photo-copy">
          <p class="eyebrow">Direction</p>
          <h3>Un langage visuel plus epure, avec davantage d espace et moins d effets.</h3>
        </div>
      </article>
    </section>

    <section class="section-title">
      <div>
        <p class="eyebrow">Rayons</p>
        <h3>La collection</h3>
      </div>
    </section>
    <div class="mini-grid">
      ${data.categories.map(categoryCard).join("")}
    </div>

    <section class="section-title">
      <div>
        <p class="eyebrow">Selection</p>
        <h3>Produits mis en avant</h3>
      </div>
      <button class="btn-ghost" data-route="catalog">Voir tout</button>
    </section>
    <div class="product-grid">
      ${data.featuredProducts.map(productCard).join("")}
    </div>

    <section class="section-title">
      <div>
        <p class="eyebrow">Retours clients</p>
        <h3>Avis recents</h3>
      </div>
    </section>
    <div class="review-grid">
      ${data.latestReviews.map(reviewCard).join("")}
    </div>
  `;
}

export function catalogTemplate(data, activeFilters = {}) {
  return `
    <div class="catalog-shell">
      <aside class="panel-card sticky-panel">
        <p class="eyebrow">Recherche</p>
        <h3>Affiner la selection</h3>
        <form id="catalog-filter-form" class="field-grid">
          <label class="field">
            <span>Mot-cle</span>
            <input type="text" name="search" placeholder="Slip, premium, noir..." value="${activeFilters.search || ""}" />
          </label>
          <label class="field">
            <span>Type</span>
            <select name="category">
              <option value="">Tous</option>
              ${data.categories
                .map(
                  (category) =>
                    `<option value="${category.IdTypeProd}" ${
                      String(activeFilters.category || "") === String(category.IdTypeProd) ? "selected" : ""
                    }>${category.NomTypeProd}</option>`
                )
                .join("")}
            </select>
          </label>
          <label class="field">
            <span>Gamme</span>
            <select name="gamme">
              <option value="">Toutes</option>
              ${filterOptions(data.filters.gammes, activeFilters.gamme)}
            </select>
          </label>
          <label class="field">
            <span>Coloris</span>
            <select name="color">
              <option value="">Tous</option>
              ${filterOptions(data.filters.colors, activeFilters.color)}
            </select>
          </label>
          <button class="btn" type="submit">Appliquer</button>
        </form>
      </aside>

      <section class="catalog-results">
        <div class="section-title">
          <div>
            <p class="eyebrow">Catalogue</p>
            <h3>${data.products.length} produit(s)</h3>
          </div>
        </div>
        <div class="product-grid">
          ${data.products.length ? data.products.map(productCard).join("") : emptyState("Aucun produit ne correspond aux filtres choisis.")}
        </div>
      </section>
    </div>
  `;
}

export function productTemplate(product, user) {
  return `
    <section class="product-hero">
      <article class="detail-card product-detail-grid">
        <div class="product-detail-media">
          <img src="${assetPath(product.ImageProd)}" alt="${product.NomProd}" />
        </div>
        <div class="product-detail-copy">
          <p class="eyebrow">${product.NomTypeProd}</p>
          <h3>${product.NomProd}</h3>
          <p class="price">${formatPrice(product.PrixProd)}</p>
          <div class="tag-row">
            <span class="tag">${product.GammeProd}</span>
            <span class="tag">${product.CouleurProd}</span>
            <span class="tag">Absorption ${product.AbsorptionProd}</span>
            <span class="tag">Taille ${product.TailleProd}</span>
          </div>
          <p>${product.DescProd}</p>
          <div class="points-list">
            ${product.PointsForts.map((point) => `<span>${point}</span>`).join("")}
          </div>
          <div class="hero-actions">
            <button class="btn" data-add-product="${product.IdProd}">Ajouter au panier</button>
            <button class="btn-ghost" data-route="cart">Passer au panier</button>
          </div>
          <div class="meta-stack">
            <span>Reference: ${product.RefProd}</span>
            <span>Usage recommande: ${product.UsageProd}</span>
            <span>Stock disponible: ${product.StockProd}</span>
            <span>Avis: ${stars(product.NoteMoyenne)} (${product.NombreAvis})</span>
          </div>
        </div>
      </article>
    </section>

    <div class="two-columns product-columns">
      <section class="panel-card">
        <div class="section-title">
          <div>
            <p class="eyebrow">Avis produit</p>
            <h3>Deposer un avis</h3>
          </div>
        </div>
        ${
          user
            ? `
              <form id="review-form" class="field-grid">
                <input type="hidden" name="productId" value="${product.IdProd}" />
                <label class="field">
                  <span>Titre</span>
                  <input name="title" required />
                </label>
                <label class="field">
                  <span>Note</span>
                  <select name="rating">
                    <option value="5">5</option>
                    <option value="4">4</option>
                    <option value="3">3</option>
                    <option value="2">2</option>
                    <option value="1">1</option>
                  </select>
                </label>
                <label class="field">
                  <span>Message</span>
                  <textarea name="message" rows="5" required></textarea>
                </label>
                <button class="btn-secondary" type="submit">Publier mon avis</button>
              </form>
            `
            : `<p class="muted">Connecte-toi pour laisser un avis et retrouver tes commentaires dans ton profil.</p>`
        }
      </section>

      <section class="panel-card">
        <div class="section-title">
          <div>
            <p class="eyebrow">Produits lies</p>
            <h3>Dans le meme rayon</h3>
          </div>
        </div>
        <div class="related-list">
          ${product.relatedProducts.length ? product.relatedProducts.map(productCard).join("") : emptyState("Pas encore de suggestions sur cette categorie.")}
        </div>
      </section>
    </div>

    <section class="section-title">
      <div>
        <p class="eyebrow">Clients</p>
        <h3>Tous les avis</h3>
      </div>
    </section>
    <div class="review-grid">
      ${product.reviews.length ? product.reviews.map(reviewCard).join("") : emptyState("Aucun avis pour le moment.")}
    </div>
  `;
}

export function authTemplate() {
  return `
    <div class="auth-layout">
      <section class="panel-card auth-card auth-visual">
        <p class="eyebrow">Compte MENstruation</p>
        <h3>Retrouve tes commandes, tes adresses et tes achats dans un espace clair et simple a utiliser.</h3>
        <img src="${assetPath("assets/setup-rgb.jpg")}" alt="Espace compte MENstruation" />
      </section>

      <section class="panel-card auth-card">
        <p class="eyebrow">Connexion</p>
        <h3>Acceder a votre espace</h3>
        <form id="login-form" class="field-grid">
          <label class="field">
            <span>Email</span>
            <input type="email" name="email" required />
          </label>
          <label class="field">
            <span>Mot de passe</span>
            <input type="password" name="password" required />
          </label>
          <button class="btn" type="submit">Se connecter</button>
        </form>
      </section>

      <section class="panel-card auth-card">
        <p class="eyebrow">Inscription</p>
        <h3>Creer un compte</h3>
        <form id="register-form" class="field-grid">
          <div class="field-grid two">
            <label class="field">
              <span>Nom</span>
              <input type="text" name="nom" required />
            </label>
            <label class="field">
              <span>Prenom</span>
              <input type="text" name="prenom" required />
            </label>
          </div>
          <div class="field-grid two">
            <label class="field">
              <span>Date de naissance</span>
              <input type="date" name="dateNaissance" required />
            </label>
            <label class="field">
              <span>Telephone</span>
              <input type="text" name="telephone" />
            </label>
          </div>
          <label class="field">
            <span>Email</span>
            <input type="email" name="email" required />
          </label>
          <label class="field">
            <span>Mot de passe</span>
            <input type="password" name="password" required />
          </label>
          <label class="field">
            <span>Produit favori</span>
            <input type="text" name="favori" placeholder="Ex: Calecon couche Stealth Premium" />
          </label>
          <button class="btn-secondary" type="submit">Creer mon compte</button>
        </form>
      </section>
    </div>
  `;
}

export function profileTemplate(data) {
  const client = data.client;
  return `
    <div class="profile-top">
      <section class="panel-card">
        <p class="eyebrow">Mon profil</p>
        <h3>${client.PrenomCli} ${client.NomCli}</h3>
        <div class="pill-grid">
          ${statPill("Commandes", data.stats.orders)}
          ${statPill("Avis", data.stats.reviews)}
          ${statPill("Depenses", formatPrice(data.stats.spent))}
        </div>
      </section>
    </div>

    <div class="two-columns profile-columns">
      <section class="panel-card">
        <p class="eyebrow">Coordonnees</p>
        <h3>Informations personnelles</h3>
        <form id="profile-form" class="field-grid">
          <div class="field-grid two">
            <label class="field">
              <span>Nom</span>
              <input name="nom" value="${client.NomCli}" required />
            </label>
            <label class="field">
              <span>Prenom</span>
              <input name="prenom" value="${client.PrenomCli}" required />
            </label>
          </div>
          <label class="field">
            <span>Email</span>
            <input type="email" name="email" value="${client.MailCli}" required />
          </label>
          <div class="field-grid two">
            <label class="field">
              <span>Telephone</span>
              <input name="telephone" value="${client.TelCli || ""}" />
            </label>
            <label class="field">
              <span>Produit favori</span>
              <input name="favori" value="${client.FavoriCli || ""}" />
            </label>
          </div>
          <button class="btn" type="submit">Enregistrer</button>
        </form>
      </section>

      <section class="panel-card">
        <p class="eyebrow">Adresses</p>
        <h3>Adresses enregistrees</h3>
        ${
          data.addresses.length
            ? data.addresses
                .map(
                  (address) => `
                    <div class="list-row list-row-card">
                      <div>
                        <strong>${address.TypeAddr}</strong>
                        <div class="muted">${address.RueAddr}, ${address.CPAddr} ${address.VilleAddr}</div>
                      </div>
                      <span>${address.PaysAddr}</span>
                    </div>
                  `
                )
                .join("")
            : `<p class="muted">Aucune adresse enregistree pour le moment.</p>`
        }
      </section>
    </div>

    <section class="section-title">
      <div>
        <p class="eyebrow">Archives</p>
        <h3>Mes avis produits</h3>
      </div>
    </section>
    <div class="review-grid">
      ${data.reviews.length ? data.reviews.map(reviewCard).join("") : emptyState("Tu n as pas encore laisse d avis.")}
    </div>
  `;
}

export function cartTemplate(data, cart, summary) {
  return `
    <div class="cart-layout">
      <section class="panel-card">
        <div class="section-title">
          <div>
            <p class="eyebrow">Panier</p>
            <h3>${summary.count} article(s)</h3>
          </div>
          ${cart.length ? `<button class="btn-link" data-cart-clear="1">Vider le panier</button>` : ""}
        </div>
        ${
          cart.length
            ? `<div class="cart-list">${cart.map(cartLine).join("")}</div>`
            : emptyState("Ton panier est vide. Ajoute des produits depuis le catalogue.")
        }
      </section>

      <aside class="checkout-card">
        <p class="eyebrow">Paiement</p>
        <h3>Finaliser la commande</h3>
        <div class="summary-box">
          <div class="order-line"><span>Sous-total</span><strong>${formatPrice(summary.subtotal)}</strong></div>
          <div class="order-line"><span id="shipping-label">Livraison standard</span><strong id="shipping-value">${formatPrice(summary.shipping)}</strong></div>
          <div class="order-line order-line-total"><span>Total estime</span><strong id="cart-total-value">${formatPrice(summary.total)}</strong></div>
        </div>
        <form id="checkout-form" class="field-grid">
          <label class="field">
            <span>Methode de paiement</span>
            <select name="paymentId" id="payment-method-select">
              ${data.paymentMethods
                .map((item) => `<option value="${item.IdPay}">${item.LibellePay}</option>`)
                .join("")}
            </select>
          </label>
          <label class="field">
            <span>Mode de livraison</span>
            <select name="deliveryMode" id="delivery-mode-select">
              <option value="Standard 72h">Standard 72h</option>
              <option value="Express 24h">Express 24h</option>
            </select>
          </label>
          <div class="field-grid two">
            <label class="field">
              <span>Rue</span>
              <input name="rue" required />
            </label>
            <label class="field">
              <span>Ville</span>
              <input name="ville" required />
            </label>
          </div>
          <div class="field-grid two">
            <label class="field">
              <span>Code postal</span>
              <input name="cp" required />
            </label>
            <label class="field">
              <span>Pays</span>
              <input name="pays" value="France" required />
            </label>
          </div>
          <label class="field">
            <span>Date de livraison souhaitee</span>
            <input type="date" name="deliveryDate" required />
          </label>
          <div id="card-payment-fields" class="field-grid two">
            <label class="field">
              <span>Nom sur la carte</span>
              <input name="cardholderName" autocomplete="cc-name" />
            </label>
            <label class="field">
              <span>Numero de carte</span>
              <input name="cardNumber" inputmode="numeric" autocomplete="cc-number" placeholder="4242 4242 4242 4242" />
            </label>
            <label class="field">
              <span>Mois d expiration</span>
              <input name="expiryMonth" type="number" min="1" max="12" autocomplete="cc-exp-month" placeholder="12" />
            </label>
            <label class="field">
              <span>Annee d expiration</span>
              <input name="expiryYear" type="number" min="2026" max="2045" autocomplete="cc-exp-year" placeholder="2030" />
            </label>
            <label class="field">
              <span>Cryptogramme</span>
              <input name="cvv" inputmode="numeric" autocomplete="cc-csc" placeholder="123" />
            </label>
          </div>
          <div id="paypal-fields" class="field" hidden>
            <span>Email PayPal</span>
            <input name="walletEmail" type="email" autocomplete="email" placeholder="client@email.com" />
          </div>
          <div id="wallet-device-fields" class="field" hidden>
            <span>Appareil autorise</span>
            <input name="walletDevice" placeholder="iPhone de Nova" />
          </div>
          <button class="btn" type="submit" ${cart.length ? "" : "disabled"}>Payer et commander</button>
        </form>
      </aside>
    </div>
  `;
}

export function ordersTemplate(data) {
  return `
    <section class="section-title">
      <div>
        <p class="eyebrow">Historique</p>
        <h3>Mes commandes</h3>
      </div>
    </section>
    <div class="order-grid">
      ${data.orders.length ? data.orders.map(orderCard).join("") : emptyState("Aucune commande pour le moment.")}
    </div>
  `;
}

export function adminTemplate(data) {
  return `
    <div class="stats-row">
      <article class="stat-card"><span>Clients</span><strong>${data.stats.clients}</strong></article>
      <article class="stat-card"><span>Produits</span><strong>${data.stats.products}</strong></article>
      <article class="stat-card"><span>Commandes</span><strong>${data.stats.orders}</strong></article>
      <article class="stat-card"><span>CA</span><strong>${formatPrice(data.stats.revenue)}</strong></article>
    </div>

    <div class="admin-layout">
      <aside class="panel-card">
        <p class="eyebrow">Catalogue admin</p>
        <h3>Ajouter ou modifier un produit</h3>
        <form id="admin-product-form" class="field-grid">
          <input type="hidden" name="id" />
          <label class="field">
            <span>Categorie</span>
            <select name="categoryId">
              ${data.categories
                .map((category) => `<option value="${category.IdTypeProd}">${category.NomTypeProd}</option>`)
                .join("")}
            </select>
          </label>
          <div class="field-grid two">
            <label class="field">
              <span>Nom</span>
              <input name="name" required />
            </label>
            <label class="field">
              <span>Reference</span>
              <input name="ref" required />
            </label>
          </div>
          <div class="field-grid two">
            <label class="field">
              <span>Prix</span>
              <input type="number" step="0.01" name="price" required />
            </label>
            <label class="field">
              <span>Stock</span>
              <input type="number" name="stock" required />
            </label>
          </div>
          <div class="field-grid two">
            <label class="field">
              <span>Taille</span>
              <input name="size" required />
            </label>
            <label class="field">
              <span>Coloris</span>
              <input name="color" required />
            </label>
          </div>
          <div class="field-grid two">
            <label class="field">
              <span>Gamme</span>
              <select name="gamme">
                ${filterOptions(data.filters.gammes)}
              </select>
            </label>
            <label class="field">
              <span>Absorption</span>
              <input name="absorption" placeholder="Ex: 8h" required />
            </label>
          </div>
          <label class="field">
            <span>Usage</span>
            <input name="usage" required />
          </label>
          <label class="field">
            <span>Badge</span>
            <input name="badge" placeholder="Premium, Nouveau..." />
          </label>
          <label class="field">
            <span>Image</span>
            <input name="image" required />
          </label>
          <label class="field">
            <span>Points forts</span>
            <input name="highlights" placeholder="Ex: Maintien, toucher sec, anti-fuite" />
          </label>
          <label class="field">
            <span>Description</span>
            <textarea name="description" rows="4" required></textarea>
          </label>
          <div class="form-actions">
            <button class="btn" type="submit">Enregistrer</button>
            <button class="btn-ghost" type="button" id="admin-reset-product">Vider</button>
          </div>
        </form>
      </aside>

      <section class="field-grid">
        <article class="table-card">
          <div class="section-title">
            <div>
              <p class="eyebrow">Stock faible</p>
              <h3>Produits a surveiller</h3>
            </div>
          </div>
          ${
            data.lowStock.length
              ? data.lowStock
                  .map(
                    (product) => `
                      <div class="list-row">
                        <span>${product.NomProd}</span>
                        <strong>${product.StockProd}</strong>
                      </div>
                    `
                  )
                  .join("")
              : `<p class="muted">Aucune alerte stock.</p>`
          }
        </article>

        <article class="table-card">
          <div class="section-title">
            <div>
              <p class="eyebrow">Clients</p>
              <h3>Base utilisateurs</h3>
            </div>
          </div>
          <table class="table">
            <thead>
              <tr><th>ID</th><th>Nom</th><th>Email</th><th>Role</th></tr>
            </thead>
            <tbody>
              ${data.clients
                .map(
                  (client) => `
                    <tr>
                      <td>${client.IdCli}</td>
                      <td>${client.PrenomCli} ${client.NomCli}</td>
                      <td>${client.MailCli}</td>
                      <td>${client.Role}</td>
                    </tr>
                  `
                )
                .join("")}
            </tbody>
          </table>
        </article>

        <article class="table-card">
          <div class="section-title">
            <div>
              <p class="eyebrow">Catalogue</p>
              <h3>Produits existants</h3>
            </div>
          </div>
          <div class="admin-products-grid">
            ${data.products
              .map(
                (product) => `
                  <div class="list-row list-row-card">
                    <div>
                      <strong>${product.NomProd}</strong>
                      <div class="muted">${product.NomTypeProd} • ${product.GammeProd} • ${formatPrice(product.PrixProd)}</div>
                    </div>
                    <button class="btn-ghost" data-edit-product="${encodeURIComponent(JSON.stringify(product))}">
                      Modifier
                    </button>
                  </div>
                `
              )
              .join("")}
          </div>
        </article>
      </section>
    </div>
  `;
}
