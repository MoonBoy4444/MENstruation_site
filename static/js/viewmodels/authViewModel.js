export class AuthViewModel {
  constructor(api, store) {
    this.api = api;
    this.store = store;
  }

  async login(formData) {
    const user = await this.api.post("/api/auth/login", {
      email: formData.get("email"),
      password: formData.get("password"),
    });
    this.store.setState({ user, route: "home" });
    this.store.setFlash("success", `Bienvenue ${user.PrenomCli}.`);
  }

  async register(formData) {
    const user = await this.api.post("/api/auth/register", {
      NomCli: formData.get("nom"),
      PrenomCli: formData.get("prenom"),
      DateNaissanceCli: formData.get("dateNaissance"),
      MailCli: formData.get("email"),
      MdpCli: formData.get("password"),
      TelCli: formData.get("telephone"),
      FavoriCli: formData.get("favori"),
    });
    this.store.setState({ user, route: "profile" });
    this.store.setFlash("success", "Compte cree avec succes.");
  }
}
