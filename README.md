
## In Invoices
```sql 
create table invoices_ins (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    
    -- Attributes
    external_id NVARCHAR(255) NULL,
    list_id NVARCHAR(255) NULL,
    uuid UNIQUEIDENTIFIER NULL,

    -- Header
    sender NVARCHAR(255) NULL,
    receiver NVARCHAR(255) NULL,
    supplier NVARCHAR(255) NULL,
    customer NVARCHAR(255) NULL,
    issue_date DATETIME2 NULL, -- Düzeltilmiş
    payable_amount DECIMAL(15,2) NULL,
    from_address NVARCHAR(255) NULL,
    to_address NVARCHAR(255) NULL,
    profile_id NVARCHAR(255) NULL,
    invoice_type_code NVARCHAR(255) NULL,
    status NVARCHAR(255) NULL,
    status_description NVARCHAR(255) NULL,
    gib_status_code NVARCHAR(255) NULL,
    gib_status_description NVARCHAR(255) NULL,
    cdate DATETIME2 NULL, -- Düzeltilmiş
    envelope_identifier UNIQUEIDENTIFIER NULL,
    status_code NVARCHAR(255) NULL,
    line_extension_amount DECIMAL(15,2) NULL,
    tax_exclusive_total_amount DECIMAL(15,2) NULL,
    tax_inclusive_total_amount DECIMAL(15,2) NULL,
    allowance_total_amount DECIMAL(15,2) NULL,

    company_code NVARCHAR(255) NULL,
    
    created_at DATETIME2 NULL,
    updated_at DATETIME2 NULL
);
````

## Out Invoices
```sql 
create table invoices_outs (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    
    -- Attributes
    external_id NVARCHAR(255) NULL,
    list_id NVARCHAR(255) NULL,
    uuid UNIQUEIDENTIFIER NULL,

    -- Header
    sender NVARCHAR(255) NULL,
    receiver NVARCHAR(255) NULL,
    supplier NVARCHAR(255) NULL,
    customer NVARCHAR(255) NULL,
    issue_date DATETIME2 NULL, -- Düzeltilmiş
    payable_amount DECIMAL(15,2) NULL,
    from_address NVARCHAR(255) NULL,
    to_address NVARCHAR(255) NULL,
    profile_id NVARCHAR(255) NULL,
    invoice_type_code NVARCHAR(255) NULL,
    status NVARCHAR(255) NULL,
    status_description NVARCHAR(255) NULL,
    gib_status_code NVARCHAR(255) NULL,
    gib_status_description NVARCHAR(255) NULL,
    cdate DATETIME2 NULL, -- Düzeltilmiş
    envelope_identifier UNIQUEIDENTIFIER NULL,
    status_code NVARCHAR(255) NULL,
    line_extension_amount DECIMAL(15,2) NULL,
    tax_exclusive_total_amount DECIMAL(15,2) NULL,
    tax_inclusive_total_amount DECIMAL(15,2) NULL,
    allowance_total_amount DECIMAL(15,2) NULL,

    company_code NVARCHAR(255) NULL,
    
    created_at DATETIME2 NULL,
    updated_at DATETIME2 NULL
);
````

## e archive
```sql 
CREATE TABLE e_archive_invoices_outs (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    
    invoice_id NVARCHAR(255) NULL,
    uuid UNIQUEIDENTIFIER NOT NULL UNIQUE,
    sender_name NVARCHAR(255) NULL,
    sender_identifier NVARCHAR(255) NULL,
    customer_name NVARCHAR(255) NULL,
    customer_identifier NVARCHAR(255) NULL,
    profile_id NVARCHAR(255) NULL,
    invoice_type NVARCHAR(255) NULL,
    earchive_type NVARCHAR(255) NULL,
    sending_type NVARCHAR(255) NULL,
    status NVARCHAR(255) NULL,
    status_code NVARCHAR(255) NULL,
    issue_date DATETIME2 NULL,
    payable_amount DECIMAL(12,2) NULL,
    taxable_amount DECIMAL(12,2) NULL,
    currency_code NVARCHAR(5) NOT NULL DEFAULT 'TRY',
    company_code NVARCHAR(255) NULL,
    
    created_at DATETIME2 NULL,
    updated_at DATETIME2 NULL
);
````


## sync 
```sql 
CREATE TABLE sync_logs (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    company_code NVARCHAR(255)  NULL,
    created_at DATETIME2 NULL,
    updated_at DATETIME2 NULL
);
````


- PHP SQL SERVER DLL VE PHP INI yükle !!!!!

DEPLOY
https://gist.github.com/amestsantim/d79cc93fe98d164d13a11eda473a7a9a



## DOSYALARA İZİN VER

```powershell
icacls "C:\inetpub\wwwroot\fapp\storage" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "C:\inetpub\wwwroot\fapp\bootstrap\cache" /grant "IIS_IUSRS:(OI)(CI)F" /T
