<?php

namespace Tests\Unit;

use App\Models\RequestLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class CleanLogControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function it_returns_correct_yesterdays_log()
    {
        $data = [
            'duration' => '1.0',
            'url' => 'https://crsd.team',
            'method' => 'POST',
            'ip' => '1.1.1.1',
            'request' => 'test',
            'response' => 'test',
        ];

        RequestLog::create(
            $data + ['created_at' => Carbon::today()->subDays(7)->getTimestamp()]
        );
        RequestLog::create($data);

        $controller = App::make('App\Http\Controllers\CleanLogController');

        $logs = $controller->getYesterdaysLogs();

        $this->assertSame([$data], $logs);
    }
}
