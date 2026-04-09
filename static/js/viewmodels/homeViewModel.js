export class HomeViewModel {
  constructor(api) {
    this.api = api;
  }

  async load() {
    return this.api.get("/api/home");
  }
}
