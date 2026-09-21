# Hướng Dẫn Thực Hành Lab: Azure Virtual Network Peering & Thực Nghiệm ETL (`avn-with-vnet-peering`)

> **Môn học:** IS402 - Điện toán đám mây (Trường ĐH Công Nghệ Thông Tin - UIT)  
> **Repository:** `azure-ecommerce-devops-is402`  
> **Dự án:** `avn-with-vnet-peering` (Thực hành Lab & Thực nghiệm ETL)  
> **Chủ đề:** Kết nối mạng ảo bằng Global VNet Peering & Thực nghiệm truyền tải, xử lý dữ liệu lớn (>1GB)  
> **Tài liệu gốc tham khảo:** `03_Connect Azure Virtual Networks with VNet Peering.docx`  
> **Resource Group:** `rg-asm`  
> **Cấu hình máy ảo:** `Standard_B2as_v2` / `Standard_B2s_v2` (2 vCPU, 8 GiB RAM - B-series v2 thế hệ mới)

---

## Mục Lục
1. [Lab Description (Mô Tả Bài Lab)](#1-lab-description-mô-tả-bài-lab)
2. [Lab Objectives (Mục Tiêu Bài Lab)](#2-lab-objectives-mục-tiêu-bài-lab)
3. [Bản Chất Kỹ Thuật & Các Khái Niệm Cốt Lõi](#3-bản-chất-kỹ-thuật--các-khái-niệm-cốt-lõi)
   * [3.1 Khái niệm: Các Khái Niệm Mạng Cốt Lõi & Xử Lý Dữ Liệu Lớn](#31-khái-niệm-các-khái-niệm-mạng-cốt-lõi--xử-lý-dữ-liệu-lớn)
   * [3.2 Vấn Đề Kỹ Thuật và Giải Pháp (Environment Before & After)](#32-vấn-đề-kỹ-thuật-và-giải-pháp-environment-before--after)
4. [Các Bước Thực Hiện Chi Tiết (Lab Steps)](#4-các-bước-thực-hiện-chi-tiết-lab-steps)
   * [Bước 1: Logging in & Creating Resource Group](#bước-1-logging-in-to-the-microsoft-azure-portal--creating-resource-group-đăng-nhập--tạo-resource-group)
   * [Bước 2: Creating the First Network Resources](#bước-2-creating-the-first-network-resources-tạo-tài-nguyên-mạng-1---environment-before)
   * [Bước 3: Creating the Second Network Resources](#bước-3-creating-the-second-network-resources-tạo-tài-nguyên-mạng-2---environment-after)
   * [Bước 4: Attempting to Make a Connection Using Serial Console](#bước-4-attempting-to-make-a-connection-using-serial-console-thử-kết-nối-trước-khi-peering)
   * [Bước 5: Initiating the Virtual Network Peering Connection](#bước-5-initiating-the-virtual-network-peering-connection-thiết-lập-kết-nối-vnet-peering)
   * [Bước 6: Testing Peering & Big Data Experiment](#bước-6-testing-peering--big-data-experiment-kiểm-thử-peering--thực-nghiệm-dữ-liệu-lớn)
5. [Dọn Dẹp Tài Nguyên (Clean Up)](#5-dọn-dẹp-tài-nguyên-clean-up)

---

## 1. Lab Description (Mô Tả Bài Lab)

**Azure Virtual Network (VNet) Peering** cho phép kết nối liền mạch hai mạng ảo Azure với nhau. Sau khi thiết lập Peering, hai mạng ảo sẽ hoạt động như một mạng duy nhất về mặt kết nối:
* Các thiết bị và máy ảo trong hai mạng có thể giao tiếp trực tiếp với nhau thông qua **địa chỉ IP nội bộ (Private IP addresses)**.
* Hai mạng có thể nằm trong cùng một khu vực (Region) hoặc khác khu vực địa lý (**Global VNet Peering**).
* Toàn bộ lưu lượng truyền giữa các mạng qua Peering được định tuyến hoàn toàn trên hạ tầng đường trục cáp quang riêng của Microsoft (**Microsoft private backbone network**), không bao giờ đi ra ngoài mạng Internet công cộng, đảm bảo độ an toàn bảo mật cao nhất, độ trễ tối thiểu và hiệu năng băng thông vượt trội.

Bài thực hành thiết lập liên kết giữa hai mạng ảo đám mây độc lập được chuẩn hóa theo mã số:
1. **Mạng 1 (`vnet-1` tại `eastasia` - Hong Kong):** Phân mạng `subnet-1 (10.0.0.0/24)`, nhóm bảo mật `nsg-1`, IP công cộng `pip-vm-1` và máy chủ dữ liệu đích `vm-1 (10.0.0.100)`. Đóng vai trò môi trường ban đầu (Environment Before) - mô phỏng phân hệ dịch vụ / chi nhánh thứ nhất.
2. **Mạng 2 (`vnet-2` tại `koreacentral` - Seoul):** Phân mạng `subnet-2 (192.168.0.0/24)` và máy trạm phân tích `vm-2`. Đóng vai trò môi trường sau triển khai (Environment After) - mô phỏng phân hệ phân tích / chi nhánh thứ hai.
3. Bạn sẽ tiến hành khởi tạo tài nguyên cho Mạng 2, thiết lập kết nối **Global VNet Peering** liên vùng, kiểm chứng kết nối thực tế qua **Azure Serial Console**, nạp bộ dữ liệu y tế cộng đồng quy mô lớn (**>1.2 GB**) từ máy chủ nội bộ qua **Tailscale Mesh VPN** và thực hiện pipeline **ETL tự động** (Extract - Transform - Load), chuyển đổi sang định dạng nén **Apache Parquet (Snappy)** và đo đạc các chỉ số hiệu năng (Network Transfer, I/O Throughput, Compression Ratio) đáp ứng trọn vẹn yêu cầu Rubric môn học **IS402 - Điện toán đám mây**.

> [!NOTE]
> **Quy ước đặt tên tài nguyên (Resource Naming Convention):**  
> Toàn bộ tài nguyên trên hệ thống Azure được đánh số nhất quán (`vnet-1`, `subnet-1`, `nsg-1`, `pip-vm-1`, `vm-1`, `vnet-2`, `subnet-2`, `vm-2`) để đảm bảo tính chuẩn mực, tính trừu tượng linh hoạt cho mọi trường hợp nghiệp vụ thực tế, tuyệt đối không gán cứng theo tên phòng ban cụ thể.

---

## 2. Lab Objectives (Mục Tiêu Bài Lab)

Sau khi hoàn thành bài thực hành này, sinh viên sẽ đạt được các chuẩn đầu ra sau:

1. **Về Mạng Đám Mây (Cloud Networking):**
   * Nắm vững kiến trúc mạng ảo Azure SDN, phân mạng con (Subnet), Default Gateway ảo và cơ chế kiểm soát truy cập qua Network Security Groups (NSG).
   * Cấu hình thành thạo **Global VNet Peering** hai chiều giữa các Region khác nhau trên Azure Portal.
   * Hiểu rõ điều kiện không gian địa chỉ không trùng lặp (**Non-overlapping IP**) và các giải pháp thay thế (VPN Gateway NAT, Private Endpoints) khi mạng bị chồng lấn.
   * Sử dụng thành thạo **Azure VM Serial Console** để kiểm thử kết nối mức mạng độc lập với hệ điều hành.

2. **Về Xử Lý & Lưu Trữ Dữ Liệu Lớn (Big Data & Cloud Storage):**
   * **Tiêu chí 1 (Dung lượng & Mức tải):** Xử lý bộ dữ liệu quy mô thực tế **> 1.0 GB** (~10 triệu dòng bản ghi) trên máy ảo cấu hình bộ nhớ **8 GiB RAM**.
   * **Tiêu chí 2 (Cơ sở lý thuyết & Dịch vụ Cloud):** Nắm vững kiến trúc lưu trữ dữ liệu đám mây (Cloud Data Lake), Virtual Network Service Endpoints, cơ chế định tuyến không NAT của Peering và mạng riêng ảo Tailscale Mesh VPN.
   * **Tiêu chí 3 (Mô hình dữ liệu & Tối ưu hóa ETL):** Triển khai luồng ETL tự động làm sạch, lọc, chuẩn hóa kiểu dữ liệu; tối ưu hóa lưu trữ bằng cách chuyển đổi từ CSV thô sang **Apache Parquet (Snappy)**, nâng cao tốc độ đọc/ghi I/O và tiết kiệm hơn **75%** dung lượng đĩa.
   * **Tiêu chí 5 (Báo cáo & Demo):** Thu thập và tổng hợp bảng số liệu thực nghiệm đo đạc chính xác (Độ trễ RTT, Băng thông truyền tải MB/s, Tốc độ đọc/ghi I/O, Tỷ lệ nén dữ liệu) đưa trực tiếp vào Báo cáo KLTN/Word.

---

## 3. Bản Chất Kỹ Thuật & Các Khái Niệm Cốt Lõi

### 3.1 Khái niệm
### 3.1 Khái niệm: Các Khái Niệm Mạng Cốt Lõi & Xử Lý Dữ Liệu Lớn

Trước khi bắt tay vào triển khai thực tế, bạn cần nắm vững các khái niệm nền tảng:

1. **Virtual Network (VNet):**
   * Là ranh giới cô lập mạng ảo (Isolation Boundary) cấp cao nhất trong kiến trúc mạng do phần mềm định nghĩa (SDN) của Azure.
   * Mặc định, các tài nguyên nằm trong các VNet khác nhau hoàn toàn bị cô lập và không thể tự nhìn thấy nhau.
   * Trong bài lab có 2 VNet: `vnet-1` (`10.0.0.0/16`) và `vnet-2` (`192.168.0.0/20`).

2. **Subnet (Phân mạng con):**
   * Là sự chia nhỏ không gian địa chỉ của VNet thành các vùng mạng logic để gán card mạng (NIC) cho máy ảo.
   * Trong bài lab: `subnet-1` (`10.0.0.0/24`) thuộc `vnet-1`, và `subnet-2` (`192.168.0.0/24`) thuộc `vnet-2`.

3. **Private IP Address (Địa chỉ IP riêng RFC 1918):**
   * Địa chỉ nội bộ dùng để liên lạc trong mạng riêng và không thể định tuyến trực tiếp ra ngoài Internet công cộng (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`).

4. **Default Gateway / Virtual Router (Bộ định tuyến ảo):**
   * Trong mỗi subnet của Azure, địa chỉ IP đầu tiên (ví dụ `.1`) luôn được dành riêng làm Default Gateway cho toàn bộ subnet đó để chuyển tiếp gói tin Layer 3.
   * Mỗi Virtual Network được điều phối bởi một Bộ định tuyến ảo (Virtual Router) do phần mềm định nghĩa (SDN) nhằm quản lý bảng định tuyến hệ thống (System Routes) và chuyển tiếp lưu lượng mạng.

5. **Microsoft Backbone Network (Đường trục riêng của Microsoft):**
   * Hạ tầng mạng cáp quang riêng quy mô toàn cầu kết nối giữa tất cả các trung tâm dữ liệu và Region của Azure, mang lại băng thông cực lớn và độ trễ cực thấp.

6. **VNet Peering:**
   * Cơ chế ghép nối 2 mạng ảo ngang hàng thông qua việc Azure SDN tự động nạp các tuyến đường trực tiếp vào bảng định tuyến hệ thống.
   * Lưu lượng di chuyển trực tiếp giữa các card mạng ảo (NIC) qua đường trục cáp quang riêng mà không cần đi qua Gateway trung gian hay phần cứng NAT.

7. **Khi nào hai dải địa chỉ IP mới gọi là trùng lặp (Overlapping IP Addresses)?**
   * Hai mạng được coi là **trùng lặp địa chỉ (Overlapping)** khi **tồn tại ít nhất một địa chỉ IP có mặt ở cả hai mạng**.
   * *Trường hợp 1 (Trùng lặp hoàn toàn - Identical):* Cả hai VNet cùng dùng chung dải `10.0.0.0/16`.
   * *Trường hợp 2 (Bao trùm - Enclosure):* VNet A là `10.0.0.0/16` (`10.0.0.0` → `10.0.255.255`) và VNet B là `10.0.1.0/24` (`10.0.1.0` → `10.0.1.255`). Toàn bộ VNet B nằm gọn bên trong VNet A.
   * *Trường hợp 3 (Giao nhau một phần - Partial Overlap):* VNet A là `10.0.0.0/23` (`10.0.0.0` → `10.0.1.255`) và VNet B là `10.0.1.0/24` (`10.0.1.0` → `10.0.1.255`).
   * *Vì sao Azure cấm Peering khi trùng IP?* VNet Peering là định tuyến tầng 3 (Layer 3) không dùng NAT. Nếu trùng IP, Virtual Router của Azure không thể biết gói tin gửi tới địa chỉ đó là dành cho máy nội bộ hay máy bên VNet đối tác (gây xung đột định tuyến - Routing Conflict).
   * *Trong bài lab:* `vnet-1` (`10.0.0.0/16`) và `vnet-2` (`192.168.0.0/20`) hoàn toàn tách biệt, không giao nhau bất kỳ IP nào (**Non-overlapping**) → Đủ điều kiện kỹ thuật để thiết lập Peering.

8. **Tailscale Mesh VPN & Ephemeral Auth Key:**
   * Tailscale là mạng riêng ảo dựa trên giao thức WireGuard, thiết lập kết nối dạng lưới (Mesh) điểm-điểm giữa các máy.
   * **Ephemeral Auth Key:** Là khóa xác thực một lần tạm thời. Khi máy ảo tắt hoặc bị hủy, thiết bị sẽ tự động được thu hồi khỏi mạng mà không lưu vết. Trong bài lab, Ephemeral Key dùng để kết nối `vm-1` vào máy chủ dữ liệu nội bộ `/dataset-server` để kéo bộ dữ liệu lớn một cách an toàn và siêu tốc.

9. **Tối ưu hóa lưu trữ với Apache Parquet & Snappy Compression:**
   * **CSV thô:** Lưu trữ theo dòng (Row-oriented), tốn dung lượng đĩa, kiểu dữ liệu text không tối ưu, thời gian đọc I/O chậm khi nạp hàng triệu dòng.
   * **Apache Parquet:** Định dạng lưu trữ dạng cột (Columnar Storage) chuẩn công nghiệp Big Data. Hỗ trợ nén theo cột (Snappy), ghi nhớ metadata thống kê cho phép bỏ qua các khối dữ liệu không cần thiết (Data Skipping), giúp tăng tốc độ đọc từ 5 đến 20 lần và giảm dung lượng lưu trữ từ 70% đến 85%.

10. **Kỹ thuật truyền tải dữ liệu lớn hiệu năng cao: `aria2c` (HTTP Client-Pull) & `pv | pigz` (SSH Stream-Push):**
    * **`aria2c` (HTTP Client-Pull đa luồng):** Tải dữ liệu từ máy chủ web bằng cách mở nhiều kết nối TCP song song (`-x 8 -s 8 -k 1M`). Yêu cầu máy chủ HTTP hỗ trợ `Accept-Ranges: bytes`. Công cụ chia file lớn thành nhiều phân đoạn byte (byte ranges) để kéo đồng thời, loại bỏ nút thắt cổ chai TCP single-stream và tối đa hóa băng thông đường truyền.
    * **`pv | pigz` (SSH Stream-Push / Pipeline nén song song):** Truyền dữ liệu trực tiếp giữa 2 máy ảo qua kết nối SSH nội bộ VNet Peering:
      * **`pigz` (Parallel Gzip):** Tận dụng toàn bộ các nhân vCPU của máy ảo (`Standard_B2as_v2` / `Standard_B2ms` 2 vCPU) để nén/giải nén dữ liệu song song cực nhanh theo khối nhớ (mức nén `-1`), giảm kích thước dữ liệu luân chuyển trên đường truyền mạng.
      * **`pv` (Pipe Viewer):** Đóng vai trò đồng hồ đo lưu lượng thực tế kẹp giữa đường ống Unix pipeline, hiển thị trực quan dung lượng đã truyền, tốc độ truyền tức thời (MB/s), thời gian đã trôi qua và tiến độ (ETA) mà không cần cài đặt thêm phần mềm benchmark phức tạp.
    * **Data Processing Layer (Xử lý dữ liệu lớn):**
      * **`Polars` / `PyArrow`:** Thư viện tính toán song song đa luồng viết bằng ngôn ngữ Rust/C++, tối ưu hóa triệt để hiệu năng CPU/RAM của máy ảo Azure, nhanh gấp nhiều lần so với thư viện Pandas truyền thống khi xử lý bảng dữ liệu hàng triệu dòng.
      * **Apache Parquet:** Định dạng lưu trữ dữ liệu theo cột (columnar storage) kết hợp giải thuật nén Snappy. Giúp giảm dung lượng đĩa từ 5x - 10x so với file CSV thô và tăng tốc độ truy vấn phân tích dữ liệu lên gấp hàng chục lần.

---

### 3.2 Vấn Đề Kỹ Thuật và Giải Pháp (Environment Before & After)

#### A. Environment Before (Trước khi Peering):
* **Hạ tầng hiện tại:**
  * Đã tạo mạng `vnet-1` (`10.0.0.0/16`) tại vùng **East Asia** và `vnet-2` (`192.168.0.0/20`) tại vùng **Korea Central**.
  * Đã tạo máy ảo `vm-1` (SKU `Standard_B2as_v2` / `Standard_B2ms`, 8GB RAM) với IP tĩnh nội bộ `10.0.0.100`.
  * Đã tạo máy ảo `vm-2` (SKU `Standard_B2as_v2` / `Standard_B2s_v2`, 8GB RAM) và bật tính năng Boot Diagnostics (Serial Console).

#### Vấn đề kỹ thuật đặt ra (The Problem)
* Doanh nghiệp có hai phân hệ mạng độc lập: `vnet-1` tại `eastasia` và `vnet-2` tại `koreacentral`. Mặc định, Azure cô lập hoàn toàn giữa các mạng ảo: **Bộ định tuyến ảo (Virtual Router)** của mỗi VNet chỉ quản lý các subnet nội bộ và không hề có tuyến đường (route) nào dẫn sang VNet đối tác.
* Khi máy ảo ở `vnet-2` gửi gói tin tới địa chỉ IP nội bộ `10.0.0.100` của `vnet-1`, **bộ định tuyến ảo của `vnet-2`** tra bảng định tuyến hệ thống (System Route Table) không thấy đích đến nên sẽ tự động drop gói tin (trả về lỗi **Timeout**).
* Nếu bắt buộc phải đi qua Internet công cộng: sẽ phát sinh chi phí truyền tải ra ngoài (Egress Data Transfer), tăng độ trễ và đặc biệt nguy hiểm về bảo mật khi phải phơi bày cổng dữ liệu/quản trị ra ngoài mạng công cộng.

#### Kiến trúc môi trường (Environment)
![Sơ đồ kiến trúc Lab VNet Peering](images/vnet-peering-lab.png)

* **Trạng thái Before (Nửa trên sơ đồ):**
  * Đã có sẵn Mạng 1: `vnet-1` (`10.0.0.0/16`), chứa `subnet-1` (`10.0.0.0/24`).
  * Có Network Security Group (`nsg-1`) đặt luật Inbound: chỉ cho phép gói tin SSH (cổng 22) từ dải IP của Mạng 2 (`192.168.0.0/16`) đi vào.
  * Máy ảo đích `vm-1` có IP tĩnh `10.0.0.100` đang lắng nghe cổng SSH 22.
  * *Chưa tồn tại mạng `vnet-2` và chưa có bất kỳ liên kết Peering nào.*

#### Giải pháp kỹ thuật (The Solution) & Kiến trúc sau khi hoàn thành (Environment After)
* **Giải pháp:** Thiết lập **Azure Global VNet Peering** hai chiều giữa `vnet-1` và `vnet-2`.
* **Cơ chế hoạt động:**
  * Azure SDN tự động nạp bảng định tuyến tĩnh (System Routes) vào **bộ định tuyến ảo (Virtual Router)** của cả hai bên:
    * **Virtual Router của `vnet-2`** được nạp route: gói tin gửi tới dải `10.0.0.0/16` → đẩy thẳng qua liên kết Peering sang `vnet-1`.
    * **Virtual Router của `vnet-1`** được nạp route: muốn tới dải `192.168.0.0/20` → đẩy thẳng qua liên kết Peering sang `vnet-2`.
  * Toàn bộ gói tin truyền đi với tốc độ cao trên hạ tầng cáp quang riêng của Microsoft Backbone Network mà không cần NAT, không đi ra Internet.
* **Trạng thái After (Nửa dưới sơ đồ):**
  * Đã tạo mới Mạng 2: `vnet-2` (`192.168.0.0/20`), chứa `subnet-2` (`192.168.0.0/24`).
  * Đã tạo máy ảo `vm-2` (SKU `Standard_B2as_v2` / `Standard_B2s_v2`, 8GB RAM) và bật tính năng Boot Diagnostics (Serial Console).
  * Đã thiết lập liên kết Peering hoàn chỉnh (**Complete Peering Connection**). Máy `vm-2` gọi lệnh `nc -zv 10.0.0.100 22` hoặc SSH trực tiếp sang `vm-1` thành công ngay lập tức!
* **Khi nào giải pháp Peering không áp dụng được:**
  * Nếu hai VNet bị trùng dải IP (Overlapping IP CIDR), Azure sẽ từ chối tạo Peering.
  * *Giải pháp thay thế:* Bắt buộc phải triển khai **Azure VPN Gateway có cấu hình tính năng NAT** để biên dịch dải IP trùng sang một dải ảo khác, hoặc dùng **Azure Private Endpoint** kết nối từng dịch vụ đơn lẻ.

---

#### Mô hình bản chất mạng thực tế qua Cisco Packet Tracer:

Để bóc tách bản chất kỹ thuật đằng sau giao diện đám mây trừu tượng của Azure (vốn ẩn đi các thiết bị định tuyến vật lý), mô hình mạng này tương đương chính xác với sơ đồ thực tế được mô phỏng trong **Cisco Packet Tracer** với các thiết bị mạng cụ thể:

![Mô hình bản chất mạng Packet Tracer](images/packet-tracer-topology.png)

**Phân tích đối chiếu sơ đồ thiết bị:**
* **`Switch0` (ở trung tâm):** Đại diện cho toàn bộ hạ tầng chuyển mạch đường trục **Microsoft Backbone Network** kết nối xuyên qua hai vùng địa lý (East Asia và Korea Central).
* **Khung `vnet-1` (bên trái):** Đại diện cho ranh giới mạng `10.0.0.0/16` chứa `subnet-1 (10.0.0.0/24)`. Máy `PC-PT (10.0.0.100)` cắm vào **`Router0`** - thiết bị đóng vai trò là **Virtual Router / Default Gateway (`10.0.0.1`)** của Mạng 1.
* **Khung `vnet-2` (bên phải):** Đại diện cho ranh giới mạng `192.168.0.0/20` chứa `subnet-2 (192.168.0.0/24)`. Máy `PC-PT (192.168.0.100)` cắm vào **`Router1`** - thiết bị đóng vai trò là **Virtual Router / Default Gateway (`192.168.0.1`)** của Mạng 2.
* **Đường liên kết `Router0` ↔ `Switch0` ↔ `Router1`:** Đại diện cho kết nối **Global VNet Peering** ghép nối hai bộ định tuyến ảo xuyên qua đường trục cáp quang riêng của Microsoft.
  * *Trước khi Peering:* `Router0` và `Router1` chưa được nạp bảng định tuyến của nhau → Gói tin từ `192.168.0.100` gửi tới `10.0.0.100` bị `Router1` drop ngay tại cổng (Timeout).
  * *Sau khi Peering:* Tuyến đường được thiết lập qua `Switch0`, `Router1` chuyển tiếp trực tiếp gói tin sang `Router0` một cách liền mạch mà không cần NAT.

---

#### Sơ đồ luồng dữ liệu & thực nghiệm phân tán (Data Flow Architecture)

```
┌────────────────────────────────────────────────────────┐
│ MÁY CHỦ NỘI BỘ (Dataset Server trong Tailnet)          │
│ - Endpoint: http://<SERVER_IP>:8000/dataset.csv        │
│ - Tối ưu Nginx: Accept-Ranges: bytes, max_ranges 512   │
│ - Dung lượng file: > 1.2 GB (~10 triệu dòng bản ghi)   │
└──────────────────────────┬─────────────────────────────┘
                           │ (Tailscale VPN qua Ephemeral Key)
                           │ [Client-Pull: aria2c -x 8 -s 8]
                           ▼
┌────────────────────────────────────────────────────────┐
│ VÙNG 1: East Asia (Hong Kong)                          │
│ Mạng: vnet-1 (10.0.0.0/16) - Subnet: subnet-1          │
│                                                        │
│ [vm-1 (IP tĩnh 10.0.0.100)] ─────────────────┐         │
│ - Cấu hình: Standard_B2as_v2 (2 vCPU, 8 GB RAM)        │
│ - Tải dataset.csv (>1.2GB) siêu tốc bằng               │
│   aria2c (8 luồng song song, chunk 1MB)                │
│ - Cấp dữ liệu an toàn qua dịch vụ SSH (Port 22)        │
└──────────────────────────────────────────────┼─────────┘
                                               │ (Global VNet Peering qua Backbone)
                                               │ SSH Stream: time (ssh | gzip)
                                               │ Cổng 22 nội bộ (được NSG-1 cho phép)
┌──────────────────────────────────────────────┼─────────┐
│ VÙNG 2: Korea Central (Seoul)                │         │
│ Mạng: vnet-2 (192.168.0.0/20) - Subnet: subnet-2       │
│                                              │         │
│ [vm-2 (Private Subnet - 100% Offline)] ◄─────┘         │
│ - Cấu hình: Standard_B2as_v2 (2 vCPU, 8 GB RAM)        │
│ - Hoàn toàn không cần Internet / Public IP             │
│ - Kéo dataset.csv qua Private IP 10.0.0.100:           │
│     * SSH Stream nén luồng Gzip tích hợp sẵn           │
│     * Xác thực toàn vẹn bit bằng mã băm md5sum         │
│ - Pipeline ETL tự động (Python Standard Library):      │
│     1. Extract: Đo đạc tốc độ đọc CSV thô (>1.2GB)     │
│     2. Transform: Làm sạch, lọc null, GroupBy thống kê │
│     3. Load: Xuất & nén sang dataset_clean.csv.gz      │
│     4. Benchmark: Đo tốc độ đọc file nén & so sánh     │
└────────────────────────────────────────────────────────┘
```

---

## 4. Các Bước Thực Hiện Chi Tiết (Lab Steps)

### Bước 1: Logging in to the Microsoft Azure Portal & Creating Resource Group (Đăng Nhập & Tạo Resource Group)

1. Mở trình duyệt web và truy cập: [https://portal.azure.com/](https://portal.azure.com/).
2. Đăng nhập tài khoản sinh viên UIT hoặc tài khoản Azure được cấp.
3. **Tạo Resource Group quy chuẩn cho toàn bộ bài Lab:**
   * Tại ô tìm kiếm trên cùng của Azure Portal, gõ **Resource groups** và chọn dịch vụ **Resource groups**.
   * Nhấn nút **+ Create**.
   * **Tab Basics:**
     * **Subscription:** Chọn subscription học tập của bạn (ví dụ: *Azure for Students*).
     * **Resource group:** Nhập chính xác tên quy chuẩn: **`rg-asm`**.
     * **Region:** Chọn **`East Asia`** (hoặc khu vực mặc định của bạn).
   * Nhấn **Review + create** → sau đó nhấn **Create**.
   * Đợi 2 - 5 giây để Resource Group `rg-asm` được khởi tạo thành công.

---

#### Bước 2: Creating the First Network Resources (Tạo Tài Nguyên Mạng 1 - Environment Before)

Phần này sẽ dựng toàn bộ hạ tầng mạng và máy chủ của Mạng 1 (đóng vai trò là môi trường ban đầu trước khi Peering):

#### 1. Tạo Virtual Network 1 (`vnet-1`):
1. Tại ô tìm kiếm trên cùng, gõ **Virtual networks** → chọn dịch vụ **Virtual networks** → nhấn **+ Create**.
2. **Tab Basics:**
   * Subscription: Chọn subscription của bạn.
   * Resource Group: Chọn **`rg-asm`**.
   * Virtual network name: Nhập **`vnet-1`**.
   * Region: Chọn **`East Asia`**.
3. **Tab Security (Tùy chọn bảo mật giao diện Portal mới):**
   * Giữ mặc định tất cả các dịch vụ ở trạng thái **Disable** (Azure Bastion: Disable, DDoS Network Protection: Disable, Azure Firewall: Disable) để tránh phát sinh chi phí ngoài ý muốn.
   * Nhấn nút **Next: IP Addresses** (hoặc nhấp trực tiếp vào tab **IP addresses**).
4. **Tab IP addresses (hoặc Address space):**
   * Nếu có tùy chọn *Allocate using IP address pool*, hãy **bỏ tích chọn (Uncheck)** để tự nhập dải CIDR thủ công.
   * **IPv4 address space:** Nhập chính xác **`10.0.0.0/16`** (Starting address: `10.0.0.0`, Size: `/16 (65,536 addresses)`). Nếu có dải mặc định khác, xóa đi hoặc sửa lại cho đúng.
   * **Cấu hình Subnet:** Nhấn nút **Add a subnet** (hoặc nhấp chuột vào dòng subnet mẫu `default` nếu Portal tự tạo sẵn để chỉnh sửa lại):
      * **Name:** Nhập **`subnet-1`**.
      * **Starting address:** Nhập **`10.0.0.0`**.
      * **Size:** Chọn **`/24 (256 addresses)`** (Dải địa chỉ: `10.0.0.0 - 10.0.0.255`).
      * **Private subnet (no default outbound access):** **Không tích chọn (Uncheck)** *(Để máy ảo vẫn giữ quyền truy cập Internet qua outbound để tải package và cập nhật hệ điều hành)*.
      * **Security (NAT gateway, Network security group):** Để **None** (chúng ta sẽ cấu hình và gắn `nsg-1` riêng biệt ở Bước 2.2).
      * Nhấn nút **Add** (hoặc **Save** nếu đang chỉnh sửa subnet mẫu).
5. Nhấn **Review + create** → sau đó nhấn **Create**. Đợi 2 - 5 giây để VNet được tạo hoàn tất.

#### 2. Tạo Network Security Group (`nsg-1`):

> [!NOTE]
> **Tại sao cần tạo riêng NSG và luật này?**
> * **Bản chất VNet Peering:** Peering chỉ chịu trách nhiệm **Định tuyến (Routing Layer 3)** mở đường truyền giữa 2 mạng, chứ **không phải là tường lửa** và không tự động sinh ra bất kỳ luật bảo mật nào.
> * **Nguyên tắc bảo mật Zero Trust / Phân quyền:** Trong kịch bản thực tế, máy chủ của Mạng 1 (`vm-1`) không thể mở toang cho các mạng khác tự do truy cập. Quản trị viên sử dụng NSG làm tường lửa gác cổng: chỉ mở **duy nhất cổng 22 (SSH)** cho các máy thuộc dải IP của Mạng 2 (`192.168.0.0/16`) đi vào quản trị, còn các cổng hoặc dải mạng khác đều bị kiểm soát nghiêm ngặt.

1. Tại ô tìm kiếm trên cùng, gõ **Network security groups** → chọn dịch vụ **Network security groups** → nhấn **+ Create**.
2. **Tab Basics:**
   * Resource Group: Chọn **`rg-asm`**.
   * Name: Nhập **`nsg-1`**.
   * Region: Chọn **`East Asia`** (phải cùng Region với `vnet-1`).
3. Nhấn **Review + create** → nhấn **Create**.
4. **Cấu hình luật kiểm soát truy cập (Inbound Security Rules):**
   * Sau khi tạo xong, bấm **Go to resource** (hoặc mở lại `nsg-1`).
   * Tại menu bên trái (nhóm **Settings**), chọn **Inbound security rules** → nhấn **+ Add** để tạo 2 luật sau:

   * **Luật 1: Cho phép SSH nội bộ từ Mạng 2 (`vnet-2`)**
     * **Source:** Chọn **IP Addresses**.
     * **Source IP addresses/CIDR ranges:** Nhập **`192.168.0.0/16`** *(Khớp chính xác nhãn sơ đồ: chỉ cho phép dải IP của Mạng 2)*.
     * **Source port ranges:** Nhập **`*`**.
     * **Destination:** Chọn **Any**.
     * **Service:** Chọn **SSH** (hoặc để Custom và nhập port **`22`**).
     * **Destination port ranges:** Nhập **`22`**.
     * **Protocol:** Chọn **TCP**.
     * **Action:** Chọn **Allow**.
     * **Priority:** Nhập **`1010`**.
     * **Name:** Nhập **`AllowSSHFromVNet2`**.
     * Nhấn nút **Add**.

   * **Luật 2: Mở cổng WireGuard Tailscale (Chống nghẽn DERP Relay)**
     * Nhấn lại nút **+ Add**.
     * **Source:** Chọn **Any** *(WireGuard mã hóa end-to-end bằng public key, tự động drop gói tin không hợp lệ nên mở Any hoàn toàn an toàn và linh hoạt cho IP mạng gia đình)*.
     * **Source port ranges:** Nhập **`*`**.
     * **Destination:** Chọn **Any**.
     * **Service:** Chọn **Custom**.
     * **Destination port ranges:** Nhập **`41641`**.
     * **Protocol:** Chọn **UDP**.
     * **Action:** Chọn **Allow**.
     * **Priority:** Nhập **`1020`**.
     * **Name:** Nhập **`AllowTailscaleUDP`**.
     * Nhấn nút **Add**.

> [!NOTE]
> **Tại sao cần mở cổng 41641/UDP trên NSG cho Tailscale?**
> * **Kích hoạt Direct P2P (Bypass DERP Relay):** Mặc định Azure NSG chặn toàn bộ Inbound UDP khiến Tailscale không thể thực hiện kỹ thuật đục lỗ NAT (hole-punching) và buộc phải rơi vào máy chủ chuyển tiếp **DERP Relay (TCP 443)**, làm tốc độ tải bị bóp nghẹt xuống chỉ còn 1 - 2 MB/s. Việc mở cổng cố định 41641/UDP giúp máy chủ dataset ngoài Internet có thể bắt tay trực tiếp (Direct P2P WireGuard) với `vm-1`, giải phóng băng thông lên tới 50 - 100+ MB/s.
> * **Bảo mật tuyệt đối (Silent by Default):** Giao thức WireGuard được thiết kế theo cơ chế im lặng. Nếu gói tin UDP gửi đến không có khóa mật mã hợp lệ của mạng Tailscale, kernel sẽ lập tức drop gói tin mà không phản hồi. Các công cụ quét cổng (port scanner) từ Internet sẽ chỉ thấy cổng ở trạng thái filtered/stealth, không làm lộ bất kỳ bề mặt tấn công nào.

5. **Gắn NSG vào phân mạng `subnet-1`:**
   * Tại menu bên trái của `nsg-1`, chọn mục **Subnets** → nhấn **Associate**.
   * Virtual network: Chọn **`vnet-1`**.
   * Subnet: Chọn **`subnet-1`**.
   * Nhấn **OK**.

#### 3. Tạo Máy Ảo Target (`vm-1`) với IP tĩnh `10.0.0.100`:
1. Tìm kiếm **Virtual machines** → chọn **Create** → **Azure virtual machine**.
2. **Tab 1: Basics:**
   * **Project details:**
     * Subscription: Chọn subscription sinh viên của bạn (*Azure for Students*).
     * Resource group: Chọn **`rg-asm`**.
   * **Instance details:**
     * Virtual machine name: Nhập **`vm-1`**.
     * Region: Chọn **`(Asia Pacific) East Asia`** (Hong Kong).
     * Deploy to an Azure Extended Zone: *Không tích chọn*.
     * Availability options: Chọn **No infrastructure redundancy required**.
     * Security type: Chọn **Standard**.
     * Image: Chọn **`Ubuntu Server 24.04 LTS - x64 Gen2`** (hoặc `Ubuntu Server 22.04 LTS`).
     * VM architecture: **`x64`**.
     * Size: Nhấp **See all sizes** → Tìm và chọn **`Standard_B2as_v2`** (2 vCPU, 8 GiB RAM - Thế hệ B-series v2 chip AMD, tối ưu chi phí sinh viên ~$76/tháng, nhãn **Popular**) hoặc **`Standard_B2s_v2`** (Intel, 8 GiB RAM).
       > [!IMPORTANT]
       > Thế hệ mới B-series v2 có quy ước tên gọi thay đổi:
       > - `B2ats_v2` / `B2ts_v2` (chữ `t` = Tiny): chỉ có **1 GiB RAM** ❌
       > - `B2als_v2` / `B2ls_v2` (chữ `l` = Low): chỉ có **4 GiB RAM** ❌ 
       > - **`Standard_B2as_v2`** hoặc **`Standard_B2s_v2`**: chuẩn **8 GiB RAM** ✅ 
     * Run with Azure Spot discount: *Không tích chọn*.
   * **Administrator account:**
     * Authentication type: Chọn **Password** *(Bắt buộc để đăng nhập trực tiếp qua Azure Serial Console)*.
     * Username: Nhập **`azureuser`**.
     * Password / Confirm password: Nhập **`AzureLab@123456`**.
   * **Inbound port rules:**
     * Public inbound ports: Chọn **None** *(Toàn bộ kết nối được bảo vệ và quản lý tập trung bởi NSG ở cấp độ Subnet)*.
3. **Tab 2: Disks:**
   * **OS disk:**
     * OS disk size: Giữ mặc định (Default).
     * OS disk type: Chọn **Standard SSD (locally-redundant storage)** *(Tối ưu hóa chi phí cho tài khoản sinh viên mà vẫn đảm bảo độ ổn định cao)*.
     * Delete with VM: **Tích chọn (Checked)** *(Đảm bảo khi xóa máy ảo, ổ cứng OS disk sẽ tự động được xóa theo, tránh phát sinh chi phí ngầm)*.
     * Enable Ultra Disk compatibility: *Không tích chọn*.
   * **Data disks:** Để trống (không cần thêm disk phụ).
4. **Tab 3: Networking:**
   * **Virtual network:** Chọn **`vnet-1`**.
   * **Subnet:** Chọn **`subnet-1 (10.0.0.0/24)`**.
   * **Public IP:** Nhấp **Create new** → Nhập tên **`pip-vm-1`** (SKU: Standard, Allocation: Static) → Nhấn **OK**.  
     *(Giải thích kỹ thuật: Theo tài liệu kỹ thuật chính thức từ Tailscale, việc gán Public IP cho cloud VM là giải pháp tốt nhất để biến mô hình "Cloud NAT" thành "No NAT", triệt tiêu hoàn toàn cơ chế Symmetric NAT gây đổi cổng UDP ngẫu nhiên của Azure, đảm bảo 100% đạt kết nối Direct P2P).*
   * **NIC network security group:** Chọn **None** *(Do đã gắn `nsg-1` trực tiếp ở cấp độ Subnet `subnet-1`, chọn None ở đây để tránh trùng lặp rule)*.
   * **Public inbound ports:** Chọn **None**.
   * **Delete NIC when VM is deleted:** **Tích chọn (Checked)**.
   * **Enable accelerated networking:** Tích chọn (nếu có sẵn).
   * **Load balancing options:** Chọn **None**.
5. **Tab 4: Management:**
   * **Identity & Microsoft Entra ID:** Giữ mặc định (Tắt).
   * **Auto-shutdown:**
     * Tích chọn **Enable auto-shutdown**.
     * Shutdown time: Nhập **`23:00:00`**.
     * Time zone: Chọn **`(UTC+07:00) Bangkok, Hanoi, Jakarta`**.
     * Notification before shutdown: Bỏ chọn (hoặc nhập email nếu muốn).  
     *(Biện pháp phòng ngừa: Tự động tắt máy ban đêm nếu quên dừng, bảo toàn $100 credit sinh viên).*
   * **Guest OS updates & Hibernation:** Giữ mặc định.
6. **Tab 5: Monitoring:**
   * **Alerts:** Bỏ chọn / Không bật.
   * **Diagnostics:**
     * **Boot diagnostics:** Tích chọn **Enable with managed storage account (recommended)** *(CỰC KỲ QUAN TRỌNG: Bắt buộc phải bật tùy chọn này để Azure kích hoạt cổng giao tiếp phần cứng ảo Azure Serial Console ở Bước 4)*.
     * Enable OS guest diagnostics: *Không tích chọn*.
   * **Health:** Enable application health monitoring: *Không tích chọn*.
7. **Tab 6: Advanced:**
   * Giữ toàn bộ cài đặt mặc định (không thêm Extensions, Custom data, hay SQL Server).
8. **Tab Review + create:**
   * Nhấn **Review + create** → kiểm tra thông báo **Validation passed** → nhấn nút **Create**. Đợi 1 - 2 phút để máy ảo triển khai xong.
9. **Cấu hình gán địa chỉ IP tĩnh nội bộ `10.0.0.100` cho máy ảo:**
   * Vào menu **Virtual Machines** → chọn **`vm-1`**.
   * Ở menu bên trái, chọn **Networking**.
   * Nhấp vào tên Card mạng (Network Interface) của máy ảo (ví dụ: `vm-1-nic` hoặc dạng `vm-1xxx`).
   * Trong giao diện của Card mạng, tại menu bên trái chọn **IP configurations**.
   * Nhấp chuột vào dòng cấu hình **`ipconfig1`**.
   * Tại mục **Private IP assignment (Allocation)**: Chuyển từ `Dynamic` sang **`Static`**.
   * Tại ô **IP address**: Nhập chính xác **`10.0.0.100`**.
   * Nhấn nút **Save** ở trên cùng. Đợi vài giây để Azure lưu cấu hình IP tĩnh.

> [!TIP]
> **Tùy chọn: Dùng Azure Cloud Shell / Azure CLI để tạo nhanh toàn bộ Bước 2 trong 1 phút:**
> Nếu bạn muốn tiết kiệm thời gian bấm chuột trên Portal, bạn có thể mở Azure Cloud Shell (biểu tượng `>_` trên thanh công cụ Portal) và dán chuỗi lệnh sau:
> ```bash
> # 1. Tạo VNet 1 và Subnet 1
> az network vnet create -g rg-asm -n vnet-1 -l eastasia --address-prefixes 10.0.0.0/16 --subnet-name subnet-1 --subnet-prefixes 10.0.0.0/24
> 
> # 2. Tạo NSG 1 và thêm luật cho phép SSH nội bộ cùng cổng WireGuard Tailscale
> az network nsg create -g rg-asm -n nsg-1 -l eastasia
> az network nsg rule create -g rg-asm --nsg-name nsg-1 -n AllowSSHFromVNet2 --priority 1010 --source-address-prefixes 192.168.0.0/16 --destination-port-ranges 22 --protocol Tcp --access Allow
> az network nsg rule create -g rg-asm --nsg-name nsg-1 -n AllowTailscaleUDP --priority 1020 --source-address-prefixes '*' --destination-port-ranges 41641 --protocol Udp --access Allow
> az network vnet subnet update -g rg-asm --vnet-name vnet-1 -n subnet-1 --network-security-group nsg-1
> 
> # 3. Tạo Public IP và máy ảo vm-1 với IP tĩnh nội bộ 10.0.0.100 (Standard_B2as_v2 8GB RAM)
> az network public-ip create -g rg-asm -n pip-vm-1 -l eastasia --sku Standard --allocation-method Static
> az vm create -g rg-asm -n vm-1 -l eastasia --image Ubuntu2204 --size Standard_B2as_v2 --admin-username azureuser --admin-password "AzureLab@123456" --vnet-name vnet-1 --subnet subnet-1 --private-ip-address 10.0.0.100 --public-ip-address pip-vm-1 --boot-diagnostics-storage ""
> ```

---

### Bước 3: Creating the Second Network Resources (Tạo Tài Nguyên Mạng 2 - Environment After)

#### 1. Tạo Virtual Network 2 (`vnet-2`):
1. Trên Azure Portal, tìm kiếm dịch vụ **Virtual networks** → chọn **Create**.
2. **Tab Basics:**
   * Subscription: Chọn subscription sinh viên của bạn.
   * Resource Group: Chọn **`rg-asm`**.
   * Name: Nhập **`vnet-2`**.
   * Region: Chọn **`Korea Central`**.
3. **Tab Security:**
   * Giữ mặc định tất cả các dịch vụ ở trạng thái **Disable** (Bastion, DDoS, Firewall) → nhấn **Next: IP Addresses**.
4. **Tab IP addresses (hoặc Address space):**
   * Bỏ tích chọn *Allocate using IP address pool* (nếu có).
   * **IPv4 address space:** Nhập chính xác **`192.168.0.0/20`** (Starting address: `192.168.0.0`, Size: `/20`).
   * **Cấu hình Subnet:** Nhấn **Add a subnet** (hoặc sửa subnet mẫu `default`):
     * **Name:** Nhập **`subnet-2`**.
     * **Starting address:** `192.168.0.0`.
     * **Size:** Chọn **`/24 (256 addresses)`** (Dải: `192.168.0.0 - 192.168.0.255`).
     * **Private subnet (no default outbound access):** **Không tích chọn (Uncheck)**.
     * **Security (NAT gateway, NSG):** Để **None**.
     * Nhấn **Add** (hoặc **Save**).
5. Nhấn **Review + create** → sau đó nhấn **Create**.

#### 2. Tạo Máy Ảo Phân Tích (`vm-2`):
1. Tìm kiếm dịch vụ **Virtual machines** → chọn **Create** → **Azure virtual machine**.
2. **Tab 1: Basics:**
   * **Project details:**
     * Subscription: Chọn subscription sinh viên của bạn (*Azure for Students*).
     * Resource group: Chọn **`rg-asm`**.
   * **Instance details:**
     * Virtual machine name: Nhập **`vm-2`**.
     * Region: Chọn **`(Asia Pacific) Korea Central`** (Seoul).
     * Deploy to an Azure Extended Zone: *Không tích chọn*.
     * Availability options: Chọn **No infrastructure redundancy required**.
     * Security type: Chọn **Standard**.
     * Image: Chọn **`Ubuntu Server 24.04 LTS - x64 Gen2`** (hoặc `Ubuntu Server 22.04 LTS`).
     * VM architecture: **`x64`**.
     * Size: Nhấp **See all sizes** → Tìm và chọn **`Standard_B2as_v2`** (2 vCPU, 8 GiB RAM).
       > [!TIP]
       > **Xử lý hạn ngạch (Quota) tại vùng Korea Central:** Nếu vùng `Korea Central` thông báo hết quota cho dòng AMD `B2as_v2`, bạn linh hoạt chọn một trong các phương án dự phòng sau (tất cả đều có **8 GiB RAM** đáp ứng 100% Tiêu chí 1 Rubric):
       > 1. **`Standard_B2s_v2`** (2 vCPU, 8 GiB RAM - Dòng Intel B-series v2).
       > 2. **`Standard_D2s_v3`** hoặc **`Standard_D2as_v4`** (2 vCPU, 8 GiB RAM - Dòng General Purpose cực kỳ dồi dào tài nguyên ở Seoul).
       > 3. **`Standard_B2ms`** (2 vCPU, 8 GiB RAM - Dòng B-series v1 nếu vùng còn quota).
     * Run with Azure Spot discount: *Không tích chọn*.
   * **Administrator account:**
     * Authentication type: Chọn **Password** *(Bắt buộc để đăng nhập trực tiếp qua Azure Serial Console)*.
     * Username: Nhập **`azureuser`**.
     * Password / Confirm password: Nhập **`AzureLab@123456`**.
   * **Inbound port rules:**
     * Public inbound ports: Chọn **None**.
3. **Tab 2: Disks:**
   * **OS disk:**
     * OS disk size: Giữ mặc định.
     * OS disk type: Chọn **Standard SSD (locally-redundant storage)** *(Tiết kiệm credit sinh viên)*.
     * Delete with VM: **Tích chọn (Checked)** *(Xóa disk khi xóa VM)*.
     * Enable Ultra Disk compatibility: *Không tích chọn*.
   * **Data disks:** Để trống.
4. **Tab 3: Networking:**
   * **Virtual network:** Chọn **`vnet-2`**.
   * **Subnet:** Chọn **`subnet-2 (192.168.0.0/24)`**.
   * **Public IP:** Chọn **None** *(Máy ảo vận hành an toàn 100% trong mạng riêng nội bộ, không phơi bày ra Internet)*.
   * **NIC network security group:** Chọn **None**.
   * **Public inbound ports:** Chọn **None**.
   * **Delete NIC when VM is deleted:** **Tích chọn (Checked)**.
   * **Enable accelerated networking:** Tích chọn (nếu có sẵn).
   * **Load balancing options:** Chọn **None**.
5. **Tab 4: Management:**
   * **Identity & Microsoft Entra ID:** Giữ mặc định (Tắt).
   * **Auto-shutdown:**
     * Tích chọn **Enable auto-shutdown**.
     * Shutdown time: Nhập **`23:00:00`**.
     * Time zone: Chọn **`(UTC+07:00) Bangkok, Hanoi, Jakarta`**.
     * Notification before shutdown: Bỏ chọn.
   * **Guest OS updates & Hibernation:** Giữ mặc định.
6. **Tab 5: Monitoring:**
   * **Alerts:** Bỏ chọn / Không bật.
   * **Diagnostics:**
     * **Boot diagnostics:** Tích chọn **Enable with managed storage account (recommended)** *(BẮT BUỘC: Để mở được Azure Serial Console ở Bước 4)*.
     * Enable OS guest diagnostics: *Không tích chọn*.
   * **Health:** Enable application health monitoring: *Không tích chọn*.
7. **Tab 6: Advanced:**
   * Giữ toàn bộ cài đặt mặc định (không thêm extension hay custom data).
8. **Tab Review + create:**
   * Nhấn **Review + create** → kiểm tra thông báo **Validation passed** → nhấn nút **Create**.
   * Đợi 1 - 2 phút cho đến khi quá trình triển khai hoàn tất.

---

### Bước 4: Attempting to Make a Connection Using Serial Console (Thử Kết Nối từ vm-2 sang vm-1 - Trước Khi Peering)

Mục tiêu bước này là chứng minh: **Khi chưa có VNet Peering, hai mạng ảo bị cô lập hoàn toàn dù đều nằm trên Azure.**

1. Mở Serial Console bằng một trong hai cách:
   * **Cách A (Trên Terminal thông qua Azure CLI):**
     ```bash
     az serial-console connect -n vm-2 -g rg-asm
     ```
   * **Cách B (Trực tiếp trên Azure Portal):**
     Vào **Virtual Machines** → chọn **`vm-2`** → menu bên trái (nhóm **Help**), chọn **Serial console**.
2. Nhấn phím **Enter** một lần để xuất hiện dòng nhắc đăng nhập `vm-2 login:`.
3. Đăng nhập với thông tin tài khoản:
   * **Login:** `azureuser`
   * **Password:** `AzureLab@123456`
4. Thực hiện lệnh kiểm tra kết nối mở cổng 22 tới máy `vm-1` (`10.0.0.100`):
   ```bash
   nc -zv 10.0.0.100 22
   ```
5. **Hiện tượng quan sát:**
   * Lệnh đứng yên chờ đợi và sau khoảng 15-30 giây trả về kết quả lỗi **Timeout**.
   * *Giải thích bản chất:* Router ảo của `vnet-2` hoàn toàn chưa có thông tin định tuyến tới dải `10.0.0.0/16`, do đó gói tin SYN bị drop ngay tại Gateway.

---

### Bước 5: Initiating the Virtual Network Peering Connection (Thiết Lập Kết Nối VNet Peering)

Sau khi chứng minh hai máy ảo hoàn toàn bị cô lập mạng ở Bước 4, ta tiến hành thiết lập **VNet Peering** hai chiều đồng thời từ giao diện quản trị của `vnet-2`:

1. Trên thanh tìm kiếm Azure Portal, gõ **Virtual networks** → chọn dịch vụ **Virtual networks** → nhấp chọn **`vnet-2`**.
2. Tại menu bên trái (nhóm **Settings**), chọn mục **Peerings** → nhấn nút **+ Add** trên thanh công cụ.
3. Trong biểu mẫu **Add peering**, cấu hình chính xác từng trường theo 2 nhóm sau:

   #### A. Nhóm Remote virtual network summary (Mạng từ xa - `vnet-1`):
   * **Peering link name:** Nhập **`peer-vnet-1-to-vnet-2`** *(Tên định danh cho chiều kết nối từ `vnet-1` trỏ về `vnet-2`)*.
   * **Peering type:** Chọn **`Virtual network`** *(Kết nối toàn bộ không gian địa chỉ VNet, không chọn Subnet peering)*.
   * **I know my resource ID:** *Không tích chọn (Unchecked)*.
   * **Subscription:** Chọn subscription của bạn (ví dụ: *Azure for Students*).
   * **Virtual network:** Chọn **`vnet-1`** *(Mạng ảo đặt tại vùng East Asia)*.
   * **Enable IPv6 only peering:** *Không tích chọn (Unchecked)*.
   * **Remote virtual network peering settings:**
     * **Allow the peered virtual network to access 'vnet-2':** **Tích chọn (Checked)** *(BẮT BUỘC: Cho phép các gói tin từ `vnet-1` đi vào `vnet-2`)*.
     * **Allow the peered virtual network to receive forwarded traffic from 'vnet-2':** **Tích chọn (Checked)** *(Cho phép `vnet-1` nhận lưu lượng mạng được chuyển tiếp từ `vnet-2`)*.
     * **Allow gateway or route server in the peered virtual network to forward traffic to 'vnet-2':** *Không tích chọn (Unchecked)* *(Lab này không sử dụng Virtual Network Gateway / Route Server)*.
     * **Enable the peered virtual network to use 'vnet-2's' remote gateway or route server:** *Không tích chọn (Unchecked)*.

   #### B. Nhóm Local virtual network summary (Mạng cục bộ - `vnet-2`):
   * **Peering link name:** Nhập **`peer-vnet-2-to-vnet-1`** *(Tên định danh cho chiều kết nối từ `vnet-2` trỏ sang `vnet-1`)*.
   * **Local virtual network peering settings:**
     * **Allow 'vnet-2' to access the peered virtual network:** **Tích chọn (Checked)** *(BẮT BUỘC: Cho phép tài nguyên trong `vnet-2` gửi gói tin sang `vnet-1`)*.
     * **Allow 'vnet-2' to receive forwarded traffic from the peered virtual network:** **Tích chọn (Checked)** *(Cho phép `vnet-2` nhận lưu lượng mạng được chuyển tiếp từ `vnet-1`)*.
     * **Allow gateway or route server in 'vnet-2' to forward traffic to the peered virtual network:** *Không tích chọn (Unchecked)*.
     * **Enable 'vnet-2' to use the peered virtual networks' remote gateway or route server:** *Không tích chọn (Unchecked)*.

4. Nhấn nút **Add** ở góc dưới cùng để Azure tạo đồng thời cả 2 chiều liên kết.
5. Đợi 10 - 20 giây rồi nhấn nút **Refresh** trên danh sách Peerings. Xác nhận cột **Peering status** của liên kết chuyển sang trạng thái **`Connected`** màu xanh lá.

> [!TIP]
> **Tùy chọn: Thiết lập Peering siêu tốc bằng Azure Cloud Shell / Azure CLI:**
> ```bash
> # Chiều 1: vnet-2 -> vnet-1
> az network vnet peering create -g rg-asm -n peer-vnet-2-to-vnet-1 --vnet-name vnet-2 --remote-vnet vnet-1 --allow-vnet-access --allow-forwarded-traffic
>
> # Chiều 2: vnet-1 -> vnet-2
> az network vnet peering create -g rg-asm -n peer-vnet-1-to-vnet-2 --vnet-name vnet-1 --remote-vnet vnet-2 --allow-vnet-access --allow-forwarded-traffic
> ```

---

### Bước 6: Testing Peering & Big Data Experiment (Kiểm Thử Peering & Thực Nghiệm Dữ Liệu Lớn)

#### 6.1 Kiểm tra thông mạng VNet Peering thành công
1. Quay lại cửa sổ **Serial Console** của máy ảo `vm-2`.
2. Chạy lại lệnh kiểm tra kết nối cổng 22:
   ```bash
   nc -zv 10.0.0.100 22
   ```
3. **Kết quả thu được:**
   ```text
   Connection to 10.0.0.100 22 port [tcp/ssh] succeeded!
   ```
4. Đăng nhập SSH trực tiếp từ máy `vm-2` sang máy `vm-1` bằng IP nội bộ:
   ```bash
   ssh azureuser@10.0.0.100
   ```
   *Nhập mật khẩu `AzureLab@123456`, bạn sẽ chuyển sang prompt `azureuser@vm-1:~$`.*

---

#### 6.2 Chuẩn bị bộ dữ liệu lớn (>1.2GB) trên máy VM-1 (`10.0.0.100`)

*(Thực hiện các lệnh đơn lẻ sau tại prompt `azureuser@vm-1:~$`)*

* **Thao tác 1: Cài đặt Tailscale client trên VM-1**
  ```bash
  curl -fsSL https://tailscale.com/install.sh | sh
  ```

* **Thao tác 2: Thêm VM-1 vào mạng Tailnet bằng Ephemeral Auth Key (Cố định cổng 41641/UDP)**
  Thay thế `<YOUR_EPHEMERAL_KEY>` bằng key tạm thời của bạn:
  ```bash
  sudo tailscale up --authkey="<YOUR_EPHEMERAL_KEY>" --hostname="vm-1" --port=41641 --accept-routes
  ```
  Kiểm tra trạng thái kết nối thành công:
  ```bash
  tailscale status
  ```

* **Thao tác 3: Xác minh kết nối Direct P2P (Bảo đảm không bị nghẽn DERP Relay)**
  Chạy lệnh ping tích hợp của Tailscale tới máy chủ dataset:
  ```bash
  tailscale ping <SERVER_IP>
  ```
  *Quan sát kết quả trên terminal thực tế:*
  ```text
  azureuser@vm-1:~$ tailscale ping <SERVER_IP>
  pong from dataset-server (<SERVER_IP>) via DERP(sin) in 155ms
  pong from dataset-server (<SERVER_IP>) via DERP(sin) in 76ms
  pong from dataset-server (<SERVER_IP>) via DERP(sin) in 76ms
  pong from dataset-server (<SERVER_IP>) via <PUBLIC_IP>:56538 in 42ms
  azureuser@vm-1:~$ tailscale ping <SERVER_IP>
  pong from dataset-server (<SERVER_IP>) via <PUBLIC_IP>:56538 in 43ms
  azureuser@vm-1:~$ tailscale ping <SERVER_IP>
  pong from dataset-server (<SERVER_IP>) via <PUBLIC_IP>:56538 in 42ms
  ```

  > [!IMPORTANT]
  > **Dấu hiệu kỹ thuật quan trọng:**
  > * Dòng chữ **`via <PUBLIC_IP>:56538 in 42ms`** xác nhận Tailscale đã đục lỗ NAT (hole-punching) thành công từ DERP sang **Direct P2P WireGuard**, độ trễ giảm từ 155ms xuống chỉ còn **42ms**.
  > * Tốc độ kéo dữ liệu lúc này sẽ đạt mức tối đa không bị bóp nghẽn bởi DERP Relay.

* **Thao tác 4: Cài đặt các công cụ tối ưu truyền tải đa luồng và nén dữ liệu**
  ```bash
  sudo apt-get update -y && sudo apt-get install -y aria2 pv pigz
  ```

* **Thao tác 5: Kéo bộ dữ liệu từ máy chủ nội bộ Tailscale (`/dataset-server`) bằng `aria2c` đa luồng**
  ```bash
  aria2c -x 8 -s 8 -k 1M http://<SERVER_IP>:8000/dataset.csv
  ```
  *Kết quả chạy thực tế trên terminal:*
  ```text
  09/21 17:50:48 [NOTICE] Downloading 1 item(s)
  [#07bca8 1.2GiB/1.2GiB(99%) CN:1 DL:33MiB]                                     
  09/21 17:51:32 [NOTICE] Download complete: /home/azureuser/dataset.csv

  Download Results:
  gid   |stat|avg speed  |path/URI
  ======+====+===========+=======================================================
  07bca8|OK  |    33MiB/s|/home/azureuser/dataset.csv

  Status Legend:
  (OK):download completed.
  ```

* **Thao tác 6: Xác nhận kích thước file tải về**
  ```bash
  ls -lh dataset.csv
  ```
  *Kết quả thực tế:*
  ```text
  -rw-rw-r-- 1 azureuser azureuser 1.3G Sep 21 17:51 dataset.csv
  ```
  *(Kích thước chính xác: `1,303,422,535 bytes` ~ `1,243.04 MB` ~ `1.3 GB`).*

* **Thao tác 7: Chuẩn bị cơ chế truyền tải nội bộ liên vùng**
  * **Cơ chế 1 (Khuyên dùng - SSH Stream-Push với `pv | pigz`):** Không cần cài thêm web server hay mở cổng nào khác; file `dataset.csv` và công cụ `pigz` đã sẵn sàng để máy `vm-2` kéo qua kênh SSH an toàn.
  * **Cơ chế 2 (HTTP Client-Pull đa luồng với `aria2c`):** Nếu muốn kiểm thử so sánh hiệu năng, bạn có thể khởi chạy thêm HTTP Server nội bộ trên cổng 8080:
    ```bash
    python3 -m http.server 8080 &
    ```

* **Thao tác 8: Thoát phiên SSH của VM-1, quay lại VM-2**
  ```bash
  exit
  ```
  *Prompt terminal bây giờ là: `azureuser@vm-2:~$`.*

---

#### 6.3 Thực nghiệm truyền tải liên vùng & Xử lý dữ liệu trên VM-2 (Môi trường Ngoại Tuyến 100% Offline)

*(Thực hiện các lệnh đơn lẻ sau tại prompt `azureuser@vm-2:~$`)*

> [!NOTE]
> **Đặc tính kiến trúc an toàn thông tin của `vm-2`:**  
> Máy ảo `vm-2` nằm trong phân mạng hoàn toàn riêng tư (Private Subnet) không có Public IP nhằm mô phỏng hệ thống phân tích dữ liệu nội bộ bảo mật cao (Zero Internet Access).  
> Do đó, toàn bộ các bước kiểm thử độ trễ, truyền tải dữ liệu và xử lý Big Data đều được thiết kế **100% sử dụng các công cụ và thư viện tích hợp sẵn trong nhân hệ điều hành Ubuntu Server** (`ssh`, `gzip`, `time`, `md5sum`, Python Standard Library).

* **Thao tác 1: Xác nhận môi trường công cụ có sẵn trên hệ điều hành**
  Kiểm tra các công cụ mặc định đã được tích hợp sẵn trong nhân Ubuntu:
  ```bash
  python3 -V
  gzip -V | head -n 1
  ssh -V
  ```
  *Xác nhận Python 3, Gzip và OpenSSH client đều đã sẵn sàng hoạt động ngay lập tức.*

* **Thao tác 2: Kiểm tra độ trễ mạng liên vùng (Latency/RTT) qua VNet Peering**
  ```bash
  ping -c 5 10.0.0.100
  ```
  *Kết quả quan sát thực tế trên terminal:*
  ```text
  azureuser@vm-2:~$ ping -c 5 10.0.0.100
  PING 10.0.0.100 (10.0.0.100) 56(84) bytes of data.
  64 bytes from 10.0.0.100: icmp_seq=1 ttl=64 time=35.7 ms
  64 bytes from 10.0.0.100: icmp_seq=2 ttl=64 time=35.3 ms
  64 bytes from 10.0.0.100: icmp_seq=3 ttl=64 time=35.3 ms
  64 bytes from 10.0.0.100: icmp_seq=4 ttl=64 time=35.3 ms
  64 bytes from 10.0.0.100: icmp_seq=5 ttl=64 time=35.2 ms

  --- 10.0.0.100 ping statistics ---
  5 packets transmitted, 5 received, 0% packet loss, time 4006ms
  rtt min/avg/max/mdev = 35.240/35.355/35.717/0.181 ms
  ```
  *(Độ trễ trung bình cực kỳ lý tưởng: **`35.355 ms`**, tỷ lệ mất gói **0%** chứng minh đường truyền Microsoft Backbone hoàn toàn thông suốt).*

* **Thao tác 3: Truyền tải bộ dữ liệu qua VNet Peering bằng SSH Stream**
  Tận dụng cổng SSH 22 nội bộ duy nhất đã được cấu hình cho phép trên NSG `nsg-1` và thông suốt qua VNet Peering. Ta sử dụng cơ chế **SSH Stream kết hợp nén luồng Gzip tích hợp** (máy nguồn `vm-1` nén dòng dữ liệu gửi qua kênh SSH, máy đích `vm-2` giải nén trực tiếp vào file đĩa) và đo đạc thời gian bằng lệnh `time`:
  ```bash
  time (ssh azureuser@10.0.0.100 "gzip -c dataset.csv" | gzip -d > dataset.csv)
  ```
  *Kết quả thực thi thực tế trên terminal:*
  ```text
  azureuser@10.0.0.100's password: 

  real    0m24.591s
  user    0m4.144s
  sys     0m0.458s
  ```
  *Ghi nhận thời gian thực tế **`24.591 s`** để tính toán thông lượng băng thông.*

* **Thao tác 4: Xác thực dung lượng và tính toàn vẹn dữ liệu nội bộ (Data Integrity Check)**
  Kiểm tra kích thước file và tính toán mã băm kiểm tra:
  ```bash
  ls -lh dataset.csv
  md5sum dataset.csv
  ```
  *Kết quả thực tế hiển thị file: `1,303,422,535 bytes` (~ `1,243.04 MB` ~ `1.3G`).*  
  $$\text{Throughput (MB/s)} = \frac{\text{Dung lượng file (MB)}}{\text{Thời gian truyền tải (s)}} = \frac{1,243.04 \text{ MB}}{24.591 \text{ s}} \approx \mathbf{50.55 \text{ MB/s}}$$
  > [!TIP]
  > **Ý nghĩa minh chứng kỹ thuật:** Băng thông truyền tải đạt **~50.55 MB/s** xuyên qua hai vùng địa lý (East Asia ↔ Korea Central) qua đường truyền mã hóa SSH nội bộ. Mã băm MD5 trùng khớp 100% giữa hai máy ảo chứng minh tính toàn vẹn bit tuyệt đối, không xảy ra hiện tượng thất thoát gói tin trên payload lớn (>1.2GB).

* **Thao tác 5: Chuẩn bị script ETL & Benchmark nén dữ liệu bằng Python Standard Library**
  
  Kéo trực tiếp file script `etl_benchmark.py` từ máy chủ (dataset-server) về `vm-2` thông qua cầu nối SSH của `vm-1` chỉ với **1 câu lệnh duy nhất**:
  ```bash
  ssh azureuser@10.0.0.100 "curl -s http://<SERVER_IP>:8000/etl_benchmark.py" > etl_benchmark.py
  ```
  *(Nhập mật khẩu `AzureLab@123456`. Thay `<SERVER_IP>` bằng IP máy chủ Tailscale của bạn. Lệnh này kích hoạt `vm-1` lấy script từ máy chủ qua Tailnet và truyền thẳng nội dung về `vm-2` qua VNet Peering trong tích tắc).*

  > [!TIP]
  > **Tùy chọn: Tự dán thủ công nếu không dùng server iMac:**
  > Nếu muốn tự tạo file thủ công hoặc kiểm tra nội dung mã nguồn, bạn có thể xem file [`demo/etl_benchmark.py`](etl_benchmark.py) hoặc chạy khối lệnh sau:
  
  ```bash
  cat << 'EOF' > etl_benchmark.py
  import os, sys, time, csv, gzip

  input_csv = sys.argv[1] if len(sys.argv) > 1 else "dataset.csv"
  out_gz = sys.argv[2] if len(sys.argv) > 2 else "dataset_clean.csv.gz"

  csv_size = os.path.getsize(input_csv)
  print(f"[1] Kích thước file CSV gốc: {csv_size / (1024*1024):.2f} MB ({csv_size:,} bytes)")

  # 1. EXTRACT & TRANSFORM & LOAD (Pipeline Streaming to Compressed Gzip)
  t0 = time.perf_counter()
  valid_rows = 0
  total_rows = 0
  summary = {}  # key: (year, topic) -> [sum_val, min_val, max_val, count]

  with open(input_csv, "r", encoding="utf-8", errors="replace") as fin, \
       gzip.open(out_gz, "wt", encoding="utf-8", compresslevel=6) as fout:
      
      reader = csv.reader(fin)
      writer = csv.writer(fout)
      
      try:
          header = next(reader)
          writer.writerow(header)
          year_idx = header.index("Year") if "Year" in header else 0
          topic_idx = header.index("Topic") if "Topic" in header else 1
          val_idx = header.index("Data_Value") if "Data_Value" in header else -1
      except StopIteration:
          header = []
          val_idx = -1

      for row in reader:
          total_rows += 1
          if not row or len(row) <= max(year_idx, topic_idx, val_idx):
              continue
          
          val_str = row[val_idx].strip() if val_idx >= 0 else ""
          if not val_str or val_str in ("NA", "Value suppressed", "~", ""):
              continue
          
          try:
              val = float(val_str)
          except ValueError:
              continue

          valid_rows += 1
          writer.writerow(row)

          # Aggregation
          y = row[year_idx]
          t = row[topic_idx]
          k = (y, t)
          if k not in summary:
              summary[k] = [val, val, val, 1]
          else:
              s = summary[k]
              s[0] += val
              if val < s[1]: s[1] = val
              if val > s[2]: s[2] = val
              s[3] += 1

  t_process = time.perf_counter() - t0
  read_speed = (csv_size / (1024*1024)) / max(t_process, 0.001)
  gz_size = os.path.getsize(out_gz)
  write_speed = (gz_size / (1024*1024)) / max(t_process, 0.001)

  print(f"[2] EXTRACT & TRANSFORM: Đọc, làm sạch {total_rows:,} dòng mất: {t_process:.3f} s (Tốc độ: {read_speed:.2f} MB/s)")
  print(f"[3] LOAD (Gzip Compressed): Ghi nén thành công, giữ lại {valid_rows:,} dòng hợp lệ")
  print(f"    Kích thước file sau nén: {gz_size / (1024*1024):.2f} MB ({gz_size:,} bytes)")

  # 2. BENCHMARK READ (Đo tốc độ đọc & giải nén file GZ)
  t0 = time.perf_counter()
  read_gz_lines = 0
  with gzip.open(out_gz, "rt", encoding="utf-8") as f_gz:
      for _ in f_gz:
          read_gz_lines += 1
  t_read_gz = time.perf_counter() - t0
  gz_read_speed = (csv_size / (1024*1024)) / max(t_read_gz, 0.001)
  print(f"[4] BENCHMARK GZ READ: Đọc lại toàn bộ file nén {read_gz_lines:,} dòng mất: {t_read_gz:.3f} s (Tốc độ tương đương: {gz_read_speed:.2f} MB/s)")

  # 3. TỔNG HỢP CHỈ SỐ
  saved_pct = ((csv_size - gz_size) / csv_size) * 100
  ratio = csv_size / max(gz_size, 1)
  speedup = t_process / max(t_read_gz, 0.0001)
  print("\n" + "="*50)
  print(f"-> Tỷ lệ nén dữ liệu:           {ratio:.2f}x (Tiết kiệm {saved_pct:.2f}% dung lượng lưu trữ)")
  print(f"-> Tăng tốc độ đọc (Speedup):   {speedup:.2f} lần so với xử lý file gốc")
  print("="*50)
  EOF
  ```

* **Thao tác 6: Khởi chạy pipeline ETL và Benchmark**
  ```bash
  python3 etl_benchmark.py dataset.csv dataset_clean.csv.gz
  ```
  *Kết quả in ra thực tế trên màn hình console:*
  ```text
  azureuser@vm-2:~$ python3 etl_benchmark.py dataset.csv dataset_clean.csv.gz
  [1] Kich thuoc file CSV goc: 1243.04 MB (1,303,422,535 bytes)
  [2] EXTRACT & TRANSFORM: Doc, lam sach 5,770,240 dong mat: 30.748 s (Toc do: 40.43 MB/s)
  [3] LOAD (Gzip Compressed): Ghi nen thanh cong, giu lai 3,404,765 dong hop le
      Kich thuoc file sau nen: 44.49 MB (46,650,007 bytes)
  [4] BENCHMARK GZ READ: Doc lai toan bo file nen 3,404,766 dong mat: 2.214 s (Toc do tuong duong: 561.48 MB/s)

  ==================================================
  -> Ty le nen du lieu:           27.94x (Tiet kiem 96.42% dung luong luu tru)
  -> Tang toc do doc (Speedup):   13.89 lan so voi xu ly file goc
  ==================================================
  ```

---

#### 6.4 Tổng Hợp Kết Quả Bằng Tay Vào Báo Cáo (Manual Report Summary)

Sau khi chạy xong các lệnh thực nghiệm, sinh viên đối chiếu các số liệu hiển thị trên màn hình Console và điền vào bảng tổng kết sau để chèn vào Báo cáo KLTN/Word:

##### 1. Biểu mẫu tổng hợp kết quả (Điền số liệu thực tế của bạn):

| Chỉ số đo đạc (Metric) | Định dạng CSV thô (Raw) | Định dạng Nén (Compressed Gzip) | Mức độ tối ưu / Cải thiện |
| :--- | :--- | :--- | :--- |
| **Dung lượng lưu trữ (Storage Size)** | `[Điền dung lượng CSV]` | `[Điền dung lượng GZ]` | **Tiết kiệm [..]%** (Tỷ lệ nén: [..]x) |
| **Thời gian xử lý / Đọc (Read Time)** | `[Điền t_process] s` | `[Điền t_read_gz] s` | **Nhanh hơn [..] lần** |
| **Tốc độ đọc I/O (Throughput)** | `[..] MB/s` | `[..] MB/s` | Tối ưu hóa I/O băng thông |
| **Số lượng bản ghi hợp lệ** | `[Tổng số dòng]` dòng | `[Số dòng sạch]` dòng sạch | Loại bỏ dòng rác/null |

##### 2. Biểu mẫu hiệu năng mạng liên vùng (VNet Peering):

| Chỉ số đường truyền | Giá trị đo đạc thực tế | Ghi chú kỹ thuật |
| :--- | :--- | :--- |
| **Vùng địa lý (Cross-Region)** | East Asia (Hong Kong) ↔ Korea Central (Seoul) | Định tuyến qua Microsoft Private Backbone |
| **Độ trễ trung bình (Avg RTT)** | `[Điền kết quả ping] ms` | Tuyến cáp quang riêng, không qua Internet |
| **Kích thước payload truyền tải** | `[Điền dung lượng file] MB` | Đáp ứng mức tải cao nhất **>= 1.0 GB** |
| **Xác thực toàn vẹn bit (MD5 Checksum)** | Trùng khớp 100% | Không lỗi bit, không suy hao gói tin |
| **SSH Stream nén luồng Gzip** | Thời gian: `[..] s` - Tốc độ: `[..] MB/s` | Kênh bảo mật SSH (Port 22), nén luồng song song |
| **Kéo dữ liệu từ Tailnet (`aria2c`)** | Thời gian: `[..] s` - Tốc độ: `[..] MB/s` | Tải 8 kết nối song song qua Tailscale Direct P2P |

##### 3. Bảng số liệu thực tế thu được (Dùng để đối chiếu và đưa vào báo cáo IS402):

### BẢNG SỐ LIỆU ĐỐI CHIẾU THỰC NGHIỆM THỰC TẾ (IS402)

| Chỉ số đo đạc (Metric) | Định dạng CSV thô (Raw) | Định dạng Nén (Compressed Gzip) | Mức độ tối ưu / Cải thiện |
| :--- | :--- | :--- | :--- |
| **Dung lượng lưu trữ (Storage Size)** | **`1,243.04 MB`** (1,303,422,535 bytes) | **`44.49 MB`** (46,650,007 bytes) | **Tiết kiệm 96.42%** (Tỷ lệ nén: **`27.94x`**) |
| **Thời gian xử lý / Đọc (Read Time)** | `30.748 s` | `2.214 s` | **Nhanh hơn 13.89 lần** |
| **Tốc độ đọc I/O (Throughput)** | `40.43 MB/s` | `561.48 MB/s` | Tối ưu hóa I/O vượt trội |
| **Số lượng bản ghi hợp lệ** | `5,770,240` dòng | `3,404,765` dòng sạch | Đã lọc loại bỏ 2,365,475 dòng thiếu/null |

**Đối chiếu hiệu năng truyền tải mạng liên vùng (Payload 1.24 GB):**

| Phương thức truyền tải | Thời gian truyền tải | Tốc độ truyền (Throughput) | Đánh giá & Ghi chú kỹ thuật |
| :--- | :--- | :--- | :--- |
| **SSH Stream nén luồng Gzip** | **`24.591 s`** | **`50.55 MB/s`** | **Tối ưu nhất:** Nén luồng tại máy nguồn, giải nén tại máy đích |
| **Kéo dữ liệu từ Tailnet (`aria2c -x 8`)** | `44.000 s` | `33.00 MB/s` | Kéo 8 luồng song song qua WireGuard Direct P2P |

> [!TIP]
> **Hướng dẫn nộp bài & Báo cáo KLTN/Word:**
> 1. Chụp ảnh màn hình cửa sổ Serial Console hiển thị kết quả kiểm thử Peering thành công (`nc -zv 10.0.0.100 22 succeeded`).
> 2. Chụp ảnh màn hình terminal các lệnh chạy `ping`, kết quả truyền tải SSH Stream và kết quả in ra của `etl_benchmark.py`.
> 3. Điền các thông số đo đạc thực tế của bạn vào biểu mẫu bảng tổng hợp ở trên và dán vào phần Thực nghiệm & Đánh giá của Báo cáo.
> 4. Trích dẫn cấu hình máy ảo `Standard_B2as_v2` / `Standard_B2s_v2` (8GB RAM) và kích thước dữ liệu `1.24 GB` để minh chứng thỏa mãn 100% Tiêu chí 1, Tiêu chí 2 và Tiêu chí 3 trong Rubric chấm điểm.

---

## 5. Dọn Dẹp Tài Nguyên (Clean Up)

Sau khi hoàn tất bài thực hành và chụp ảnh minh chứng, phương pháp dọn dẹp **nhanh nhất và sạch sẽ nhất** là sử dụng một câu lệnh duy nhất qua Azure CLI để xóa tận gốc toàn bộ Resource Group:

```bash
az group delete --name rg-asm --yes --no-wait
```

> [!IMPORTANT]
> **Tại sao đây là phương pháp tối ưu nhất?**
> * **Xóa sạch sẽ triệt để 100%:** Khi Resource Group bị xóa, Azure sẽ tự động kích hoạt cơ chế xóa dây chuyền (Cascade Deletion), giải phóng toàn bộ các tài nguyên bên trong (Virtual Machines, OS Disks, Network Interfaces, Subnets, NSG, VNets, Peering Links), không bao giờ để sót tài nguyên ngầm gây phát sinh chi phí cho tài khoản sinh viên.
> * **Nhanh nhất và không bị treo cửa sổ:**
>   * Tham số `--yes`: Tự động đồng ý xác nhận xóa mà không cần dừng lại hỏi prompt.
>   * Tham số `--no-wait`: Gửi yêu cầu xóa ngầm trực tiếp lên Azure Resource Manager và giải phóng ngay cửa sổ Terminal cho bạn tiếp tục công việc khác.
