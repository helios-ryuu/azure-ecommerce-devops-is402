import os

import pytest

from app.cosmos_client import CosmosManager


@pytest.mark.asyncio
async def test_sqlite_order_survives_reconnect(tmp_path, monkeypatch):
    database = tmp_path / "orders.db"
    monkeypatch.setenv("ORDER_STORAGE", "sqlite")
    monkeypatch.setenv("ORDER_SQLITE_PATH", str(database))
    order = {
        "id": "order-1",
        "userId": "1",
        "items": [{"product_id": "1", "quantity": 2, "price": 499000}],
        "total_amount": 998000,
        "status": "PENDING",
        "created_at": "2026-11-16T00:00:00+00:00",
    }

    first = CosmosManager()
    await first.connect()
    await first.save_order(order)
    await first.close()

    second = CosmosManager()
    await second.connect()
    assert await second.get_order("order-1") == order
    assert await second.get_order("missing") is None
    await second.close()
