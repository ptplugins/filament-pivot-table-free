<?php

namespace PtPlugins\FilamentPivotTableFree\Builders;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;

/**
 * Pivot data builder. Groups records by row and column dimensions and sums the
 * value column.
 *
 * Free version: max 2 row × 2 column dimensions, Sum aggregation only. Throws
 * when limits are exceeded — link to Pro upgrade in the message.
 */
class PivotBuilder
{
    public const FREE_LIMIT_DIMENSIONS = 2;

    public const UPGRADE_URL = 'https://ptplugins.com/filament-pivot-table';

    /** @var array<int, string> */
    private array $rowFields;

    /** @var array<int, string> */
    private array $columnFields;

    private string $valueField;

    /**
     * @param  EloquentBuilder|Collection|iterable<mixed>  $source
     * @param  array<int, string>  $rowFields
     * @param  array<int, string>  $columnFields
     */
    public function __construct(
        private EloquentBuilder|Collection|iterable $source,
        array $rowFields,
        array $columnFields,
        string $valueField,
    ) {
        $this->rowFields = array_values($rowFields);
        $this->columnFields = array_values($columnFields);
        $this->valueField = $valueField;

        $this->guardLimits();
    }

    /**
     * Build the pivot. Returns:
     *   - rowKeys:    list of row tuples (each = list of values, length = count(rowFields))
     *   - colKeys:    list of column tuples
     *   - cells:      [rowKeyJson][colKeyJson] => sum
     *   - rowFields, columnFields, valueField (echoed back for the view)
     *
     * @return array{
     *     rowFields: array<int, string>,
     *     columnFields: array<int, string>,
     *     valueField: string,
     *     rowKeys: array<int, array<int, mixed>>,
     *     colKeys: array<int, array<int, mixed>>,
     *     cells: array<string, array<string, float>>,
     * }
     */
    public function build(): array
    {
        $records = $this->source instanceof EloquentBuilder
            ? $this->source->get()
            : Collection::make($this->source);

        $rowSet = [];
        $colSet = [];
        $cells = [];

        foreach ($records as $record) {
            $rowKey = array_map(fn ($f) => data_get($record, $f), $this->rowFields);
            $colKey = array_map(fn ($f) => data_get($record, $f), $this->columnFields);
            $value = (float) data_get($record, $this->valueField, 0);

            $rowKeyJson = json_encode($rowKey);
            $colKeyJson = json_encode($colKey);

            $rowSet[$rowKeyJson] = $rowKey;
            $colSet[$colKeyJson] = $colKey;

            $cells[$rowKeyJson][$colKeyJson] = ($cells[$rowKeyJson][$colKeyJson] ?? 0.0) + $value;
        }

        // Preserve insertion order — caller controls ordering via Eloquent ->orderBy().
        $rowKeys = array_values($rowSet);
        $colKeys = array_values($colSet);

        return [
            'rowFields' => $this->rowFields,
            'columnFields' => $this->columnFields,
            'valueField' => $this->valueField,
            'rowKeys' => $rowKeys,
            'colKeys' => $colKeys,
            'cells' => $cells,
        ];
    }

    private function guardLimits(): void
    {
        if (count($this->rowFields) === 0 || count($this->columnFields) === 0) {
            throw new \InvalidArgumentException(
                'PivotBuilder requires at least one row field and one column field.'
            );
        }

        if (count($this->rowFields) > self::FREE_LIMIT_DIMENSIONS) {
            throw new \OutOfBoundsException(sprintf(
                'Free version supports max %d row dimensions (got %d). Upgrade to Pro for unlimited dimensions: %s',
                self::FREE_LIMIT_DIMENSIONS,
                count($this->rowFields),
                self::UPGRADE_URL,
            ));
        }

        if (count($this->columnFields) > self::FREE_LIMIT_DIMENSIONS) {
            throw new \OutOfBoundsException(sprintf(
                'Free version supports max %d column dimensions (got %d). Upgrade to Pro for unlimited dimensions: %s',
                self::FREE_LIMIT_DIMENSIONS,
                count($this->columnFields),
                self::UPGRADE_URL,
            ));
        }
    }
}
