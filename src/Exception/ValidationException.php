<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Exception;

use function sprintf;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends iFirmaException
{
    public function __construct(
        private readonly ConstraintViolationListInterface $violations,
    ) {
        parent::__construct($this->buildMessage($violations));
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    private function buildMessage(ConstraintViolationListInterface $violations): string
    {
        $messages = [];

        foreach ($violations as $violation) {
            $messages[] = sprintf('[%s] %s', $violation->getPropertyPath(), $violation->getMessage());
        }

        return implode('; ', $messages);
    }
}
