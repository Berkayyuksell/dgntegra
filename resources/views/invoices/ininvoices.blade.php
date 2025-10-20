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
        </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="invoiceTable">
                        <thead class="bg-light">
                        <tr>
                            <th class="border-0 py-3 ps-4">ID</th>
                            <th class="border-0 py-3">Tedarikçi</th>
                            <th class="border-0 py-3">Tutar</th>
                            <th class="border-0 py-3">Durum</th>
                            <th class="border-0 py-4 "style="width: 125px;">Tarih</th>
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
    <script>

        $(function() {
            window.isGitmeyenFilterActive = false;
            var table = $('#invoiceTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url : '{{ route("invoices.index_in.data") }}',
                    data: function(d){
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.isInvoiceOkey = window.isGitmeyenFilterActive ? 0 : '';
                    }
                },
                columns: [
                    {
                        data: 'external_id',
                        name: 'external_id',
                        className: 'ps-4 text-muted'
                    },
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'payable_amount',
                        name: 'payable_amount'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'cdate',
                        name: 'cdate',
                        className: 'fw-bold text-dark'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'pe-4 text-end'
                    }
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
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm').css('width', '250px');
                    $('.dataTables_length select').addClass('form-select form-select-sm').css('width', 'auto');
                }
            });

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


            $('#syncBtn').on('click', function() {
                const $btn = $(this);
                const originalHtml = $btn.html();

                $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Senkronize Ediliyor...');
                $btn.prop('disabled', true);

                setTimeout(function() {
                    $('#invoiceTable').DataTable().ajax.reload();
                    $btn.html(originalHtml);
                    $btn.prop('disabled', false);
                }, 1500);
            });
        });
    </script>
@endsection
