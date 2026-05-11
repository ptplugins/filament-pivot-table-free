<?php

namespace PtPlugins\FilamentPivotTableFree\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PtPlugins\FilamentPivotTableFree\Builders\PivotBuilder;

class PivotBuilderTest extends TestCase
{
    public function test_one_by_one_pivot_sums_values(): void
    {
        $records = [
            ['region' => 'West', 'month' => 'Jan', 'amount' => 100],
            ['region' => 'West', 'month' => 'Jan', 'amount' => 50], // same cell, must sum
            ['region' => 'West', 'month' => 'Feb', 'amount' => 200],
            ['region' => 'East', 'month' => 'Jan', 'amount' => 150],
        ];

        $result = (new PivotBuilder($records, ['region'], ['month'], 'amount'))->build();

        $this->assertSame([['West'], ['East']], $result['rowKeys'], 'Insertion order preserved');
        $this->assertSame([['Jan'], ['Feb']], $result['colKeys']);
        $this->assertEqualsWithDelta(150.0, $result['cells']['["West"]']['["Jan"]'], 0.001);
        $this->assertEqualsWithDelta(200.0, $result['cells']['["West"]']['["Feb"]'], 0.001);
        $this->assertEqualsWithDelta(150.0, $result['cells']['["East"]']['["Jan"]'], 0.001);
    }

    public function test_two_by_two_pivot_groups_correctly(): void
    {
        $records = [
            ['r' => 'W', 'c' => 'Cl', 'y' => 2026, 'q' => 'Q1', 'v' => 100],
            ['r' => 'W', 'c' => 'El', 'y' => 2026, 'q' => 'Q1', 'v' => 200],
            ['r' => 'E', 'c' => 'Cl', 'y' => 2025, 'q' => 'Q4', 'v' => 50],
        ];

        $result = (new PivotBuilder($records, ['r', 'c'], ['y', 'q'], 'v'))->build();

        $this->assertCount(3, $result['rowKeys']);
        $this->assertCount(2, $result['colKeys']);
        $this->assertSame(['W', 'Cl'], $result['rowKeys'][0]);
        $this->assertEqualsWithDelta(100.0, $result['cells']['["W","Cl"]']['[2026,"Q1"]'], 0.001);
    }

    public function test_throws_on_three_row_dimensions_with_upgrade_url(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessageMatches('/Free version supports max 2 row dimensions \(got 3\)\..*ptplugins\.com/');

        new PivotBuilder([], ['a', 'b', 'c'], ['x'], 'v');
    }

    public function test_throws_on_three_column_dimensions_with_upgrade_url(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessageMatches('/Free version supports max 2 column dimensions \(got 4\)\..*ptplugins\.com/');

        new PivotBuilder([], ['a'], ['x', 'y', 'z', 'w'], 'v');
    }

    public function test_throws_on_zero_row_fields(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PivotBuilder([], [], ['x'], 'v');
    }

    public function test_throws_on_zero_column_fields(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PivotBuilder([], ['a'], [], 'v');
    }

    public function test_empty_source_yields_empty_pivot(): void
    {
        $result = (new PivotBuilder([], ['region'], ['month'], 'amount'))->build();

        $this->assertSame([], $result['rowKeys']);
        $this->assertSame([], $result['colKeys']);
        $this->assertSame([], $result['cells']);
    }

    public function test_missing_value_field_treats_as_zero(): void
    {
        $records = [
            ['region' => 'West', 'month' => 'Jan'], // no amount field
            ['region' => 'West', 'month' => 'Jan', 'amount' => 100],
        ];

        $result = (new PivotBuilder($records, ['region'], ['month'], 'amount'))->build();

        $this->assertEqualsWithDelta(100.0, $result['cells']['["West"]']['["Jan"]'], 0.001);
    }

    public function test_data_get_walks_object_properties(): void
    {
        $records = [
            (object) ['region' => 'West', 'month' => 'Jan', 'amount' => 100],
            (object) ['region' => 'East', 'month' => 'Feb', 'amount' => 200],
        ];

        $result = (new PivotBuilder($records, ['region'], ['month'], 'amount'))->build();

        $this->assertEqualsWithDelta(100.0, $result['cells']['["West"]']['["Jan"]'], 0.001);
        $this->assertEqualsWithDelta(200.0, $result['cells']['["East"]']['["Feb"]'], 0.001);
    }

    public function test_preserves_insertion_order_not_alphabetical(): void
    {
        // Months arrive Mar, Jan, Feb — must NOT be sorted alphabetically.
        $records = [
            ['region' => 'A', 'month' => 'Mar', 'amount' => 1],
            ['region' => 'A', 'month' => 'Jan', 'amount' => 2],
            ['region' => 'A', 'month' => 'Feb', 'amount' => 3],
        ];

        $result = (new PivotBuilder($records, ['region'], ['month'], 'amount'))->build();

        $this->assertSame([['Mar'], ['Jan'], ['Feb']], $result['colKeys']);
    }

    public function test_two_dimensions_at_limit_works(): void
    {
        // Exactly 2 dims on each axis — must NOT throw.
        $records = [['a' => 1, 'b' => 2, 'x' => 3, 'y' => 4, 'v' => 10]];

        $result = (new PivotBuilder($records, ['a', 'b'], ['x', 'y'], 'v'))->build();

        $this->assertCount(1, $result['rowKeys']);
        $this->assertCount(1, $result['colKeys']);
    }
}
