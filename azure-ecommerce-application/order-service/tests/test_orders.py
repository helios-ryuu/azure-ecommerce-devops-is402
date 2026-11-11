import os
from unittest.mock import AsyncMock, patch
from fastapi.testclient import TestClient

os.environ.setdefault("SERVICE_VERSION", "v1-blue")
os.environ.setdefault("USER_SVC_URL", "http://user-svc:8080")
os.environ.setdefault("COSMOS_ENDPOINT", "https://fake.documents.azure.com:443/")
os.environ.setdefault("COSMOS_KEY", "fake-key")
os.environ.setdefault("COSMOS_DATABASE", "ecommerce")
os.environ.setdefault("COSMOS_CONTAINER", "orders")

from app.main import app  # noqa: E402


def _make_http_mock(status_code: int = 200, side_effect=None):
    mock_http = AsyncMock()
    mock_resp = AsyncMock()
    mock_resp.status_code = status_code
    if side_effect:
        mock_http.get = AsyncMock(side_effect=side_effect)
    else:
        mock_http.get = AsyncMock(return_value=mock_resp)
    return mock_http


@patch("app.main.cosmos_manager")
def test_create_order_success(mock_cosmos):
    mock_cosmos.save_order = AsyncMock(
        return_value={
            "id": "ord-abc123",
            "userId": "u123",
            "items": [{"product_id": "p1", "quantity": 2, "price": 100000}],
            "total_amount": 200000,
            "status": "PENDING",
            "created_at": "2026-11-11T00:00:00+00:00",
        }
    )

    mock_http = _make_http_mock(200)
    app.state.http_client = mock_http

    client = TestClient(app)
    response = client.post(
        "/api/orders",
        json={
            "user_id": "u123",
            "items": [{"product_id": "p1", "quantity": 2, "price": 100000}],
            "total_amount": 200000,
        },
    )

    assert response.status_code == 201
    body = response.json()
    assert body["status"] == "success"
    assert body["service_version"] == "v1-blue"
    assert body["data"]["userId"] == "u123"
    mock_cosmos.save_order.assert_awaited_once()


@patch("app.main.cosmos_manager")
def test_create_order_user_not_found(mock_cosmos):
    mock_cosmos.save_order = AsyncMock()

    mock_http = _make_http_mock(404)
    app.state.http_client = mock_http

    client = TestClient(app)
    response = client.post(
        "/api/orders",
        json={
            "user_id": "invalid-user",
            "items": [{"product_id": "p1", "quantity": 1, "price": 50000}],
            "total_amount": 50000,
        },
    )

    assert response.status_code == 400
    mock_cosmos.save_order.assert_not_awaited()


@patch("app.main.cosmos_manager")
def test_create_order_user_svc_down(mock_cosmos):
    mock_cosmos.save_order = AsyncMock()

    import httpx

    mock_http = _make_http_mock(
        side_effect=httpx.RequestError("boom", request=None)
    )
    app.state.http_client = mock_http

    client = TestClient(app)
    response = client.post(
        "/api/orders",
        json={
            "user_id": "u123",
            "items": [{"product_id": "p1", "quantity": 1, "price": 50000}],
            "total_amount": 50000,
        },
    )

    assert response.status_code == 503
    mock_cosmos.save_order.assert_not_awaited()
