import os
import json
import aiosqlite
from azure.cosmos.aio import CosmosClient
from azure.cosmos.exceptions import CosmosHttpResponseError


class CosmosManager:
    def __init__(self):
        self.storage_mode = os.getenv("ORDER_STORAGE", "cosmos").lower()
        self.endpoint = os.getenv("COSMOS_ENDPOINT", "")
        self.key = os.getenv("COSMOS_KEY", "")
        self.db_name = os.getenv("COSMOS_DATABASE", "ecommerce")
        self.container_name = os.getenv("COSMOS_CONTAINER", "orders")
        self.sqlite_path = os.getenv("ORDER_SQLITE_PATH", "/data/orders.db")
        self.client: CosmosClient | None = None
        self.sqlite: aiosqlite.Connection | None = None
        self.local_orders: dict[str, dict] = {}

    async def connect(self):
        if self.storage_mode == "memory":
            return
        if self.storage_mode == "sqlite":
            os.makedirs(os.path.dirname(self.sqlite_path) or ".", exist_ok=True)
            self.sqlite = await aiosqlite.connect(self.sqlite_path)
            await self.sqlite.execute(
                "CREATE TABLE IF NOT EXISTS orders (id TEXT PRIMARY KEY, user_id TEXT NOT NULL, payload TEXT NOT NULL, created_at TEXT NOT NULL)"
            )
            await self.sqlite.commit()
            return
        if not self.endpoint or not self.key:
            raise ValueError(
                "COSMOS_ENDPOINT và COSMOS_KEY không được để trống khi ORDER_STORAGE=cosmos. "
                "Dùng ORDER_STORAGE=memory để chạy local."
            )
        self.client = CosmosClient(self.endpoint, credential=self.key)

    async def close(self):
        if self.sqlite:
            await self.sqlite.close()
            self.sqlite = None
        if self.client:
            await self.client.close()

    async def save_order(self, order_doc: dict) -> dict:
        if self.storage_mode == "memory":
            saved = order_doc.copy()
            self.local_orders[saved["id"]] = saved
            return saved
        if self.storage_mode == "sqlite":
            if not self.sqlite:
                raise RuntimeError("SQLite chưa được khởi tạo.")
            saved = order_doc.copy()
            await self.sqlite.execute(
                "INSERT INTO orders (id, user_id, payload, created_at) VALUES (?, ?, ?, ?)",
                (saved["id"], saved["userId"], json.dumps(saved), saved["created_at"]),
            )
            await self.sqlite.commit()
            return saved
        if not self.client:
            raise RuntimeError("CosmosClient chưa được khởi tạo.")
        database = self.client.get_database_client(self.db_name)
        container = database.get_container_client(self.container_name)
        return await container.create_item(body=order_doc)

    async def get_order(self, order_id: str) -> dict | None:
        if self.storage_mode == "memory":
            return self.local_orders.get(order_id)
        if self.storage_mode == "sqlite":
            if not self.sqlite:
                raise RuntimeError("SQLite chưa được khởi tạo.")
            cursor = await self.sqlite.execute("SELECT payload FROM orders WHERE id = ?", (order_id,))
            row = await cursor.fetchone()
            await cursor.close()
            return json.loads(row[0]) if row else None
        if not self.client:
            raise RuntimeError("CosmosClient chưa được khởi tạo.")
        database = self.client.get_database_client(self.db_name)
        container = database.get_container_client(self.container_name)
        query = "SELECT * FROM c WHERE c.id = @id"
        parameters = [{"name": "@id", "value": order_id}]
        async for item in container.query_items(query=query, parameters=parameters):
            return item
        return None


cosmos_manager = CosmosManager()
