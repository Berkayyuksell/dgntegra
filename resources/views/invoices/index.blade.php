@extends('layouts.app')

@section('content')
    <div class="my-5">

        <!-- 🔹 Fatura Özet Kartları -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark">Toplam Fatura Tutarı</h5>
                            <p class="text-muted mb-0" id="totalAmount">₺0</p>
                        </div>
                        <div>
                            <canvas id="amountChart" width="80" height="80"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark">Toplam Fatura Adedi</h5>
                            <p class="text-muted mb-0" id="totalCount">0</p>
                        </div>
                        <div>
                            <canvas id="countChart" width="80" height="80"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card border-0">
            <div class="card-header bg-white border-bottom py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Giden Fatura</h4>
                        <p class="text-muted mb-0 small">Tüm faturalarınızı görüntüleyin</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Başlangıç" />
                        <input type="date" id="end_date" class="form-control form-control-sm" placeholder="Bitiş" />
                        <button id="filterd" class="btn btn-sm btn-danger">Gitmeyen</button>
                        <button id="filterBtn" class="btn btn-sm btn-dark">Filtrele</button>
                        <button id="resetBtn" class="btn btn-sm btn-secondary">Temizle</button>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="invoiceTable">
                        <thead class="bg-light">
                        <tr>
                            <th class="border-0 py-3 ps-4">Ref no</th>
                            <th class="border-0 py-3">Fatura numara</th>
                            <th class="border-0 py-3">Müşteri</th>
                            <th class="border-0 py-3">Tutar</th>
                            <th class="border-0 py-3">Birim</th>
                            <th class="border-0 py-3">Birim</th>
                            <th class="border-0 py-3">Durum</th>
                            <th class="border-0 py-3">Şirket Kodu</th>
                            <th class="border-0 py-3 pe-4 text-end">İşlemler</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function () {
            // 🔹 Gitmeyen filtresi için global değişken
            window.isGitmeyenFilterActive = false;

            // 🔹 DataTable başlatma
            var table = $('#invoiceTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("invoices.data") }}',
                    data: function (d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.isInvoiceOkey = window.isGitmeyenFilterActive ? 0 : '';
                    },
                    dataSrc: function (json){
                        let totalAmount = 0;
                        json.data.forEach(row => {
                            totalAmount += parseFloat(row.doc_price);
                        });
                        $('#totalAmount').text('₺' + totalAmount.toLocaleString('tr-TR', {minimumFractionDigits:2}));
                        $('#totalCount').text(json.data.length);

                        updateChart(amountChart, totalAmount);
                        updateChart(countChart, json.data.length);
                        return json.data;
                    }
                },
                columns: [
                    { data: 'InvoiceNumber', name: 'InvoiceNumber', className: 'ps-4 text-muted' },
                    { data: 'EInvoiceNumber', name: 'EInvoiceNumber', className: 'fw-bold text-dark' },
                    { data: 'customers', name: 'customers' },
                    { data: 'doc_price', name: 'doc_price', orderable: false, searchable: false },
                    { data: 'DocCurrencyCode', name: 'DocCurrencyCode' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                    { data: 'InvoiceDate', name: 'InvoiceDate' },
                    { data: 'CompanyCode', name : 'CompanyCode'},
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'pe-4 text-end' }
                ],
                pageLength: 15,
                lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
                order: [[0, 'desc']],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json",
                    search: "",
                    searchPlaceholder: "Ara..."
                },
                dom: '<"row px-4 pt-3 pb-2"<"col-sm-6"l><"col-sm-6 text-end"f>>rt<"row px-4 py-3 border-top"<"col-sm-6 text-muted small"i><"col-sm-6"p>>',
                initComplete: function () {
                    $('.dataTables_filter input').addClass('form-control form-control-sm').css('width', '250px');
                    $('.dataTables_length select').addClass('form-select form-select-sm').css('width', 'auto');
                }
            });

            // 🔸 Gitmeyen butonu → sadece 0'ları göster
            $('#filterd').on('click', function () {
                window.isGitmeyenFilterActive = true;
                table.ajax.reload();
            });


            $('#filterBtn').on('click', function () {
                window.isGitmeyenFilterActive = false;
                table.ajax.reload();
            });


            $('#resetBtn').on('click', function () {
                $('#start_date, #end_date').val('');
                window.isGitmeyenFilterActive = false;
                table.ajax.reload();
            });

            // (Opsiyonel) Sync butonu varsa
            $('#syncBtn').on('click', function () {
                const $btn = $(this);
                const originalHtml = $btn.html();

                $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Senkronize Ediliyor...');
                $btn.prop('disabled', true);

                setTimeout(function () {
                    table.ajax.reload();
                    $btn.html(originalHtml);
                    $btn.prop('disabled', false);
                }, 1500);
            });

            const amountCtx = document.getElementById('amountChart').getContext('2d');
            const countCtx = document.getElementById('countChart').getContext('2d');

            const amountChart = new Chart(amountCtx, {
                type: 'doughnut',
                data: { labels: ['Tutar'], datasets: [{ data: [0], backgroundColor: ['#6f42c1'], borderColor: ['#fff'], borderWidth: 2 }] },
                options: { cutout: '75%', plugins: { legend: { display: false } } }
            });

            const countChart = new Chart(countCtx, {
                type: 'doughnut',
                data: { labels: ['Adet'], datasets: [{ data: [0], backgroundColor: ['#343a40'], borderColor: ['#fff'], borderWidth: 2 }] },
                options: { cutout: '75%', plugins: { legend: { display: false } } }
            });

            function updateChart(chart, value) {
                chart.data.datasets[0].data[0] = value;
                chart.update();
            }
        });
    </script>
@endsection
