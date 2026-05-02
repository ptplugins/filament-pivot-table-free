@php
    $rowFields = $resolvedRowFields;
    $colFields = $resolvedColumnFields;
    $rowKeys = $pivot['rowKeys'];
    $colKeys = $pivot['colKeys'];
    $cells = $pivot['cells'];
    $isMultiRow = count($rowFields) > 1;
    $isMultiCol = count($colFields) > 1;

    // Group leaf columns by first-dimension value (only when 2 col dims)
    $colGroups = [];
    if ($isMultiCol) {
        foreach ($colKeys as $colKey) {
            $first = $colKey[0];
            $colGroups[$first] ??= ['label' => $first, 'count' => 0];
            $colGroups[$first]['count']++;
        }
        $colGroups = array_values($colGroups);
    }

    // Compute row "groups" — for the leftmost row dimension we render a rowspan cell.
    $rowGroupRunStart = []; // rowIndex => groupCount  (only at the first row of a group)
    if ($isMultiRow) {
        $current = null;
        $startIdx = 0;
        foreach ($rowKeys as $i => $rowKey) {
            if ($rowKey[0] !== $current) {
                if ($current !== null) {
                    $rowGroupRunStart[$startIdx] = $i - $startIdx;
                }
                $current = $rowKey[0];
                $startIdx = $i;
            }
        }
        if ($current !== null) {
            $rowGroupRunStart[$startIdx] = count($rowKeys) - $startIdx;
        }
    }

    $grandTotal = 0.0;
    foreach ($cells as $rowCells) {
        foreach ($rowCells as $value) {
            $grandTotal += $value;
        }
    }

    $fmt = fn ($v) => number_format((float) $v, 2);
    $hasData = ! empty($rowKeys) && ! empty($colKeys);
@endphp

<div class="pivot-free-wrapper" style="font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;">
    @if ($title)
        <h3 class="pivot-free-title" style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0 0 0.75rem; padding: 0 0.25rem;">{{ $title }}</h3>
    @endif

    @if (! $hasData)
        <div class="pivot-free-empty" style="padding: 2rem; text-align: center; color: #6b7280; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem;">
            {{ $emptyText }}
        </div>
    @else
        <div class="pivot-free-table-scroll" style="overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 0.5rem; background: #ffffff;">
            <table class="pivot-free-table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead style="background: #f9fafb;">
                    @if ($isMultiCol)
                        <tr>
                            <th colspan="{{ count($rowFields) }}" rowspan="2" style="padding: 0.625rem 0.875rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;">
                                {{ implode(' / ', $rowFields) }}
                            </th>
                            @foreach ($colGroups as $group)
                                <th colspan="{{ $group['count'] }}" style="padding: 0.5rem 0.875rem; text-align: center; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;">
                                    {{ $group['label'] }}
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($colKeys as $colKey)
                                <th style="padding: 0.5rem 0.875rem; text-align: right; font-weight: 500; color: #6b7280; border-bottom: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;">
                                    {{ $colKey[1] }}
                                </th>
                            @endforeach
                        </tr>
                    @else
                        <tr>
                            <th colspan="{{ count($rowFields) }}" style="padding: 0.625rem 0.875rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;">
                                {{ implode(' / ', $rowFields) }}
                            </th>
                            @foreach ($colKeys as $colKey)
                                <th style="padding: 0.625rem 0.875rem; text-align: right; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;">
                                    {{ $colKey[0] }}
                                </th>
                            @endforeach
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @foreach ($rowKeys as $i => $rowKey)
                        <tr style="border-top: 1px solid #f3f4f6;">
                            @if ($isMultiRow)
                                @if (isset($rowGroupRunStart[$i]))
                                    <td rowspan="{{ $rowGroupRunStart[$i] }}" style="padding: 0.625rem 0.875rem; font-weight: 600; color: #111827; background: #f9fafb; vertical-align: top; border-right: 1px solid #e5e7eb;">
                                        {{ $rowKey[0] }}
                                    </td>
                                @endif
                                <td style="padding: 0.625rem 0.875rem; color: #374151; border-right: 1px solid #e5e7eb;">
                                    {{ $rowKey[1] }}
                                </td>
                            @else
                                <td style="padding: 0.625rem 0.875rem; font-weight: 500; color: #111827; border-right: 1px solid #e5e7eb;">
                                    {{ $rowKey[0] }}
                                </td>
                            @endif

                            @foreach ($colKeys as $colKey)
                                @php
                                    $rk = json_encode($rowKey);
                                    $ck = json_encode($colKey);
                                    $value = $cells[$rk][$ck] ?? null;
                                @endphp
                                <td style="padding: 0.625rem 0.875rem; text-align: right; color: {{ $value === null ? '#d1d5db' : '#111827' }}; font-variant-numeric: tabular-nums; border-right: 1px solid #f3f4f6;">
                                    {{ $value === null ? '—' : $fmt($value) }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="background: #f9fafb;">
                    <tr style="border-top: 2px solid #e5e7eb;">
                        <td colspan="{{ count($rowFields) }}" style="padding: 0.625rem 0.875rem; font-weight: 700; color: #111827; border-right: 1px solid #e5e7eb;">
                            Grand Total
                        </td>
                        <td colspan="{{ count($colKeys) }}" style="padding: 0.625rem 0.875rem; text-align: right; font-weight: 700; color: #111827; font-variant-numeric: tabular-nums;">
                            {{ $fmt($grandTotal) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="pivot-free-footer" style="margin-top: 0.5rem; font-size: 0.75rem; color: #9ca3af; text-align: right;">
            Powered by <a href="https://ptplugins.com/filament-pivot-table" style="color: #6366f1; text-decoration: none;">PtPlugins</a>
            &middot;
            <a href="https://ptplugins.com/filament-pivot-table" style="color: #6366f1; text-decoration: none;">Need heatmap, drill-down, exports? Upgrade →</a>
        </div>
    @endif
</div>
