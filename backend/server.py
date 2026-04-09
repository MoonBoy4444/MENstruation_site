from __future__ import annotations

import json
import mimetypes
from http import HTTPStatus
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import parse_qs, urlparse

from backend.config import HOST, PORT, STATIC_DIR
from backend.database import initialize_database
from backend.services import AppService


service = AppService()


class AppHandler(BaseHTTPRequestHandler):
    def do_GET(self) -> None:
        parsed = urlparse(self.path)

        if parsed.path.startswith("/api/"):
            self._handle_api_get(parsed)
            return

        self._serve_static(parsed.path)

    def do_POST(self) -> None:
        parsed = urlparse(self.path)
        payload = self._read_json_body()

        try:
            if parsed.path == "/api/auth/login":
                self._json_response(service.login(payload))
            elif parsed.path == "/api/auth/register":
                self._json_response(service.register(payload), status=HTTPStatus.CREATED)
            elif parsed.path == "/api/reviews":
                self._json_response(service.add_review(payload), status=HTTPStatus.CREATED)
            elif parsed.path == "/api/orders":
                self._json_response(service.create_order(payload), status=HTTPStatus.CREATED)
            elif parsed.path == "/api/admin/products":
                self._json_response(service.save_product(payload), status=HTTPStatus.CREATED)
            else:
                self._json_error("Route introuvable", HTTPStatus.NOT_FOUND)
        except ValueError as error:
            self._json_error(str(error), HTTPStatus.BAD_REQUEST)
        except KeyError as error:
            self._json_error(f"Champ manquant: {error}", HTTPStatus.BAD_REQUEST)

    def do_PUT(self) -> None:
        parsed = urlparse(self.path)
        payload = self._read_json_body()

        try:
            if parsed.path.startswith("/api/profile/"):
                client_id = int(parsed.path.split("/")[-1])
                self._json_response(service.update_profile(client_id, payload))
            elif parsed.path == "/api/admin/products":
                self._json_response(service.save_product(payload))
            else:
                self._json_error("Route introuvable", HTTPStatus.NOT_FOUND)
        except ValueError as error:
            self._json_error(str(error), HTTPStatus.BAD_REQUEST)

    def log_message(self, format: str, *args) -> None:
        return

    def _handle_api_get(self, parsed) -> None:
        try:
            if parsed.path == "/api/home":
                self._json_response(service.home_payload())
            elif parsed.path == "/api/catalog":
                params = parse_qs(parsed.query)
                self._json_response(
                    service.get_catalog(
                        search=params.get("search", [""])[0],
                        category_id=params.get("category", [""])[0],
                    )
                )
            elif parsed.path.startswith("/api/products/"):
                product_id = int(parsed.path.split("/")[-1])
                self._json_response(service.get_product_detail(product_id))
            elif parsed.path.startswith("/api/profile/"):
                client_id = int(parsed.path.split("/")[-1])
                self._json_response(service.get_profile(client_id))
            elif parsed.path.startswith("/api/orders/"):
                client_id = int(parsed.path.split("/")[-1])
                self._json_response(service.get_orders(client_id))
            elif parsed.path == "/api/admin/dashboard":
                self._json_response(service.admin_dashboard())
            elif parsed.path == "/api/admin/clients":
                self._json_response(service.admin_clients())
            elif parsed.path == "/api/admin/products":
                self._json_response(service.admin_products())
            else:
                self._json_error("Route introuvable", HTTPStatus.NOT_FOUND)
        except ValueError as error:
            self._json_error(str(error), HTTPStatus.BAD_REQUEST)

    def _read_json_body(self) -> dict:
        content_length = int(self.headers.get("Content-Length", "0"))
        raw_body = self.rfile.read(content_length) if content_length else b"{}"
        return json.loads(raw_body.decode("utf-8"))

    def _json_response(self, payload, status: HTTPStatus = HTTPStatus.OK) -> None:
        body = json.dumps(payload, ensure_ascii=True).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def _json_error(self, message: str, status: HTTPStatus) -> None:
        self._json_response({"error": message}, status=status)

    def _serve_static(self, path: str) -> None:
        relative_path = "index.html" if path in ("/", "") else path.lstrip("/")
        file_path = (STATIC_DIR / relative_path).resolve()

        if not str(file_path).startswith(str(STATIC_DIR.resolve())) or not file_path.exists():
            self.send_error(HTTPStatus.NOT_FOUND, "Fichier introuvable")
            return

        content_type, _ = mimetypes.guess_type(str(file_path))
        data = Path(file_path).read_bytes()
        self.send_response(HTTPStatus.OK)
        self.send_header("Content-Type", content_type or "application/octet-stream")
        self.send_header("Content-Length", str(len(data)))
        self.end_headers()
        self.wfile.write(data)


def run() -> None:
    initialize_database()
    last_error = None

    for port in range(PORT, PORT + 10):
        try:
            server = ThreadingHTTPServer((HOST, port), AppHandler)
            print(f"MENstruation disponible sur http://{HOST}:{port}")
            server.serve_forever()
            return
        except OSError as error:
            last_error = error
            continue

    raise last_error if last_error else RuntimeError("Impossible de demarrer le serveur")
