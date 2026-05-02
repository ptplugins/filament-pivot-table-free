@php
    $rowFields = $resolvedRowFields;
    $colFields = $resolvedColumnFields;
    $rowKeys = $pivot['rowKeys'];
    $colKeys = $pivot['colKeys'];
    $cells = $pivot['cells'];
    $isMultiRow = count($rowFields) > 1;
    $isMultiCol = count($colFields) > 1;

    $colGroups = [];
    if ($isMultiCol) {
        foreach ($colKeys as $colKey) {
            $first = $colKey[0];
            $colGroups[$first] ??= ['label' => $first, 'count' => 0];
            $colGroups[$first]['count']++;
        }
        $colGroups = array_values($colGroups);
    }

    $rowGroupRunStart = [];
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
    $emptyMessage = $emptyText ?? __('pivot-free::messages.empty');
@endphp

<div class="pivot-free-wrapper">
    @if ($title)
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 px-1">{{ $title }}</h3>
    @endif

    @if (! $hasData)
        <div class="p-8 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg">
            {{ $emptyMessage }}
        </div>
    @else
        <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    @if ($isMultiCol)
                        <tr>
                            <th colspan="{{ count($rowFields) }}" rowspan="2" class="px-3.5 py-2.5 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 border-b border-r border-gray-200 dark:border-gray-700">
                                {{ implode(' / ', $rowFields) }}
                            </th>
                            @foreach ($colGroups as $group)
                                <th colspan="{{ $group['count'] }}" class="px-3.5 py-2 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 border-b border-r border-gray-200 dark:border-gray-700">
                                    {{ $group['label'] }}
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($colKeys as $colKey)
                                <th class="px-3.5 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 border-b border-r border-gray-200 dark:border-gray-700">
                                    {{ $colKey[1] }}
                                </th>
                            @endforeach
                        </tr>
                    @else
                        <tr>
                            <th colspan="{{ count($rowFields) }}" class="px-3.5 py-2.5 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 border-b border-r border-gray-200 dark:border-gray-700">
                                {{ implode(' / ', $rowFields) }}
                            </th>
                            @foreach ($colKeys as $colKey)
                                <th class="px-3.5 py-2.5 text-right text-sm font-semibold text-gray-700 dark:text-gray-200 border-b border-r border-gray-200 dark:border-gray-700">
                                    {{ $colKey[0] }}
                                </th>
                            @endforeach
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @foreach ($rowKeys as $i => $rowKey)
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            @if ($isMultiRow)
                                @if (isset($rowGroupRunStart[$i]))
                                    <td rowspan="{{ $rowGroupRunStart[$i] }}" class="px-3.5 py-2.5 font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800/50 align-top border-r border-gray-200 dark:border-gray-700">
                                        {{ $rowKey[0] }}
                                    </td>
                                @endif
                                <td class="px-3.5 py-2.5 text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">
                                    {{ $rowKey[1] }}
                                </td>
                            @else
                                <td class="px-3.5 py-2.5 font-medium text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700">
                                    {{ $rowKey[0] }}
                                </td>
                            @endif

                            @foreach ($colKeys as $colKey)
                                @php
                                    $rk = json_encode($rowKey);
                                    $ck = json_encode($colKey);
                                    $value = $cells[$rk][$ck] ?? null;
                                @endphp
                                <td class="px-3.5 py-2.5 text-right tabular-nums border-r border-gray-100 dark:border-gray-800 {{ $value === null ? 'text-gray-300 dark:text-gray-600' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ $value === null ? '—' : $fmt($value) }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800/50 border-t-2 border-gray-200 dark:border-gray-700">
                    <tr>
                        <td colspan="{{ count($rowFields) }}" class="px-3.5 py-2.5 font-bold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700">
                            {{ __('pivot-free::messages.grand_total') }}
                        </td>
                        <td colspan="{{ count($colKeys) }}" class="px-3.5 py-2.5 text-right font-bold text-gray-900 dark:text-white tabular-nums">
                            {{ $fmt($grandTotal) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-2 text-xs text-gray-400 dark:text-gray-500 text-right px-1">
            {!! __('pivot-free::messages.powered_by', [
                'brand' => '<a href="https://ptplugins.com/filament-pivot-table" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-300">PtPlugins</a>',
            ]) !!}
            &middot;
            <a href="https://ptplugins.com/filament-pivot-table" class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-300">{{ __('pivot-free::messages.upgrade_cta') }}</a>
        </div>
    @endif
</div>
