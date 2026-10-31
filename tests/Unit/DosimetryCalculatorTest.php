<?php

namespace Tests\Unit;

use App\Services\DosimetryCalculator;
use PHPUnit\Framework\TestCase;

class DosimetryCalculatorTest extends TestCase
{
    public function test_deterministic_dosimetry_calculation_across_zones(): void
    {
        $calculator = new DosimetryCalculator;

        $resting = $calculator->calculate(50.0, 60, 0, 45.0, 70.0);
        $tempo = $calculator->calculate(50.0, 60, 3, 45.0, 70.0);
        $vo2max = $calculator->calculate(50.0, 60, 5, 45.0, 70.0);

        $this->assertSame(390, $resting['total_air_liters']);
        $this->assertSame(3960, $tempo['total_air_liters']);
        $this->assertGreaterThan($resting['inhaled_dose_ug'], $tempo['inhaled_dose_ug']);
        $this->assertGreaterThan($tempo['inhaled_dose_ug'], $vo2max['inhaled_dose_ug']);
        $this->assertGreaterThan(10.0, $tempo['exertion_multiplier']);
    }

    public function test_pm25_to_epa_and_defra_indices(): void
    {
        $calculator = new DosimetryCalculator;

        $this->assertSame(1, $calculator->pm25ToEpaIndex(10.0));
        $this->assertSame(2, $calculator->pm25ToEpaIndex(22.1));
        $this->assertSame(4, $calculator->pm25ToEpaIndex(58.4));

        $this->assertSame(1, $calculator->pm25ToDefraIndex(10.0));
        $this->assertSame(2, $calculator->pm25ToDefraIndex(22.1));
        $this->assertSame(8, $calculator->pm25ToDefraIndex(58.4));
    }
}
