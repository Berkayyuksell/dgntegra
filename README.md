# DGN Tegra - E-Fatura Entegrasyon Sistemi

Bu proje, e-fatura, e-arşiv ve gelen/giden fatura yönetimi için geliştirilmiş bir Laravel uygulamasıdır.

## Veritabanı Yapısı

### 📊 Views (Görünümler)

---

#### view_e_archive_panel

E-Arşiv faturalarını görüntülemek için kullanılan view.

```sql
CREATE VIEW [dbo].[view_e_archive_panel] AS
SELECT
    cdCurrAccDesc.CurrAccDescription,
    ai.InvoiceHeaderID,
    ai.InvoiceNumber,
    ai.InvoiceDate,
    ai.EInvoiceNumber,
    ai.IsReturn,
    e.payable_amount,
    COALESCE(p.TotalPrice, 0) AS price,
    CASE
        WHEN e.uuid IS NULL THEN 0
        ELSE 1
    END AS status
FROM trInvoiceHeader ai WITH (NOLOCK)
LEFT JOIN e_archive_invoices_outs e
    ON e.uuid = ai.InvoiceHeaderID
LEFT JOIN cdCurrAccDesc
    ON cdCurrAccDesc.CurrAccCode = ai.CurrAccCode
LEFT JOIN (
    SELECT
        InvoiceHeaderID,
        SUM(Doc_NetAmount) AS TotalPrice
    FROM AllInvoices WITH (NOLOCK)
    GROUP BY InvoiceHeaderID
) p ON p.InvoiceHeaderID = ai.InvoiceHeaderID
WHERE ai.CompanyCode = 1
  AND ai.IsReturn = 0
  AND ai.InvoiceTypeCode = 2;
```

---

#### view_e_invoices_in_panel

Gelen e-faturaları görüntülemek için kullanılan view.

```sql
CREATE VIEW [dbo].[view_e_invoices_in_panel] AS
SELECT
    ai.UUID,
    ai.ID,
    CONVERT(date, ai.IssueDate) AS IssueDate,
    e.supplier,
    e.payable_amount,
    CASE
        WHEN e.uuid IS NULL THEN 0
        ELSE 1
    END AS status
FROM invoices_ins e WITH (NOLOCK)
LEFT JOIN e_InboxInvoiceHeader ai
    ON e.uuid = ai.UUID
WHERE ai.CompanyCode = 1;
```

---

#### view_e_invoices_out_panel

Giden e-faturaları görüntülemek için kullanılan view.

```sql
CREATE VIEW [dbo].[view_e_invoices_out_panel] AS
SELECT
    cdCurrAccDesc.CurrAccDescription,
    ai.InvoiceHeaderID,
    ai.InvoiceNumber,
    ai.InvoiceDate,
    ai.EInvoiceNumber,
    ai.IsReturn,
    e.payable_amount,
    COALESCE(p.TotalPrice, 0) AS price,
    CASE
        WHEN e.uuid IS NULL THEN 0
        ELSE 1
    END AS status
FROM trInvoiceHeader ai WITH (NOLOCK)
LEFT JOIN invoices_outs e
    ON e.uuid = ai.InvoiceHeaderID
LEFT JOIN cdCurrAccDesc
    ON cdCurrAccDesc.CurrAccCode = ai.CurrAccCode
LEFT JOIN (
    SELECT
        InvoiceHeaderID,
        SUM(Doc_NetAmount) AS TotalPrice
    FROM AllInvoices WITH (NOLOCK)
    GROUP BY InvoiceHeaderID
) p ON p.InvoiceHeaderID = ai.InvoiceHeaderID
WHERE ai.CompanyCode = 1
  AND ai.IsReturn = 0
  AND ai.InvoiceTypeCode = 1
  AND ai.TransTypeCode = 2;
```

---

### 📋 Tables (Tablolar)

---

#### e_archive_invoices_outs

E-Arşiv giden faturaları için tablo.

```sql
CREATE TABLE dbo.e_archive_invoices_outs (
    id bigint IDENTITY(1,1) NOT NULL,
    invoice_id nvarchar(255) NULL,
    uuid uniqueidentifier NOT NULL,
    sender_name nvarchar(255) NULL,
    sender_identifier nvarchar(255) NULL,
    customer_name nvarchar(255) NULL,
    customer_identifier nvarchar(255) NULL,
    profile_id nvarchar(255) NULL,
    invoice_type nvarchar(255) NULL,
    earchive_type nvarchar(255) NULL,
    sending_type nvarchar(255) NULL,
    status nvarchar(255) NULL,
    status_code nvarchar(255) NULL,
    issue_date datetime2 NULL,
    payable_amount decimal(12,2) NULL,
    taxable_amount decimal(12,2) NULL,
    currency_code nvarchar(5) NOT NULL DEFAULT 'TRY',
    company_code nvarchar(255) NULL,
    created_at datetime2 NULL,
    updated_at datetime2 NULL,
    CONSTRAINT PK_e_archive_invoices_outs PRIMARY KEY (id),
    CONSTRAINT UQ_e_archive_invoices_outs_uuid UNIQUE (uuid)
);
```

---

#### invoices_outs

Giden faturalar için tablo.

```sql
CREATE TABLE dbo.invoices_outs (
    id bigint IDENTITY(1,1) NOT NULL,
    external_id nvarchar(255) NULL,
    list_id nvarchar(255) NULL,
    uuid uniqueidentifier NULL,
    sender nvarchar(255) NULL,
    receiver nvarchar(255) NULL,
    supplier nvarchar(255) NULL,
    customer nvarchar(255) NULL,
    issue_date datetime2 NULL,
    payable_amount decimal(15,2) NULL,
    from_address nvarchar(255) NULL,
    to_address nvarchar(255) NULL,
    profile_id nvarchar(255) NULL,
    invoice_type_code nvarchar(255) NULL,
    status nvarchar(255) NULL,
    status_description nvarchar(255) NULL,
    gib_status_code nvarchar(255) NULL,
    gib_status_description nvarchar(255) NULL,
    cdate datetime2 NULL,
    envelope_identifier uniqueidentifier NULL,
    status_code nvarchar(255) NULL,
    line_extension_amount decimal(15,2) NULL,
    tax_exclusive_total_amount decimal(15,2) NULL,
    tax_inclusive_total_amount decimal(15,2) NULL,
    allowance_total_amount decimal(15,2) NULL,
    company_code nvarchar(255) NULL,
    created_at datetime2 NULL,
    updated_at datetime2 NULL,
    CONSTRAINT PK_invoices_outs PRIMARY KEY (id)
);
```

---

#### invoices_ins

Gelen faturalar için tablo.

```sql
CREATE TABLE dbo.invoices_ins (
    id bigint IDENTITY(1,1) NOT NULL,
    external_id nvarchar(255) NULL,
    list_id nvarchar(255) NULL,
    uuid uniqueidentifier NULL,
    sender nvarchar(255) NULL,
    receiver nvarchar(255) NULL,
    supplier nvarchar(255) NULL,
    customer nvarchar(255) NULL,
    issue_date datetime2 NULL,
    payable_amount decimal(15,2) NULL,
    from_address nvarchar(255) NULL,
    to_address nvarchar(255) NULL,
    profile_id nvarchar(255) NULL,
    invoice_type_code nvarchar(255) NULL,
    status nvarchar(255) NULL,
    status_description nvarchar(255) NULL,
    gib_status_code nvarchar(255) NULL,
    gib_status_description nvarchar(255) NULL,
    cdate datetime2 NULL,
    envelope_identifier uniqueidentifier NULL,
    status_code nvarchar(255) NULL,
    line_extension_amount decimal(15,2) NULL,
    tax_exclusive_total_amount decimal(15,2) NULL,
    tax_inclusive_total_amount decimal(15,2) NULL,
    allowance_total_amount decimal(15,2) NULL,
    company_code nvarchar(255) NULL,
    created_at datetime2 NULL,
    updated_at datetime2 NULL,
    CONSTRAINT PK_invoices_ins PRIMARY KEY (id)
);
```

---

#### sync_logs

Senkronizasyon logları için tablo.

```sql
CREATE TABLE sync_logs (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    company_code NVARCHAR(255) NULL,
    created_at DATETIME2 NULL,
    updated_at DATETIME2 NULL
);
```

---

## Kurulum

1. Projeyi klonlayın
2. Bağımlılıkları yükleyin: `composer install`
3. `.env` dosyasını oluşturun ve veritabanı ayarlarını yapın
4. Yukarıdaki SQL sorgularını MSSQL veritabanınızda çalıştırın
5. Uygulamayı başlatın: `php artisan serve`
