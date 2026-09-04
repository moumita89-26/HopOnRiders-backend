<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AdminUserEditViewTest extends TestCase
{
    public function test_role_select_reflects_driver_state(): void
    {
        $select = $this->renderSelect('role', ['role' => 1]);

        $this->assertStringContainsString('value="1" selected', $select);
        $this->assertStringNotContainsString('value="2" selected', $select);
    }

    public function test_role_select_reflects_passenger_state(): void
    {
        $select = $this->renderSelect('role', ['role' => 2]);

        $this->assertStringContainsString('value="2" selected', $select);
        $this->assertStringNotContainsString('value="1" selected', $select);
        $this->assertStringContainsString('Passenger', $select);
        $this->assertStringNotContainsString('Passanger', $select);
    }

    public function test_verified_select_reflects_pending_user_state(): void
    {
        $select = $this->renderSelect('is_verified', ['is_verified' => 0]);

        $this->assertStringContainsString('value="0" selected', $select);
        $this->assertStringNotContainsString('value="1" selected', $select);
    }

    public function test_verified_select_reflects_approved_user_state(): void
    {
        $select = $this->renderSelect('is_verified', ['is_verified' => 1]);

        $this->assertStringContainsString('value="1" selected', $select);
        $this->assertStringNotContainsString('value="0" selected', $select);
    }

    public function test_document_verified_select_reflects_stored_state(): void
    {
        $select = $this->renderSelect('is_document_verify', ['is_document_verify' => 0]);

        $this->assertStringContainsString('value="0" selected', $select);
        $this->assertStringNotContainsString('value="1" selected', $select);
    }

    private function renderSelect(string $name, array $attributes): string
    {
        $user = new User([
            'name' => 'Driver',
            'email' => 'driver@example.com',
        ]);
        $user->id = 123;
        $user->phone = '+260570314488';
        $user->forceFill($attributes);

        $template = file_get_contents(resource_path('views/admin/user/edit.blade.php'));
        $matched = preg_match('/<select name="' . preg_quote($name, '/') . '".*?<\/select>/s', $template, $matches);

        $this->assertSame(1, $matched, "The {$name} select was not rendered.");

        return preg_replace('/\s+/', ' ', Blade::render($matches[0], ['row' => $user]));
    }
}
