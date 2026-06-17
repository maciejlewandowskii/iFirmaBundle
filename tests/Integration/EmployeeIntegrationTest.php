<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Tests\Integration;

use function is_string;

use maciejlewandowskii\iFirmaApi\DTO\Request\Employee\SendQuestionnaireRequest;
use maciejlewandowskii\iFirmaApi\Exception\ApiException;
use PHPUnit\Framework\Attributes\Group;

use function sprintf;

#[Group('integration')]
final class EmployeeIntegrationTest extends IntegrationTestCase
{
    /**
     * Questionnaire tests require a real email address to send to.
     * Set IFIRMA_TEST_EMPLOYEE_EMAIL to opt into these tests.
     */
    private function requireEmployeeEmail(): string
    {
        $value = $_ENV['IFIRMA_TEST_EMPLOYEE_EMAIL'] ?? getenv('IFIRMA_TEST_EMPLOYEE_EMAIL');
        $email = is_string($value) ? $value : '';

        if ('' === $email) {
            $this->markTestSkipped(
                'Employee questionnaire tests require IFIRMA_TEST_EMPLOYEE_EMAIL env var.',
            );
        }

        return $email;
    }

    public function testSendQuestionnaireDispatchesEmail(): void
    {
        $email = $this->requireEmployeeEmail();

        try {
            $this->api()->employeeService->sendQuestionnaire(new SendQuestionnaireRequest(
                email: $email,
                firstName: 'Integration',
                lastName: sprintf('Test-%d', time()),
                isCooperatingPerson: false,
            ));

            // No exception thrown = email dispatched successfully
            $this->addToAssertionCount(1);
        } catch (ApiException $e) {
            $this->skipOnKnownConfigError($e);

            throw $e;
        }
    }
}
