<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\App;
use Tests\TestCase;

class BackupServiceTest extends TestCase
{
    /** @test **/
    public function test_get_year()
    {
        $date = '2020-07-18';

        $service = App::make('App\Services\BackupService');
        $this->assertEquals('2020', $service->getYear($date));
    }

    /** @test **/
    public function test_get_month()
    {
        $date = '2020-07-18';

        $service = App::make('App\Services\BackupService');
        $this->assertEquals('07', $service->getMonth($date));
    }
}
