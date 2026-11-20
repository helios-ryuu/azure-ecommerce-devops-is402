-- =============================================================================
-- Kịch bản SQL trên Azure Synapse Analytics Serverless SQL Pool (synw-is402)
-- Tài khoản ADLS Gen2: stis402lakehouse (Japan East)
-- =============================================================================

IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'ecommerce_bi')
BEGIN
    CREATE DATABASE ecommerce_bi;
END
GO

USE ecommerce_bi;
GO

-- 1. Tạo View vw_orders_summary đọc dữ liệu đơn hàng Auto-ETL từ Azure Functions
CREATE OR ALTER VIEW dbo.vw_orders_summary AS
SELECT
    JSON_VALUE(doc.line, '$.orderId') AS orderId,
    JSON_VALUE(doc.line, '$.userId') AS userId,
    CAST(JSON_VALUE(doc.line, '$.totalAmount') AS DECIMAL(18, 2)) AS totalAmount,
    CAST(JSON_VALUE(doc.line, '$.itemsCount') AS INT) AS itemsCount,
    JSON_VALUE(doc.line, '$.status') AS orderStatus,
    TRY_CAST(JSON_VALUE(doc.line, '$.createdAt') AS DATETIME2) AS createdAt,
    TRY_CAST(JSON_VALUE(doc.line, '$.etlProcessedAt') AS DATETIME2) AS etlProcessedAt
FROM OPENROWSET(
    BULK 'https://stis402lakehouse.dfs.core.windows.net/orders-etl/*.jsonl',
    FORMAT = 'CSV',
    FIELDQUOTE = '0x0b',
    FIELDTERMINATOR = '0x0b',
    ROWTERMINATOR = '0x0a'
) WITH (
    line VARCHAR(MAX)
) AS doc;
GO

-- 2. Truy vấn Benchmark trên tập dữ liệu lớn (> 4GB CSV) tại benchmark-dataset/ (Thực nghiệm 1)
-- So sánh thời gian thực thi phân tán trên Cloud Synapse với máy Local:
SELECT
    category,
    COUNT(*) AS total_orders,
    SUM(CAST(total_amount AS DECIMAL(18, 2))) AS total_revenue,
    AVG(CAST(total_amount AS DECIMAL(18, 2))) AS avg_order_value
FROM OPENROWSET(
    BULK 'https://stis402lakehouse.dfs.core.windows.net/benchmark-dataset/*.csv',
    FORMAT = 'CSV',
    PARSER_VERSION = '2.0',
    HEADER_ROW = TRUE
) AS rows
GROUP BY category
ORDER BY total_revenue DESC;
GO
