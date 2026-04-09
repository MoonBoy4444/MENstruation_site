const SESSION_KEY = "gamerdry-session";
const CART_KEY = "gamerdry-cart";

export class AppStore {
  constructor() {
    this.state = {
      route: "home",
      user: this.loadItem(SESSION_KEY),
      selectedProductId: null,
      cart: this.loadItem(CART_KEY) || [],
      flash: null,
    };
    this.listeners = new Set();
  }

  subscribe(listener) {
    this.listeners.add(listener);
    return () => this.listeners.delete(listener);
  }

  notify() {
    for (const listener of this.listeners) {
      listener(this.state);
    }
  }

  setState(patch) {
    this.state = { ...this.state, ...patch };
    if (Object.prototype.hasOwnProperty.call(patch, "user")) {
      this.saveItem(SESSION_KEY, this.state.user);
    }
    if (Object.prototype.hasOwnProperty.call(patch, "cart")) {
      this.saveItem(CART_KEY, this.state.cart);
    }
    this.notify();
  }

  setFlash(type, message) {
    this.setState({ flash: { type, message } });
    window.setTimeout(() => {
      if (this.state.flash?.message === message) {
        this.setState({ flash: null });
      }
    }, 3200);
  }

  loadItem(key) {
    try {
      return JSON.parse(window.localStorage.getItem(key) || "null");
    } catch {
      return null;
    }
  }

  saveItem(key, value) {
    if (value === null || value === undefined) {
      window.localStorage.removeItem(key);
      return;
    }
    window.localStorage.setItem(key, JSON.stringify(value));
  }
}
