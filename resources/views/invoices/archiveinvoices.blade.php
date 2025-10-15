
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
                        {{-- 🔽 DROPDOWN --}}
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button"
                                    id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Kategori Seçin
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                                <li><a class="dropdown-item" href="#">Hepsi</a></li>
                                <li><a class="dropdown-item" href="#">Onaylı</a></li>
                                <li><a class="dropdown-item" href="#">Bekleyen</a></li>
                                <li><a class="dropdown-item" href="#">İptal Edilmiş</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button"
                                    id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Kategori Seçin
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                                <li><a class="dropdown-item" href="#">Hepsi</a></li>
                                <li><a class="dropdown-item" href="#">Onaylı</a></li>
                                <li><a class="dropdown-item" href="#">Bekleyen</a></li>
                                <li><a class="dropdown-item" href="#">İptal Edilmiş</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button"
                                    id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Kategori Seçin
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                                <li><a class="dropdown-item" href="#">Hepsi</a></li>
                                <li><a class="dropdown-item" href="#">Onaylı</a></li>
                                <li><a class="dropdown-item" href="#">Bekleyen</a></li>
                                <li><a class="dropdown-item" href="#">İptal Edilmiş</a></li>
                            </ul>
                        </div>


                        {{-- TARİH FİLTRELERİ --}}
                        <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Başlangıç" />
                        <input type="date" id="end_date" class="form-control form-control-sm" placeholder="Bitiş" />
                        <button id="filterd" class="btn btn-sm btn-danger">Gitmeyen </button>
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
                            <th class="border-0 py-3 ps-4">ID</th>
                            <th class="border-0 py-3">Tarih</th>
                            <th class="border-0 py-3">Tutar</th>
                            <th class="border-0 py-3">Müşteri</th>
                            <th class="border-0 py-3">Tedarikçi</th>
                            <th class="border-0 py-3">Durum</th>
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
            $('#invoiceTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("invoices.archive.data") }}',
                columns: [
                    {
                        data: 'invoice_id',
                        name: 'invoice_id'
                    },
                    {
                        data: 'issue_date',
                        name: 'issue_date'
                    },
                    {
                        data: 'payable_amount',
                        name: 'payable_amount',
                        className: 'fw-bold text-dark'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'sender_name',
                        name: 'sender_name'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false,
                        searchable: false
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
