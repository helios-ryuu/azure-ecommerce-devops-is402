import argparse
import json
import os
import statistics
import time
from concurrent.futures import ThreadPoolExecutor
import psycopg_pool
import redis

DB_DSN = os.getenv("DB_DSN", "postgresql://ecommerce:ecommerce@localhost:5432/ecommerce")
REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/0")
CACHE_KEY = "catalog:products"


def percentile(values: list[float], p: float) -> float:
    ordered = sorted(values)
    return ordered[min(len(ordered) - 1, round((len(ordered) - 1) * p))]


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("-n", "--requests", type=int, default=1000)
    ap.add_argument("-c", "--concurrency", type=int, default=20)
    args = ap.parse_args()

    pool = psycopg_pool.ConnectionPool(DB_DSN, min_size=1, max_size=args.concurrency)
    r = redis.from_url(REDIS_URL, decode_responses=True)

    def db_read() -> float:
        t = time.perf_counter()
        with pool.connection() as conn, conn.cursor() as cur:
            cur.execute("SELECT id, name, description, price, image_url FROM products ORDER BY id")
            cur.fetchall()
        return (time.perf_counter() - t) * 1000

    def cache_read() -> float:
        t = time.perf_counter()
        data = r.get(CACHE_KEY)
        if data is None:
            with pool.connection() as conn, conn.cursor() as cur:
                cur.execute("SELECT id, name, description, price, image_url FROM products ORDER BY id")
                data = json.dumps(cur.fetchall(), default=str)
                r.setex(CACHE_KEY, 300, data)
        return (time.perf_counter() - t) * 1000

    for name, fn in [("postgresql", db_read), ("redis_cache_aside", cache_read)]:
        if name.startswith("redis"):
            r.delete(CACHE_KEY)
            fn()  # Warm cache
        started = time.perf_counter()
        with ThreadPoolExecutor(max_workers=args.concurrency) as ex:
            lat = list(ex.map(lambda _: fn(), range(args.requests)))
        elapsed = time.perf_counter() - started
        print(
            json.dumps(
                {
                    "mode": name,
                    "requests": args.requests,
                    "concurrency": args.concurrency,
                    "latency_avg_ms": round(statistics.mean(lat), 3),
                    "latency_p50_ms": round(percentile(lat, 0.5), 3),
                    "latency_p95_ms": round(percentile(lat, 0.95), 3),
                    "throughput_rps": round(args.requests / elapsed, 2),
                },
                ensure_ascii=False,
            )
        )
    pool.close()


if __name__ == "__main__":
    main()
