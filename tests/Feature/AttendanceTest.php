<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

        $this->post("api/v2/discords/$user->discord_id/attendances")->assertSee("success");
    }


    public function testCannotCheckAttendanceIfAttendanceExists()
    {
        $this->withoutExceptionHandling();

        $user = factory('App\Models\User')->create();

        $this->post("api/v2/discords/$user->discord_id/attendances")->assertSee("success");

        $this->post("api/v2/discords/$user->discord_id/attendances")->assertSee("exist_attendance");
    }


    public function testCanOpenBox()
    {
        
    }
}
