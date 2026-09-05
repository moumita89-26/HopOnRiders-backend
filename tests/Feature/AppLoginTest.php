<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('The pdo_sqlite extension is required for login migration tests.');
        }

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        Artisan::call('migrate:fresh', ['--force' => true]);

        DB::table('users')->insert([
            'name' => 'Login Test',
            'email' => 'login@example.com',
            'unique_id' => 'login-test',
            'phone' => '1234567890',
            'password' => Hash::make('test-password'),
            'role' => 1,
            'is_verified' => true,
            'is_email_verify' => 1,
        ]);
    }

    public function test_login_saves_app_device_details(): void
    {
        foreach (['android', 'ios'] as $deviceType) {
            $this->postJson('/api/login', [
                'phone' => 'login@example.com',
                'password' => 'test-password',
                'deviceToken' => 'test-device-token',
                'deviceType' => $deviceType,
            ])->assertOk()->assertJson(['responseCode' => 1]);

            $this->assertDatabaseHas('users', [
                'email' => 'login@example.com',
                'device_token' => 'test-device-token',
                'device_type' => $deviceType,
            ]);
        }
    }

    public function test_login_without_token_preserves_existing_device_details(): void
    {
        DB::table('users')->update(['device_token' => 'existing-token', 'device_type' => 'ios']);

        $this->postJson('/api/login', [
            'phone' => '1234567890',
            'password' => 'test-password',
        ])->assertOk()->assertJson(['responseCode' => 1]);

        $this->assertDatabaseHas('users', ['device_token' => 'existing-token', 'device_type' => 'ios']);
    }

    public function test_login_allows_token_without_device_type(): void
    {
        $this->postJson('/api/login', [
            'phone' => 'login@example.com',
            'password' => 'test-password',
            'deviceToken' => 'test-device-token',
        ])->assertOk()->assertJson(['responseCode' => 1]);

        $this->assertDatabaseHas('users', ['device_token' => 'test-device-token', 'device_type' => null]);
    }

    public function test_wrong_password_does_not_update_device_details(): void
    {
        $this->postJson('/api/login', [
            'phone' => 'login@example.com',
            'password' => 'wrong-password',
            'deviceToken' => 'test-device-token',
            'deviceType' => 'android',
        ])->assertOk()->assertJson(['responseCode' => 0]);

        $this->assertDatabaseHas('users', ['device_token' => null, 'device_type' => null]);
    }

    public function test_missing_column_logs_cause_without_credentials_or_token(): void
    {
        Schema::table('users', fn ($table) => $table->dropColumn('device_type'));
        Log::spy();

        $this->postJson('/api/login', [
            'phone' => 'login@example.com',
            'password' => 'test-password',
            'deviceToken' => 'test-device-token',
            'deviceType' => 'android',
        ])->assertOk()->assertExactJson([
            'responseCode' => 0,
            'responseText' => 'Something Wrong Please try again.',
        ]);

        Log::shouldHaveReceived('error')->once()->withArgs(function ($message, $context) {
            $this->assertSame('Login failed unexpectedly.', $message);
            $this->assertStringContainsString('device_type', $context['message']);
            $serialized = json_encode($context);
            foreach (['test-password', 'test-device-token', 'login@example.com'] as $secret) {
                $this->assertStringNotContainsString($secret, $serialized);
            }

            return true;
        });
    }
}
