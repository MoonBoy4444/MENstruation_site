export class ProfileViewModel {
  constructor(api, store) {
    this.api = api;
    this.store = store;
  }

  async load() {
    const user = this.store.state.user;
    if (!user) {
      throw new Error("Aucun utilisateur connecte.");
    }
    return this.api.get(`/api/profile/${user.IdCli}`);
  }

  async save(formData) {
    const user = this.store.state.user;
    const profile = await this.api.put(`/api/profile/${user.IdCli}`, {
      NomCli: formData.get("nom"),
      PrenomCli: formData.get("prenom"),
      MailCli: formData.get("email"),
      TelCli: formData.get("telephone"),
      FavoriCli: formData.get("favori"),
    });

    this.store.setState({
      user: {
        ...user,
        NomCli: profile.client.NomCli,
        PrenomCli: profile.client.PrenomCli,
        MailCli: profile.client.MailCli,
        TelCli: profile.client.TelCli,
        FavoriCli: profile.client.FavoriCli,
      },
    });
    this.store.setFlash("success", "Profil mis a jour.");
    return profile;
  }
}
