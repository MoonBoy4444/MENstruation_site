import sqlite3

from backend.config import DATABASE_PATH, SCHEMA_PATH, SEED_PATH


REQUIRED_PRODUCT_COLUMNS = {
    "CouleurProd",
    "GammeProd",
    "AbsorptionProd",
    "UsageProd",
    "PointsFortsProd",
    "BadgeProd",
}


def get_connection() -> sqlite3.Connection:
    connection = sqlite3.connect(DATABASE_PATH)
    connection.row_factory = sqlite3.Row
    connection.execute("PRAGMA foreign_keys = ON;")
    return connection


def _must_rebuild_database() -> bool:
    if not DATABASE_PATH.exists():
        return False

    try:
        with get_connection() as connection:
            table_exists = connection.execute(
                """
                SELECT name
                FROM sqlite_master
                WHERE type = 'table' AND name = 'produits'
                """
            ).fetchone()
            if table_exists is None:
                return True

            columns = {
                row["name"]
                for row in connection.execute("PRAGMA table_info(produits)").fetchall()
            }
            return not REQUIRED_PRODUCT_COLUMNS.issubset(columns)
    except sqlite3.DatabaseError:
        return True


def initialize_database() -> None:
    DATABASE_PATH.parent.mkdir(parents=True, exist_ok=True)

    if _must_rebuild_database():
        DATABASE_PATH.unlink(missing_ok=True)

    with get_connection() as connection:
        schema_sql = SCHEMA_PATH.read_text(encoding="utf-8")
        connection.executescript(schema_sql)
        seed_sql = SEED_PATH.read_text(encoding="utf-8")
        connection.executescript(seed_sql)
        connection.commit()
