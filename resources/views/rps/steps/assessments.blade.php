{{-- resources/views/rps/steps/assessments.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-xxl">

    @php
        // pastikan subClos sudah ter-load
        $clos->loadMissing('subClos');

        $matrix      = $weights     ?? [];
        $catWeights  = $catWeights  ?? [];
        $catDesc     = $catDesc     ?? [];
        $catDue      = $catDue      ?? [];
    @endphp

    <style>
        .table-assess {
            font-size: 13px;
        }
        .table-assess th,
        .table-assess td {
            padding: .35rem .5rem;
        }
        .table-assess thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        /* CPMK row */
        .cpmk-row {
            background: #f2f6ff;
            font-weight: 600;
        }

        /* sub-CPMK row */
        .sub-row {
            background: #fffdf5;     /* krem lembut */
            font-size: 12px;
        }
        .sub-row td:first-child {
            padding-left: 2.1rem;
            position: relative;
        }
        .sub-row td:first-child::before {
            content: '';
            position: absolute;
            left: .8rem;
            top: 50%;
            width: 10px;
            height: 1px;
            background: #999;
        }

        /* input CPMK yg ada nilai */
        .matrix-input {
            background: #e3ecff;
            border-color: #c0cef8;
            font-weight: 500;
        }

        /* input CPMK yg kosong (greyed out) */
        .matrix-input-empty {
            background: #f3f3f3;
            border-color: #e0e0e0;
            color: #999;
        }

        /* sel sub-CPMK yg punya angka */
        .sub-cell-has-value {
            background: #ffe9b3;
            font-weight: 500;
        }

        .total-col {
            background: #003366;
            color: #fff;
            font-weight: 600;
            white-space: nowrap;
        }

        .tfoot-total {
            background: #fffaf0;
            font-size: 12px;
        }
        .tfoot-total td,
        .tfoot-total th {
            border-top-width: 2px;
        }
        .tfoot-desc,
        .tfoot-due {
            background: #fcfcfc;
        }
        .tfoot-label {
            background: #f0f0f0;
            font-weight: 600;
            white-space: nowrap;
        }

        .small-hint {
            font-size: 10px;
            color: #666;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Buat RPS — Step 3: Ringkasan Bobot CPMK & Assessment</h4>
        <div class="text-muted small">RPS ID: #{{ $rps->id }}</div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <b>Periksa input:</b>
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- RINGKASAN CPMK --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0">Ringkasan Bobot CPMK (hasil CPL → CPMK di Step 2)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:80px;">CPMK</th>
                            <th>Deskripsi CPMK (CLO)</th>
                            <th style="width:140px;" class="text-end">Bobot CPMK global (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clos as $clo)
                            <tr>
                                <td>CPMK{{ $clo->no }}</td>
                                <td>{{ $clo->description }}</td>
                                <td class="text-end">
                                    @if(!is_null($clo->weight_percent))
                                        {{ number_format($clo->weight_percent, 2) }}%
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted small">
                                    Belum ada CPMK yang diinput di Step 2.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer small text-muted d-flex justify-content-between">
            <span>Total bobot CPMK (target 100%):</span>
            <span class="fw-semibold">{{ number_format($clos->sum('weight_percent'), 2) }}%</span>
        </div>
    </div>

    {{-- MATRKS CPMK / sub-CPMK × KATEGORI --}}
    <form method="POST" action="{{ route('rps.store.step', 3) }}" class="card shadow-sm border-0">
        @csrf

        <div class="card-header bg-white">
            <h5 class="mb-0">Matriks CPMK / sub-CPMK × Kategori Assessment</h5>
            <div class="small-hint mt-1">
                Semua angka pada tabel di bawah adalah <strong>dalam persen</strong> dan sudah dihitung di
                Step 2 (CPL → CPMK → sub-CPMK & assessment). Dosen <strong>tidak dapat mengubah</strong>
                angka di sini; bagian ini hanya ringkasan. Silakan isi <strong>Deskripsi</strong> dan
                <strong>Due Date</strong> di bagian bawah.
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 table-sm table-assess">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width:190px;">
                                CPMK / Sub-CPMK
                                <div class="small-hint">* dalam persen</div>
                            </th>

                            @foreach($cats as $cat)
                                <th>
                                    {{ $cat->code }}
                                    <div class="small text-muted">{{ $cat->name }}</div>
                                </th>
                            @endforeach

                            <th style="width:130px;" class="total-col">
                                TOTAL PER CPMK
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($clos as $clo)
                        {{-- CPMK --}}
                        <tr class="cpmk-row">
                            <td>CPMK {{ $clo->no }}</td>

                            @foreach($cats as $cat)
                                @php
                                    $valLocal = (float)($matrix[$cat->id][$clo->id] ?? 0);
                                    $hasValue = $valLocal > 0;
                                @endphp
                                <td class="text-end">
                                    @if($hasValue)
                                        <input
                                            type="number" step="0.01" min="0" max="100"
                                            name="weights[{{ $cat->id }}][{{ $clo->id }}]"
                                            class="form-control form-control-sm text-end matrix-input"
                                            value="{{ number_format($valLocal, 2) }}"
                                            readonly
                                        >
                                    @else
                                        <input
                                            type="text"
                                            class="form-control form-control-sm text-end matrix-input-empty"
                                            value=""
                                            disabled
                                        >
                                    @endif
                                </td>
                            @endforeach

                            <td class="text-end total-col">
                                {{ !is_null($clo->weight_percent)
                                    ? number_format($clo->weight_percent, 2).'%' 
                                    : '' }}
                            </td>
                        </tr>

                        {{-- sub-CPMK --}}
                        @foreach($clo->subClos as $sub)
                            @php
                                $ratio = 0;
                                if (!is_null($clo->weight_percent) && $clo->weight_percent > 0) {
                                    $ratio = (float)($sub->weight_percent ?? 0) / (float)$clo->weight_percent;
                                }
                            @endphp
                            <tr class="sub-row">
                                <td>Sub CPMK {{ $clo->no }}.{{ $sub->no }}</td>

                                @foreach($cats as $cat)
                                    @php
                                        $valLocal = (float)($matrix[$cat->id][$clo->id] ?? 0);
                                        $subVal   = $valLocal * $ratio;
                                        $hasSub   = $subVal > 0;
                                    @endphp
                                    <td class="text-end {{ $hasSub ? 'sub-cell-has-value' : '' }}">
                                        {{ $hasSub ? number_format($subVal, 2) : '' }}
                                    </td>
                                @endforeach

                                <td class="text-end">
                                    {{ !is_null($sub->weight_percent)
                                        ? number_format($sub->weight_percent, 2).'%' 
                                        : '' }}
                                </td>
                            </tr>
                        @endforeach

                    @empty
                        <tr>
                            <td colspan="{{ 2 + $cats->count() }}"
                                class="text-center text-muted small">
                                Belum ada CPMK yang diinput di Step 2.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>

                    <tfoot>
                        {{-- TOTAL BOBOT KATEGORI --}}
                        <tr class="tfoot-total">
                            <td class="text-end fw-semibold">TOTAL BOBOT KATEGORI</td>
                            @php $sumAllCats = 0; @endphp
                            @foreach($cats as $cat)
                                @php
                                    $w = (float)($catWeights[$cat->id] ?? 0);
                                    $sumAllCats += $w;
                                @endphp
                                <td class="text-end fw-semibold">
                                    {{ $w > 0 ? number_format($w, 2).'%' : '' }}
                                </td>
                            @endforeach
                            <td class="text-end fw-semibold total-col">
                                {{ number_format($sumAllCats, 2) }}%
                            </td>
                        </tr>

                        {{-- DESKRIPSI --}}
                        <tr class="tfoot-desc">
                            <td class="tfoot-label">Deskripsi</td>
                            @foreach($cats as $cat)
                                @php
                                    $descVal = old('desc.'.$cat->id, $catDesc[$cat->id] ?? '');
                                @endphp
                                <td>
                                    <input
                                        type="text"
                                        name="desc[{{ $cat->id }}]"
                                        class="form-control form-control-sm"
                                        value="{{ $descVal }}"
                                        placeholder="cth: Study Case, Group Project, ..."
                                    >
                                </td>
                            @endforeach
                            <td></td>
                        </tr>

                        {{-- DUE DATE --}}
                        <tr class="tfoot-due">
                            <td class="tfoot-label">Due Date</td>
                            @foreach($cats as $cat)
                                @php
                                    $dueVal = old('due_week.'.$cat->id, $catDue[$cat->id] ?? '');
                                @endphp
                                <td>
                                    <input
                                        type="text"
                                        name="due_week[{{ $cat->id }}]"
                                        class="form-control form-control-sm"
                                        value="{{ $dueVal }}"
                                        placeholder="week 3, 5, 7, 10 / week 15–16"
                                    >
                                </td>
                            @endforeach
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between">
            <a href="{{ route('rps.create.step', 2) }}" class="btn btn-outline-secondary">
                ← Kembali ke Step 2
            </a>
            <button type="submit" class="btn btn-primary">
                Simpan & Lanjut ke Step 4
            </button>
        </div>
    </form>
</div>
@endsection
