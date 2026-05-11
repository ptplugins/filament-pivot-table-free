<?php

namespace PtPlugins\FilamentPivotTableFree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PtPlugins\FilamentPivotTableFree\View\Components\PivotTable;

class PivotTableComponentTest extends TestCase
{
    public function test_normalizes_single_row_field_to_array(): void
    {
        $c = new PivotTable(
            records: [['a' => 'r1', 'b' => 'c1', 'v' => 10]],
            rowField: 'a',
            columnField: 'b',
            valueField: 'v',
        );

        $this->assertSame(['a'], $c->resolvedRowFields);
        $this->assertSame(['b'], $c->resolvedColumnFields);
    }

    public function test_normalizes_multi_row_fields_array(): void
    {
        $c = new PivotTable(
            records: [['a' => 'r1', 'b' => 'r2', 'c' => 'col1', 'v' => 10]],
            rowFields: ['a', 'b'],
            columnField: 'c',
            valueField: 'v',
        );

        $this->assertSame(['a', 'b'], $c->resolvedRowFields);
    }

    public function test_throws_when_both_row_field_and_row_fields_given(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('rowField OR rowFields');

        new PivotTable(
            records: [],
            rowField: 'a',
            rowFields: ['a', 'b'],
            columnField: 'c',
            valueField: 'v',
        );
    }

    public function test_throws_when_neither_row_prop_given(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('rowField');

        new PivotTable(
            records: [],
            columnField: 'c',
            valueField: 'v',
        );
    }

    public function test_throws_when_both_records_and_query_given(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(':records OR :query');

        // Both non-null: records=[], and a fake EloquentBuilder won't be needed for the
        // mutual-exclusion guard — pass any non-null Builder via mock-free shortcut.
        $builder = $this->getMockBuilder(\Illuminate\Database\Eloquent\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        new PivotTable(
            records: [],
            query: $builder,
            rowField: 'a',
            columnField: 'b',
            valueField: 'v',
        );
    }

    public function test_throws_when_neither_records_nor_query_given(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(':records OR :query');

        new PivotTable(
            rowField: 'a',
            columnField: 'b',
            valueField: 'v',
        );
    }

    public function test_pivot_property_is_built_at_construct_time(): void
    {
        $c = new PivotTable(
            records: [
                ['a' => 'r1', 'b' => 'c1', 'v' => 5],
                ['a' => 'r1', 'b' => 'c1', 'v' => 7],
            ],
            rowField: 'a',
            columnField: 'b',
            valueField: 'v',
        );

        $this->assertEqualsWithDelta(12.0, $c->pivot['cells']['["r1"]']['["c1"]'], 0.001);
    }
}
