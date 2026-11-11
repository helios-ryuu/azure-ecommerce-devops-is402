import os
import uuid
from datetime import datetime, timezone
from contextlib import asynccontextmanager
from fastapi import FastAPI, HTTPException, status
import httpx
from azure.cosmos.exceptions import CosmosHttpResponseError

from app.schemas import CreateOrderRequest, OrderResponse
from app.cosmos_client import cosmos_manager

SERVICE_VERSION = os.getenv("SERVICE_VERSION", "v1-blue")
USER_SVC_URL = os.getenv("USER_SVC_URL", "http://user-svc:8080")


@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup: Khởi tạo kết nối Cosmos và HTTPX client dùng chung
    await cosmos_manager.connect()
    app.state.http_client = httpx.AsyncClient(timeout=5.0)
    yield
    # Shutdown: Đóng kết nối
    await app.state.http_client.aclose()
    await cosmos_manager.close()


app = FastAPI(title="Order Service", lifespan=lifespan)


@app.get("/health")
async def health_check():
    return {"status": "ok", "version": SERVICE_VERSION}


@app.post("/api/orders", response_model=OrderResponse, status_code=status.HTTP_201_CREATED)
async def create_order(payload: CreateOrderRequest):
    http_client: httpx.AsyncClient = app.state.http_client

    # 1. Kiểm tra User nội bộ qua user-svc
    try:
        user_res = await http_client.get(f"{USER_SVC_URL}/api/users/verify/{payload.user_id}")
        if user_res.status_code == 404:
            raise HTTPException(
                status_code=400,
                detail=f"User ID '{payload.user_id}' không tồn tại hoặc không hợp lệ.",
            )
        elif user_res.status_code != 200:
            raise HTTPException(
                status_code=502,
                detail=f"user-svc trả về lỗi HTTP {user_res.status_code}",
            )
    except httpx.RequestError as exc:
        raise HTTPException(
            status_code=503,
            detail=f"Không thể kết nối tới user-svc: {str(exc)}",
        )

    # 2. Tạo document đơn hàng (trường 'userId' đúng chuẩn Partition Key /userId)
    order_id = str(uuid.uuid4())
    order_doc = {
        "id": order_id,
        "userId": payload.user_id,
        "items": [item.model_dump() for item in payload.items],
        "total_amount": payload.total_amount,
        "status": "PENDING",
        "created_at": datetime.now(timezone.utc).isoformat(),
    }

    # 3. Ghi dữ liệu vào Azure Cosmos DB
    try:
        saved_doc = await cosmos_manager.save_order(order_doc)
    except CosmosHttpResponseError as e:
        raise HTTPException(status_code=500, detail=f"Lỗi thao tác Cosmos DB: {e.message}")
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Lỗi hệ thống: {str(e)}")

    return OrderResponse(
        status="success",
        message="Đơn hàng đã được tạo thành công.",
        service_version=SERVICE_VERSION,
        data=saved_doc,
    )
