<?php

namespace App\Trading\Bots\Tests\Reports;

abstract class ReportTest implements IReportTest
{
    protected array $summary = [];

    /**
     * @return array
     */
    public function getSummary(): array
    {
        return $this->summary;
    }
}