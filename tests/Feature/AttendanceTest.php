<?php

namespace Tests\Feature;

use App\Models\AttendanceV2;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use DatabaseMigrations;

    public function testCanQueryUserKeyAmount()
    {
        $this->withoutExceptionHandling();

        $user = factory('App\Models\User')->create();

        $this->get("api/v2/discords/$user->discord_id/attendances")->assertStatus(200);
    }

    public function testCanCheckAttendance()
    {
        $this->withoutExceptionHandling();

        $user = factory('App\Models\User')->create();

        $this->post("api/v2/discords/$user->discord_id/attendances")->assertSee('success');
    }

    public function testCannotCheckAttendanceIfAttendanceExists()
    {
        $this->withoutExceptionHandling();

        $user = factory('App\Models\User')->create();
        $this->post("api/v2/discords/$user->discord_id/attendances");
        $this->post("api/v2/discords/$user->discord_id/attendances")->assertSee('exist_attendance');
    }

    public function testCannotCheckAttendanceIfAttendanceHitMax()
    {
        $user = factory('App\Models\User')->create();
        $this->post("api/v2/discords/$user->discord_id/attendances");

        $attendance = AttendanceV2::query()
            ->whereDiscordId($user->discord_id)
            ->firstOrFail();

        $attendance->key_count = 15;
        $attendance->key_acquired_at = Carbon::yesterday();
        $attendance->save();
        $this->post("api/v2/discords/$user->discord_id/attendances")->assertSee('max_key_count');
    }

    public function testCanOpenBox()
    {
        $this->withoutExceptionHandling();

        $user = factory('App\Models\User')->create();
        $this->post("api/v2/discords/$user->discord_id/attendances");

        $attendance = AttendanceV2::query()
            ->whereDiscordId($user->discord_id)
            ->firstOrFail();

        $attendance->key_count = 15;
        $attendance->save();

        $this->post("api/v2/discords/$user->discord_id/attendances/unpack", [
            'box' => 'bronze',
        ])->assertStatus(200);
    }
}
