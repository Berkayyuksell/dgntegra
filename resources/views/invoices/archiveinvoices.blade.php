
@extends('layouts.app')

@section('content')
    <div class="my-5">
        <div class="card border-0">
            {{-- CARD HEADER --}}
            <div class="card-header bg-white border-bottom py-4" style="overflow: visible;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">E-Arşiv Fatura</h4>
                        <p class="text-muted mb-0 small">Tüm faturalarınızı görüntüleyin</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        {{-- TARİH FİLTRELERİ (Son 1 ay, bugün hariç) --}}
                        <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Başlangıç" value="{{ \Carbon\Carbon::now()->subDay()->subMonth()->format('Y-m-d') }}" />
                        <input type="date" id="end_date" class="form-control form-control-sm" placeholder="Bitiş" value="{{ \Carbon\Carbon::now()->subDay()->format('Y-m-d') }}" />
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
                    <span class="text-muted">Toplam:</span>
                    <span class="fw-bold">{{ number_format((float)($stats->toplam ?? 0), 2, ',', '.') }} ₺</span>
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
                url: '{{ route("invoices.archive.data") }}?' + params.toString(),
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
                    {title: "Ref no", field: "InvoiceNumber", sorter: "string", headerFilter: "input"},
                    {title: "Fatura Numara", field: "EInvoiceNumber", sorter: "string", headerFilter: "input"},
                    {title: "Müşteri", field: "customer", sorter: "string", headerFilter: "input"},
                    {title: "Tutar", field: "doc_price", sorter: "string", hozAlign: "right"},
                    {title: "Birim", field: "DocCurrencyCode", sorter: "string"},
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
                    {title: "Tarih", field: "InvoiceDate", sorter: "date"}
                ],
            });

            $('#filterd').addClass('active');
            loadData();

            $('#filterd').on('click', function () {
                isGitmeyenFilterActive = true;
                $('#filterd').addClass('active');
                loadData();
            });

            $('#filterBtn').on('click', function () {
                isGitmeyenFilterActive = false;
                $('#filterd').removeClass('active');
                loadData();
            });

            $('#resetBtn').on('click', function () {
                const endDate = new Date();
                endDate.setDate(endDate.getDate() - 1); // dün
                const startDate = new Date(endDate.getTime());
                startDate.setMonth(startDate.getMonth() - 1);

                $('#start_date').val(startDate.toISOString().split('T')[0]);
                $('#end_date').val(endDate.toISOString().split('T')[0]);
                isGitmeyenFilterActive = false;
                $('#filterd').removeClass('active');
                loadData();
            });
        });
    </script>
@endsection
