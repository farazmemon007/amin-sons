    @if ($sales && $sales->count() > 0)
        @foreach ($sales as $sale)
            @if (!$sale)
                @continue
            @endif
            <tr>
                <td>{{ $sale->id }}</td>
                @if(Auth::check() && Auth::user()->hasRole('super admin'))
                    <td>{{ optional($sale->branch)->name ?? optional(optional($sale->customer)->branch)->name ?? 'N/A' }}</td>
                @endif
                <td>{{ $sale->invoice_no }}</td>
                <td>{{ optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'N/A' }}</td>
                <td>{{ optional($sale->customer)->mobile ?? ($sale->tel ?? 'N/A') }}</td>

                <td>
                    @if ($sale->saleItems && $sale->saleItems->count() > 0)
                        @foreach ($sale->saleItems as $item)
                            <div>{{ optional($item->product)->item_name ?? 'N/A' }}</div>
                        @endforeach
                    @else
                        N/A
                    @endif
                </td>

                <td>
                    @if ($sale->saleItems && $sale->saleItems->count() > 0)
                        @foreach ($sale->saleItems as $item)
                            <div>{{ optional($item->product)->model ?? '-' }}</div>
                        @endforeach
                    @else
                        -
                    @endif
                </td>

                <td>
                    @if ($sale->saleItems && $sale->saleItems->count() > 0)
                        @foreach ($sale->saleItems as $item)
                            <div>{{ (int) $item->sales_qty == $item->sales_qty ? (int) $item->sales_qty : rtrim(rtrim(number_format($item->sales_qty, 2, '.', ''), '0'), '.') }}</div>
                        @endforeach
                    @else
                        0
                    @endif
                </td>

                <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d-m-Y') }}</td>

                <td class="text-center">
                    <a href="{{ route('sale.dc', $sale->id) }}" class="btn btn-sm btn-success">DC</a>
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="10" class="text-center text-danger fw-bold">No Invoice Found According To Given Number</td>
        </tr>
    @endif