@extends('layouts.app')

@section('content')
    <div class="my-5">
        <div class="card border-0">
            <div class="card-header bg-white border-bottom py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Gelen Fatura </h4>
                        <p class="text-muted mb-0 small">Tüm faturalarınızı görüntüleyin</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Başlangıç" />
                        <input type="date" id="end_date" class="form-control form-control-sm" placeholder="Bitiş" />
                        <button id="filterd2" class="btn btn-sm btn-warning">İşlenmemiş </button>
                        <button id="filterd" class="btn btn-sm btn-danger">Gitmeyen </button>
                        <button id="filterBtn" class="btn btn-sm btn-dark">Filtrele</button>
                        <button id="resetBtn" class="btn btn-sm btn-secondary">Temizle</button>
                    </div>
                </div>

            </div>
        
                {{-- AYLIK İSTATİSTİK BARI --}}
            <div class="d-flex align-items-center gap-4 px-4 py-3 border-bottom bg-white" style="font-size:.85rem;">
                <span class="text-muted">{{ \Carbon\Carbon::now()->startOfMonth()->format('d.m.Y') }} — {{ \Carbon\Carbon::now()->format('d.m.Y') }}</span>
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted">Fatura:</span>
                    <span class="fw-bold">{{ number_format($stats->total ?? 0) }} adet</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted">Toplam:</span>
                    <span class="fw-bold">{{ number_format((float)($stats->toplam ?? 0), 2, ',', '.') }} ₺</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted">Vergisiz:</span>
                    <span class="fw-bold text-success">{{ number_format((float)($stats->vergisiz ?? 0), 2, ',', '.') }} ₺</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted">Vergili (KDV dahil):</span>
                    <span class="fw-bold text-primary">{{ number_format((float)($stats->vergili ?? 0), 2, ',', '.') }} ₺</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted">KDV:</span>
                    <span class="fw-bold text-warning">{{ number_format((float)(($stats->vergili ?? 0) - ($stats->vergisiz ?? 0)), 2, ',', '.') }} ₺</span>
                </div>
            </div>

        <div class="card-body p-0 position-relative">
                <div id="loadingOverlay" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 1000; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <p class="mt-2 fw-bold">Veriler yükleniyor...</p>
                    </div>
                </div>
                <div id="invoiceTable"></div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <link href="https://unpkg.com/tabulator-tables@5.5.2/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
    
    <script>
        let table;
        let isGitmeyenFilterActive = true;

        function loadData() {
            const params = new URLSearchParams({
                start_date: $('#start_date').val() || '',
                end_date: $('#end_date').val() || '',
                isInvoiceOkey: isGitmeyenFilterActive ? '0' : ''
            });

            $.ajax({
                url: '{{ route("invoices.index_in.data") }}?' + params.toString(),
                method: 'GET',
                beforeSend: function() {
                    $('#loadingOverlay').show();
                },
                success: function(data) {
                    table.setData(data);
                },
                error: function() {
                    alert('Veri yüklenirken hata oluştu');
                },
                complete: function() {
                    $('#loadingOverlay').hide();
                }
            });
        }

        $(document).ready(function() {
            table = new Tabulator("#invoiceTable", {
                data: [],
                layout: "fitColumns",
                height: "600px",
                virtualDom: true,
                pagination: true,
                paginationSize: 50,
                paginationSizeSelector: [25, 50, 100, 200],
                placeholder: "Tabloda gösterilecek veri yok",
                columns: [
                    {title: "#", formatter: "rownum", hozAlign: "center", width: 60},
                    {title: "ID", field: "external_id", sorter: "string", headerFilter: "input"},
                    {title: "Tedarikçi", field: "supplier", sorter: "string", headerFilter: "input"},
                    {title: "Tutar", field: "payable_amount", sorter: "string", hozAlign: "right"},
                    {
                        title: "Durum", 
                        field: "status", 
                        sorter: "string",
                        formatter: function(cell) {
                            const row = cell.getRow().getData();
                            const color = row.status_color;
                            return `<span class="badge bg-${color}">${cell.getValue()}</span>`;
                        }
                    },
                    {title: "Tarih", field: "cdate", sorter: "date"}
                ],
            });

            $('#filterd').addClass('active');
            loadData();

            $('#filterd2').on('click', function () {
                isGitmeyenFilterActive = 2;
                $('#filterd').removeClass('active');
                $('#filterd2').addClass('active');
                loadData();
            });

            $('#filterd').on('click', function () {
                isGitmeyenFilterActive = true;
                $('#filterd').addClass('active');
                $('#filterd2').removeClass('active');
                loadData();
            });

            $('#filterBtn').on('click', function () {
                isGitmeyenFilterActive = false;
                $('#filterd, #filterd2').removeClass('active');
                loadData();
            });

            $('#resetBtn').on('click', function () {
                $('#start_date, #end_date').val('');
                isGitmeyenFilterActive = false;
                $('#filterd, #filterd2').removeClass('active');
                loadData();
            });
        });
    </script>
@endsection
