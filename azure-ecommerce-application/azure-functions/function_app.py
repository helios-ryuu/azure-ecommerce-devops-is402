import json
import logging
import os
from datetime import datetime, timezone
import azure.functions as func
from azure.storage.blob import BlobServiceClient

app = func.FunctionApp()


@app.cosmos_db_trigger(
    arg_name="documents",
    database_name=os.getenv("COSMOS_DATABASE", "ecommerce"),
    container_name=os.getenv("COSMOS_CONTAINER", "orders"),
    connection="CosmosDBConnection",
    lease_container_name="leases",
    create_lease_container_if_not_exists=True,
    start_from_beginning=True,
)
def orders_auto_etl(documents: func.DocumentList) -> None:
    if not documents:
        return

    storage_conn = os.getenv("LAKEHOUSE_STORAGE_CONNECTION_STRING") or os.getenv("AzureWebJobsStorage", "")
    container_name = os.getenv("ETL_CONTAINER_NAME", "orders-etl")
    blob_service = BlobServiceClient.from_connection_string(storage_conn)
    container_client = blob_service.get_container_client(container_name)

    now_utc = datetime.now(timezone.utc)
    lines = []
    for doc in documents:
        raw = json.loads(doc.to_json())
        items = raw.get("items") or []
        record = {
            "orderId": raw.get("id"),
            "userId": str(raw.get("userId", "")),
            "totalAmount": float(raw.get("total_amount", 0.0)),
            "itemsCount": sum(int(i.get("quantity", 0)) for i in items),
            "status": raw.get("status", "PENDING"),
            "createdAt": raw.get("created_at", now_utc.isoformat()),
            "etlProcessedAt": now_utc.isoformat(),
        }
        lines.append(json.dumps(record, ensure_ascii=False))
        logging.info("Auto-ETL processed orderId=%s userId=%s total=%s", record["orderId"], record["userId"], record["totalAmount"])

    batch_blob_name = f"orders_{now_utc.strftime('%Y%m%d_%H%M%S_%f')}.jsonl"
    blob_client = container_client.get_blob_client(batch_blob_name)
    blob_client.upload_blob("\n".join(lines) + "\n", overwrite=True)
    logging.info("Uploaded %d ETL order records to %s/%s", len(lines), container_name, batch_blob_name)
