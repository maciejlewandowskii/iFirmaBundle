<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Service;

use maciejlewandowskii\iFirmaApi\DTO\Request\Employee\SaveQuestionnaireRequest;
use maciejlewandowskii\iFirmaApi\DTO\Request\Employee\SendQuestionnaireRequest;
use maciejlewandowskii\iFirmaApi\Enum\AuthKeyType;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

use function sprintf;

final class EmployeeService extends AbstractService
{
    /**
     * @throws ExceptionInterface
     */
    public function sendQuestionnaire(SendQuestionnaireRequest $request): void
    {
        $this->validate($request);

        $this->client->post('/kwestionariusz/send.json', AuthKeyType::Subscriber, $this->toArray($request));
    }

    /**
     * @throws ExceptionInterface
     */
    public function saveQuestionnaire(SaveQuestionnaireRequest $request): void
    {
        $this->validate($request);

        $this->client->post('/kwestionariusz.json', AuthKeyType::Subscriber, $this->toArray($request));
    }

    public function getQuestionnaire(string $ticket): string
    {
        return $this->client->getRaw(
            sprintf('/kwestionariusz/find/%s.json', urlencode($ticket)),
            AuthKeyType::Subscriber,
        );
    }

    public function getQuestionnairePdf(string $ticket): string
    {
        return $this->client->getRaw(
            sprintf('/kwestionariusz/find/%s.pdf', urlencode($ticket)),
            AuthKeyType::Subscriber,
        );
    }
}
