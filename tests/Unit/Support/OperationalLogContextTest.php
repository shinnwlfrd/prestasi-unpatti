<?php

namespace Tests\Unit\Support;

use App\Support\OperationalLogContext;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperationalLogContextTest extends TestCase
{
    #[Test]
    public function it_builds_auth_failure_context_with_standard_keys(): void
    {
        $context = OperationalLogContext::authFailure('oauth_state_mismatch', [
            'user_id' => 10,
        ]);

        $this->assertSame('auth', $context['domain']);
        $this->assertSame('auth.failure.rate', $context['metric_key']);
        $this->assertSame('oauth_state_mismatch', $context['event']);
        $this->assertSame('warning', $context['severity']);
        $this->assertSame(10, $context['user_id']);
    }

    #[Test]
    public function it_builds_upload_failure_context_with_standard_keys(): void
    {
        $context = OperationalLogContext::uploadFailure('student_certificate_invalid');

        $this->assertSame('upload', $context['domain']);
        $this->assertSame('upload.failure.rate', $context['metric_key']);
        $this->assertSame('student_certificate_invalid', $context['event']);
        $this->assertSame('warning', $context['severity']);
    }

    #[Test]
    public function it_builds_validation_failure_context_with_standard_keys(): void
    {
        $context = OperationalLogContext::validationFailure('faculty_approval_invalid_status');

        $this->assertSame('validation', $context['domain']);
        $this->assertSame('validation.failure.rate', $context['metric_key']);
        $this->assertSame('faculty_approval_invalid_status', $context['event']);
        $this->assertSame('warning', $context['severity']);
    }
}
