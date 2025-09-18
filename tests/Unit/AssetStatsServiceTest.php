<?php

use App\Services\AssetStatsService;
use PHPUnit\Framework\TestCase;

class AssetStatsServiceTest extends TestCase
{
    public function test_empty_array_returns_nulls(): void
    {
        $svc = new AssetStatsService();
        $res = $svc->compute([]);
        $this->assertSame(0, $res['count']);
        $this->assertNull($res['avg']);
        $this->assertNull($res['median']);
        $this->assertNull($res['max']);
        $this->assertNull($res['min']);
    }

    public function test_odd_count_stats(): void
    {
        $svc = new AssetStatsService();
        $res = $svc->compute([10, 2, 6]); // sorted [2,6,10]
        $this->assertSame(3, $res['count']);
        $this->assertEqualsWithDelta((10+2+6)/3, $res['avg'], 0.000001);
        $this->assertSame(6.0, $res['median']);
        $this->assertSame(10.0, $res['max']);
        $this->assertSame(2.0, $res['min']);
    }

    public function test_even_count_stats(): void
    {
        $svc = new AssetStatsService();
        $res = $svc->compute([1, 9, 5, 3]); // sorted [1,3,5,9]
        $this->assertSame(4, $res['count']);
        $this->assertSame(4.0, $res['median']);
        $this->assertSame(9.0, $res['max']);
        $this->assertSame(1.0, $res['min']);
    }

    public function test_ignores_non_numeric_and_empty(): void
    {
        $svc = new AssetStatsService();
        $res = $svc->compute(['', null, 'abc', 4, '7', 9]); // valid: [4,7,9]
        $this->assertSame(3, $res['count']);
        $this->assertSame(7.0, $res['median']);
        $this->assertSame(9.0, $res['max']);
        $this->assertSame(4.0, $res['min']);
    }

    public function test_negative_numbers(): void
    {
        $svc = new AssetStatsService();
        $res = $svc->compute([-5, -1, -3]); // sorted [-5,-3,-1]
        $this->assertSame(3, $res['count']);
        $this->assertSame(-3.0, $res['median']);
        $this->assertSame(-1.0, $res['max']);
        $this->assertSame(-5.0, $res['min']);
    }
}
