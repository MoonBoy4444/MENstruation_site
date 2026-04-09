export class ApiClient {
  async request(path, options = {}) {
    const response = await fetch(path, {
      headers: {
        "Content-Type": "application/json",
        ...(options.headers || {}),
      },
      ...options,
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(data.error || "Erreur serveur");
    }
    return data;
  }

  get(path) {
    return this.request(path);
  }

  post(path, payload) {
    return this.request(path, {
      method: "POST",
      body: JSON.stringify(payload),
    });
  }

  put(path, payload) {
    return this.request(path, {
      method: "PUT",
      body: JSON.stringify(payload),
    });
  }
}
