<?php

namespace App\Trading\Bots\Tests\Reports;

use App\Trading\Bots\Tests\Data\ResultTest;

interface IReportTest
{
    public function getSummary(): array;

    public function report(ResultTest $result): string;
}