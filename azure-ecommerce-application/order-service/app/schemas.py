from typing import List
from pydantic import BaseModel, Field

class CartItem(BaseModel):
    product_id: str
    quantity: int = Field(gt=0, description="Số lượng phải lớn hơn 0")
    price: float = Field(gt=0, description="Đơn giá phải lớn hơn 0")

class CreateOrderRequest(BaseModel):
    user_id: str
    items: List[CartItem]
    total_amount: float = Field(ge=0, description="Tổng tiền không được âm")

class OrderResponse(BaseModel):
    status: str
    message: str
    service_version: str
    data: dict
