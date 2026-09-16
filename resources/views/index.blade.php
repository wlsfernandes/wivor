@extends('layouts.master')

@section('title') Relatorio de eventos @endsection

@section('content')
    @component('common-components.breadcrumb')
        @slot('pagetitle') Wivor @endslot
        @slot('title') Relatorio de eventos @endslot
    @endcomponent

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dashboard') }}">
                <div class="row align-items-end g-3">
                    <div class="col-lg-8">
                        <label class="form-label" for="event_id">Evento</label>
                        <select class="form-select" id="event_id" name="event_id">
                            <option value="">Todos os eventos</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}" @selected($selectedEvent?->is($event))>
                                    {{ $event->title }}{{ $event->date_of_event ? ' - '.$event->date_of_event->format('d/m/Y') : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="uil uil-filter me-1"></i> Aplicar
                        </button>
                    </div>
                    @if ($selectedEvent)
                        <div class="col-lg-2">
                            <a class="btn btn-light w-100" href="{{ route('admin.dashboard') }}">Limpar</a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1">{{ $selectedEvent?->title ?? 'Todos os eventos' }}</h2>
            <p class="text-muted mb-0">Dados consolidados de uploads e vendas registrados pela plataforma.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-xl-4">
            <div class="card"><div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div><p class="text-muted mb-1">Fotos enviadas</p><h3 class="mb-0" data-plugin="counterup">{{ number_format($summary['uploadedPhotos']) }}</h3></div>
                    <span class="avatar-sm rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center"><i class="uil uil-upload text-primary font-size-24"></i></span>
                </div>
                <p class="text-muted mt-3 mb-0">{{ number_format($summary['publishedPhotos']) }} publicadas</p>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card"><div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div><p class="text-muted mb-1">Fotos vendidas</p><h3 class="mb-0" data-plugin="counterup">{{ number_format($summary['soldPhotos']) }}</h3></div>
                    <span class="avatar-sm rounded-circle bg-success-subtle d-flex align-items-center justify-content-center"><i class="uil uil-images text-success font-size-24"></i></span>
                </div>
                <p class="text-muted mt-3 mb-0">Itens de pedidos pagos</p>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card"><div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div><p class="text-muted mb-1">Pedidos pagos</p><h3 class="mb-0" data-plugin="counterup">{{ number_format($summary['orders']) }}</h3></div>
                    <span class="avatar-sm rounded-circle bg-info-subtle d-flex align-items-center justify-content-center"><i class="uil uil-shopping-bag text-info font-size-24"></i></span>
                </div>
                <p class="text-muted mt-3 mb-0">{{ number_format($summary['checkoutCount']) }} checkouts iniciados</p>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card"><div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div><p class="text-muted mb-1">Compradores unicos</p><h3 class="mb-0" data-plugin="counterup">{{ number_format($summary['uniqueBuyers']) }}</h3></div>
                    <span class="avatar-sm rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center"><i class="uil uil-users-alt text-warning font-size-24"></i></span>
                </div>
                <p class="text-muted mt-3 mb-0">E-mails distintos em pedidos pagos</p>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card"><div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div><p class="text-muted mb-1">Vendas / GMV</p><h3 class="mb-0">${{ number_format($summary['gmvCents'] / 100, 2) }}</h3></div>
                    <span class="avatar-sm rounded-circle bg-success-subtle d-flex align-items-center justify-content-center"><i class="uil uil-dollar-sign text-success font-size-24"></i></span>
                </div>
                <p class="text-muted mt-3 mb-0">Valor bruto dos pedidos pagos</p>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card"><div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div><p class="text-muted mb-1">Conversao checkout para compra</p><h3 class="mb-0">{{ $summary['checkoutConversion'] !== null ? number_format($summary['checkoutConversion'], 1).'%' : 'N/D' }}</h3></div>
                    <span class="avatar-sm rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center"><i class="uil uil-chart-growth text-danger font-size-24"></i></span>
                </div>
                <p class="text-muted mt-3 mb-0">{{ number_format($summary['nonPurchasedCheckoutCount']) }} sem compra registrada</p>
            </div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card"><div class="card-body">
                <h4 class="card-title mb-1">Vendas ao longo do tempo</h4>
                <p class="text-muted">Pedidos pagos agrupados por dia.</p>
                @if ($salesTimeline['labels'] === [])
                    <div class="text-center text-muted py-5">Nenhuma venda paga no periodo selecionado.</div>
                @else
                    <div id="event-sales-chart" data-colors='["--bs-primary", "--bs-success"]' class="apex-charts" dir="ltr"></div>
                @endif
            </div></div>
        </div>
        <div class="col-xl-4">
            <div class="card"><div class="card-body">
                <h4 class="card-title mb-3">Dados ainda nao rastreados</h4>
                <div class="alert alert-light border mb-3" role="status"><strong>Acessos a pagina do evento</strong><div class="text-muted mt-1">Nao ha registro persistente de visitas.</div></div>
                <div class="alert alert-light border mb-3" role="status"><strong>Buscas faciais e resultados</strong><div class="text-muted mt-1">As buscas aparecem apenas no log tecnico e nao podem ser agregadas com seguranca.</div></div>
                <div class="alert alert-light border mb-0" role="status"><strong>Conversao acesso para busca para compra</strong><div class="text-muted mt-1">Depende do rastreamento de acessos e buscas acima.</div></div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title mb-1">Desempenho por fotografo</h4>
            <p class="text-muted">Uploads, publicacoes e vendas atribuidos a cada fotografo.</p>
            <div class="table-responsive">
                <table class="table table-centered table-nowrap mb-0">
                    <thead class="table-light"><tr><th>Fotografo</th><th class="text-end">Enviadas</th><th class="text-end">Publicadas</th><th class="text-end">Vendidas</th><th class="text-end">GMV</th></tr></thead>
                    <tbody>
                        @forelse ($photographerRows as $photographerRow)
                            <tr>
                                <td class="fw-semibold">{{ $photographerRow['name'] }}</td>
                                <td class="text-end">{{ number_format($photographerRow['uploadedPhotos']) }}</td>
                                <td class="text-end">{{ number_format($photographerRow['publishedPhotos']) }}</td>
                                <td class="text-end">{{ number_format($photographerRow['soldPhotos']) }}</td>
                                <td class="text-end">${{ number_format($photographerRow['gmvCents'] / 100, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-4" colspan="5">Nenhum fotografo com atividade neste filtro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    @if ($salesTimeline['labels'] !== [])
        <script>
            (function () {
                var chartElement = document.getElementById('event-sales-chart');
                var colors = JSON.parse(chartElement.getAttribute('data-colors')).map(function (color) {
                    return getComputedStyle(document.documentElement).getPropertyValue(color).trim() || color;
                });

                new ApexCharts(chartElement, {
                    chart: { height: 340, type: 'line', toolbar: { show: false } },
                    colors: colors,
                    series: [
                        { name: 'GMV ($)', type: 'column', data: @json($salesTimeline['gmv']) },
                        { name: 'Pedidos', type: 'line', data: @json($salesTimeline['orders']) }
                    ],
                    labels: @json($salesTimeline['labels']),
                    stroke: { width: [0, 3], curve: 'smooth' },
                    plotOptions: { bar: { columnWidth: '45%', borderRadius: 3 } },
                    xaxis: { type: 'datetime' },
                    yaxis: [
                        { title: { text: 'GMV ($)' }, labels: { formatter: function (value) { return '$' + value.toFixed(0); } } },
                        { opposite: true, title: { text: 'Pedidos' }, decimalsInFloat: 0 }
                    ],
                    tooltip: { shared: true, intersect: false },
                    grid: { borderColor: '#f1f1f1' }
                }).render();
            })();
        </script>
    @endif
@endsection