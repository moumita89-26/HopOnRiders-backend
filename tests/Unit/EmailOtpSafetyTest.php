<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EmailOtpSafetyTest extends TestCase
{
    public function test_email_layout_used_by_mail_helpers_exists(): void
    {
        $this->assertFileExists(dirname(__DIR__, 2).'/resources/views/emails/email_template.blade.php');
    }

    public function test_email_otp_endpoint_does_not_contain_debug_dump(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Api/UserController.php');
        preg_match('/public function sendEmailOTP\(.*?\n    }\n/s', $controller, $matches);

        $this->assertNotEmpty($matches, 'The sendEmailOTP method was not found.');
        $this->assertStringNotContainsString('dd(', $matches[0]);
        $this->assertStringContainsString('catch (\\Throwable', $matches[0]);
        $this->assertStringContainsString('CustomHelper::ErrorResponse', $matches[0]);
    }
}
