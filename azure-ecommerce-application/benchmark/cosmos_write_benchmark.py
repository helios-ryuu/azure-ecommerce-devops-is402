import argparse
import json
import os
import statistics
import time
import uuid

from azure.cosmos import CosmosClient


def percentile(values: list[float], value: float) -> float:
    ordered = sorted(values)
    index = min(len(ordered) - 1, round((len(ordered) - 1) * value))
    return ordered[index]


def main() -> None:
    parser = argparse.ArgumentParser(description="Benchmark Azure Cosmos DB writes")
    parser.add_argument("-n", "--writes", type=int, default=100)
    parser.add_argument("--database", default="ecommerce")
    parser.add_argument("--container", default="orders")
    parser.add_argument("--user-id", default="benchmark-23520214")
    parser.add_argument("--keep-data", action="store_true")
    args = parser.parse_args()

    endpoint = os.environ["COSMOS_ENDPOINT"]
    key = os.environ["COSMOS_KEY"]
    client = CosmosClient(endpoint, credential=key)
    container = (
        client.get_database_client(args.database)
        .get_container_client(args.container)
    )

    latencies: list[float] = []
    created_ids: list[str] = []
    started = time.perf_counter()

    try:
        for sequence in range(args.writes):
            item_id = f"benchmark-{uuid.uuid4()}"
            document = {
                "id": item_id,
                "userId": args.user_id,
                "productId": 1,
                "quantity": 1,
                "total": 499000,
                "benchmarkSequence": sequence,
            }
            before = time.perf_counter()
            container.create_item(document)
            latencies.append((time.perf_counter() - before) * 1000)
            created_ids.append(item_id)

        elapsed = time.perf_counter() - started
        result = {
            "mode": "cosmos_write",
            "writes": args.writes,
            "latency_avg_ms": round(statistics.mean(latencies), 3),
            "latency_p50_ms": round(percentile(latencies, 0.50), 3),
            "latency_p95_ms": round(percentile(latencies, 0.95), 3),
            "throughput_writes_per_sec": round(args.writes / elapsed, 2),
        }
        print(json.dumps(result, ensure_ascii=False))
    finally:
        if not args.keep_data:
            for item_id in created_ids:
                container.delete_item(item=item_id, partition_key=args.user_id)
            print(f"Deleted {len(created_ids)} benchmark records")


if __name__ == "__main__":
    main()
