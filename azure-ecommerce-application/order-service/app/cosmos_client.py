import os
from azure.cosmos.aio import CosmosClient
from azure.cosmos.exceptions import CosmosHttpResponseError


class CosmosManager:
    def __init__(self):
        self.endpoint = os.getenv("COSMOS_ENDPOINT", "")
        self.key = os.getenv("COSMOS_KEY", "")
        self.db_name = os.getenv("COSMOS_DATABASE", "ecommerce")
        self.container_name = os.getenv("COSMOS_CONTAINER", "orders")
        self.client: CosmosClient | None = None

    async def connect(self):
        if not self.endpoint or not self.key:
            raise ValueError("COSMOS_ENDPOINT và COSMOS_KEY không được để trống.")
        self.client = CosmosClient(self.endpoint, credential=self.key)

    async def close(self):
        if self.client:
            await self.client.close()

    async def save_order(self, order_doc: dict) -> dict:
        if not self.client:
            raise RuntimeError("CosmosClient chưa được khởi tạo.")
        database = self.client.get_database_client(self.db_name)
        container = database.get_container_client(self.container_name)
        return await container.create_item(body=order_doc)


cosmos_manager = CosmosManager()
