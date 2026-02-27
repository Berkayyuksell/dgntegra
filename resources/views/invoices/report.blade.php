@extends('layouts.app')

@section('content')
<div class="my-5">

    {{-- BAŞLIK & FİLTRE --}}
    <div class="card border-0 mb-4">
        <div class="card-header bg-white border-bottom py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">Fatura Raporu</h4>
                    <p class="text-muted mb-0 small">Gelen, Giden ve E-Arşiv faturalarının özet raporu</p>
                </div>
                <form method="GET" action="{{ route('invoices.report') }}" class="d-flex align-items-center gap-2">
                    <input type="date" name="start_date" class="form-control form-control-sm"
                           value="{{ $startDate }}" placeholder="Başlangıç" />
                    <input type="date" name="end_date" class="form-control form-control-sm"
                           value="{{ $endDate }}" placeholder="Bitiş" />
                    <button type="submit" class="btn btn-sm btn-dark">Filtrele</button>
                    <a href="{{ route('invoices.report') }}" class="btn btn-sm btn-secondary">Temizle</a>
                </form>
            </div>
        </div>
    </div>

    @php
        $fmt = fn($val) => number_format((float)($val ?? 0), 2, ',', '.');

        $gelenVergi  = ($gelen->vergili_tutar ?? 0) - ($gelen->vergisiz_tutar ?? 0);
        $gidenVergi  = ($giden->vergili_tutar ?? 0) - ($giden->vergisiz_tutar ?? 0);
        $arsivVergi  = ($arsiv->toplam_odeme ?? 0) - ($arsiv->vergisiz_tutar ?? 0);
    @endphp

    <div class="row g-4">

        {{-- GELEN FATURA --}}
        <div class="col-12">
            <div class="card border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" style="background:#3b82f6;font-size:.8rem;padding:6px 14px;">
                        <i class="bi bi-arrow-down-circle me-1"></i> Gelen Fatura
                    </span>
                    <span class="text-muted small">invoices_ins</span>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Toplam Fatura</div>
                            <div class="fw-bold fs-4">{{ number_format($gelen->total ?? 0) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">adet</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Ödenecek Tutar</div>
                            <div class="fw-bold fs-5">{{ $fmt($gelen->toplam_odeme) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Vergisiz Tutar</div>
                            <div class="fw-bold fs-5 text-success">{{ $fmt($gelen->vergisiz_tutar) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Vergili Tutar (KDV dahil)</div>
                            <div class="fw-bold fs-5 text-primary">{{ $fmt($gelen->vergili_tutar) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Toplam KDV</div>
                            <div class="fw-bold fs-5 text-warning">{{ $fmt($gelenVergi) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Mal/Hizmet Toplam</div>
                            <div class="fw-bold fs-5">{{ $fmt($gelen->mal_hizmet_toplam) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col p-4 text-center">
                            <div class="text-muted small mb-1">İskonto</div>
                            <div class="fw-bold fs-5 text-danger">{{ $fmt($gelen->iskonto) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- GİDEN FATURA --}}
        <div class="col-12">
            <div class="card border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" style="background:#10b981;font-size:.8rem;padding:6px 14px;">
                        <i class="bi bi-arrow-up-circle me-1"></i> Giden Fatura
                    </span>
                    <span class="text-muted small">invoices_outs</span>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Toplam Fatura</div>
                            <div class="fw-bold fs-4">{{ number_format($giden->total ?? 0) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">adet</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Ödenecek Tutar</div>
                            <div class="fw-bold fs-5">{{ $fmt($giden->toplam_odeme) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Vergisiz Tutar</div>
                            <div class="fw-bold fs-5 text-success">{{ $fmt($giden->vergisiz_tutar) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Vergili Tutar (KDV dahil)</div>
                            <div class="fw-bold fs-5 text-primary">{{ $fmt($giden->vergili_tutar) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Toplam KDV</div>
                            <div class="fw-bold fs-5 text-warning">{{ $fmt($gidenVergi) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Mal/Hizmet Toplam</div>
                            <div class="fw-bold fs-5">{{ $fmt($giden->mal_hizmet_toplam) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col p-4 text-center">
                            <div class="text-muted small mb-1">İskonto</div>
                            <div class="fw-bold fs-5 text-danger">{{ $fmt($giden->iskonto) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- E-ARŞİV FATURA --}}
        <div class="col-12">
            <div class="card border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" style="background:#8b5cf6;font-size:.8rem;padding:6px 14px;">
                        <i class="bi bi-archive me-1"></i> E-Arşiv Fatura
                    </span>
                    <span class="text-muted small">e_archive_invoices_outs</span>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Toplam Fatura</div>
                            <div class="fw-bold fs-4">{{ number_format($arsiv->total ?? 0) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">adet</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Ödenecek Tutar</div>
                            <div class="fw-bold fs-5">{{ $fmt($arsiv->toplam_odeme) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col border-end p-4 text-center">
                            <div class="text-muted small mb-1">Vergisiz Tutar (Matrah)</div>
                            <div class="fw-bold fs-5 text-success">{{ $fmt($arsiv->vergisiz_tutar) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                        <div class="col p-4 text-center">
                            <div class="text-muted small mb-1">Toplam KDV</div>
                            <div class="fw-bold fs-5 text-warning">{{ $fmt($arsivVergi) }}</div>
                            <div class="text-muted" style="font-size:.75rem;">TRY</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
