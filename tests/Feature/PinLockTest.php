<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Services\PinSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PinLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_page_shows_when_no_pin(): void
    {
        $this->get('/pin/setup')->assertOk()->assertSee('Buat PIN CatatCash');
    }

    public function test_setup_stores_hash_and_opens_app(): void
    {
        $this->post('/pin/setup', ['pin' => '123456', 'pin_confirmation' => '123456'])
            ->assertRedirect(route('dashboard.index'));

        $settings = AppSetting::current();
        $this->assertNotSame('123456', $settings->pin_hash);
        $this->assertTrue(Hash::check('123456', $settings->pin_hash));
        $this->get('/')->assertOk();
    }

    public function test_setup_rejects_mismatched_confirmation(): void
    {
        $this->from('/pin/setup')
            ->post('/pin/setup', ['pin' => '123456', 'pin_confirmation' => '654321'])
            ->assertRedirect('/pin/setup')->assertSessionHasErrors('pin');

        $this->assertNull(AppSetting::current()->pin_hash);
    }

    public function test_lock_then_unlock_with_correct_pin(): void
    {
        app(PinSecurityService::class)->setPin('123456');

        $this->get('/')->assertRedirect(route('pin.unlock'));
        $this->post('/pin/unlock', ['pin' => '123456'])->assertRedirect(route('dashboard.index'));
        $this->get('/')->assertOk();
        $this->post('/pin/lock')->assertRedirect(route('pin.unlock'));
        $this->get('/')->assertRedirect(route('pin.unlock'));
    }

    public function test_lockout_after_five_wrong_attempts_and_rejects_correct_pin(): void
    {
        app(PinSecurityService::class)->setPin('123456');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/pin/unlock', ['pin' => '000000'])->assertSessionHasErrors('pin');
        }

        $settings = AppSetting::current();
        $this->assertSame(5, $settings->failed_attempts);
        $this->assertTrue($settings->locked_until->isFuture());

        $this->post('/pin/unlock', ['pin' => '123456'])->assertSessionHasErrors('pin');
        $this->get('/')->assertRedirect(route('pin.unlock'));
        $this->get('/pin/unlock')->assertOk()->assertSee('dikunci sementara');
    }

    public function test_lockout_expires_and_allows_correct_pin(): void
    {
        app(PinSecurityService::class)->setPin('123456');
        $settings = AppSetting::current();
        $settings->failed_attempts = 5;
        $settings->locked_until = now()->subMinute();
        $settings->save();

        $this->post('/pin/unlock', ['pin' => '123456'])->assertRedirect(route('dashboard.index'));

        $settings->refresh();
        $this->assertSame(0, $settings->failed_attempts);
        $this->assertNull($settings->locked_until);
    }

    public function test_idle_timeout_locks_after_15_minutes(): void
    {
        app(PinSecurityService::class)->setPin('123456');

        $this->withSession(['pin_unlocked_at' => time() - 300])->get('/')->assertOk();
        $this->withSession(['pin_unlocked_at' => time() - 960])->get('/')->assertRedirect(route('pin.unlock'));
    }

    public function test_reset_pin_command_clears_pin_counters_and_sessions(): void
    {
        config(['session.driver' => 'database']);
        app(PinSecurityService::class)->setPin('123456');

        $settings = AppSetting::current();
        $settings->failed_attempts = 5;
        $settings->locked_until = now()->addMinutes(5);
        $settings->save();

        DB::table('sessions')->insert([
            'id' => 'uji-sesi-1', 'user_id' => null, 'ip_address' => '127.0.0.1',
            'user_agent' => 'uji', 'payload' => 'x', 'last_activity' => time(),
        ]);

        $this->artisan('catatcash:reset-pin')
            ->expectsConfirmation('Apakah Anda yakin ingin mereset PIN CatatCash?', 'yes')
            ->assertSuccessful();

        $settings->refresh();
        $this->assertNull($settings->pin_hash);
        $this->assertSame(0, $settings->failed_attempts);
        $this->assertNull($settings->locked_until);
        $this->assertSame(0, DB::table('sessions')->count());
        $this->get('/pin/setup')->assertOk();
    }
}