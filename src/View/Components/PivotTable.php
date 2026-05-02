<?php

namespace PtPlugins\FilamentPivotTableFree\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\View\Component;
use PtPlugins\FilamentPivotTableFree\Builders\PivotBuilder;

class PivotTable extends Component
{
    /** @var array<int, string> */
    public array $resolvedRowFields;

    /** @var array<int, string> */
    public array $resolvedColumnFields;

    public array $pivot;

    /**
     * @param  iterable<mixed>|null  $records  Collection or array of Eloquent models / array rows.
     * @param  EloquentBuilder|null  $query  Eloquent query (alternative to $records).
     * @param  string|null  $rowField  Single row dimension (use this OR rowFields).
     * @param  array<int, string>|null  $rowFields  Up to 2 row dimensions (Free limit).
     * @param  string|null  $columnField  Single column dimension.
     * @param  array<int, string>|null  $columnFields  Up to 2 column dimensions.
     * @param  string  $valueField  Field to sum.
     * @param  string|null  $title  Optional heading above the table.
     * @param  string|null  $emptyText  Message when no records.
     */
    public function __construct(
        public ?iterable $records = null,
        public ?EloquentBuilder $query = null,
        ?string $rowField = null,
        ?array $rowFields = null,
        ?string $columnField = null,
        ?array $columnFields = null,
        public string $valueField = 'value',
        public ?string $title = null,
        public ?string $emptyText = null,
    ) {
        if (($records === null) === ($query === null)) {
            throw new \InvalidArgumentException(
                'Provide either :records OR :query (exactly one).'
            );
        }

        $this->resolvedRowFields = $this->normalizeFields($rowField, $rowFields, 'row');
        $this->resolvedColumnFields = $this->normalizeFields($columnField, $columnFields, 'column');

        $this->pivot = (new PivotBuilder(
            $records ?? $query,
            $this->resolvedRowFields,
            $this->resolvedColumnFields,
            $valueField,
        ))->build();
    }

    public function render(): View
    {
        return view('pivot-free::components.pivot-table');
    }

    /**
     * @param  array<int, string>|null  $array
     * @return array<int, string>
     */
    private function normalizeFields(?string $single, ?array $array, string $axis): array
    {
        if ($single !== null && $array !== null) {
            throw new \InvalidArgumentException(sprintf(
                'Provide either %1$sField OR %1$sFields, not both.',
                $axis,
            ));
        }

        if ($single !== null) {
            return [$single];
        }

        if ($array !== null) {
            return array_values($array);
        }

        throw new \InvalidArgumentException(sprintf(
            'Missing %1$sField or %1$sFields prop.',
            $axis,
        ));
    }
}
