<?php

declare(strict_types=1);

namespace maciejlewandowskii\iFirmaApi\Synchronization;

use DateTimeImmutable;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaContractorInterface;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaEntityInterface;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaInvoiceInterface;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaOtherExpenseInterface;
use maciejlewandowskii\iFirmaApi\Contract\IFirmaVatExpenseInterface;
use maciejlewandowskii\iFirmaApi\Event\PostSyncEvent;
use maciejlewandowskii\iFirmaApi\Event\PreSyncEvent;
use maciejlewandowskii\iFirmaApi\iFirmaApi;

use function md5;

use RuntimeException;

use function serialize;
use function sprintf;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class SynchronizationManager
{
    public function __construct(
        private iFirmaApi $api,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function sync(IFirmaEntityInterface $entity): object
    {
        if ($entity instanceof IFirmaInvoiceInterface) {
            return $this->syncInvoice($entity);
        }

        if ($entity instanceof IFirmaContractorInterface) {
            return $this->syncContractor($entity);
        }

        if ($entity instanceof IFirmaVatExpenseInterface) {
            return $this->syncVatExpense($entity);
        }

        if ($entity instanceof IFirmaOtherExpenseInterface) {
            return $this->syncOtherExpense($entity);
        }

        throw new RuntimeException(sprintf('Entity "%s" must implement one of: IFirmaInvoiceInterface, IFirmaContractorInterface, IFirmaVatExpenseInterface, IFirmaOtherExpenseInterface.', $entity::class));
    }

    private function syncInvoice(IFirmaInvoiceInterface $entity): object
    {
        $request = $entity->toCreateInvoiceRequest();
        $preEvent = new PreSyncEvent($entity, $request);
        $this->eventDispatcher->dispatch($preEvent);

        if ($preEvent->isCanceled()) {
            return $request;
        }

        $response = $this->api->invoiceService->create($request);
        $entity->setIFirmaId($response->identifier);
        $entity->setIFirmaStateHash(md5(serialize($request)));
        $entity->setIFirmaSyncedAt(new DateTimeImmutable());
        $this->eventDispatcher->dispatch(new PostSyncEvent($entity, $request, $response));

        return $response;
    }

    private function syncContractor(IFirmaContractorInterface $entity): object
    {
        if ($entity->isSynced()) {
            $iFirmaId = $entity->getIFirmaId() ?? throw new RuntimeException('Entity marked as synced but iFirma ID is null.');
            $request = $entity->toUpdateContractorRequest();
            $preEvent = new PreSyncEvent($entity, $request);
            $this->eventDispatcher->dispatch($preEvent);

            if ($preEvent->isCanceled()) {
                return $request;
            }

            $response = $this->api->contractorService->update($iFirmaId, $request);
            $entity->setIFirmaStateHash(md5(serialize($entity->toCreateContractorRequest())));
            $entity->setIFirmaSyncedAt(new DateTimeImmutable());
            $this->eventDispatcher->dispatch(new PostSyncEvent($entity, $request, $response));

            return $response;
        }

        $request = $entity->toCreateContractorRequest();
        $preEvent = new PreSyncEvent($entity, $request);
        $this->eventDispatcher->dispatch($preEvent);

        if ($preEvent->isCanceled()) {
            return $request;
        }

        $response = $this->api->contractorService->create($request);
        $entity->setIFirmaId($response->id);
        $entity->setIFirmaStateHash(md5(serialize($request)));
        $entity->setIFirmaSyncedAt(new DateTimeImmutable());
        $this->eventDispatcher->dispatch(new PostSyncEvent($entity, $request, $response));

        return $response;
    }

    private function syncVatExpense(IFirmaVatExpenseInterface $entity): object
    {
        $request = $entity->toCreateVatPurchaseRequest();
        $preEvent = new PreSyncEvent($entity, $request);
        $this->eventDispatcher->dispatch($preEvent);

        if ($preEvent->isCanceled()) {
            return $request;
        }

        $response = $this->api->expenseService->createVatPurchase($request);

        if (null !== $response->id) {
            $entity->setIFirmaId($response->id);
        }

        $entity->setIFirmaStateHash(md5(serialize($request)));
        $entity->setIFirmaSyncedAt(new DateTimeImmutable());
        $this->eventDispatcher->dispatch(new PostSyncEvent($entity, $request, $response));

        return $response;
    }

    private function syncOtherExpense(IFirmaOtherExpenseInterface $entity): object
    {
        $request = $entity->toCreateOtherCostRequest();
        $preEvent = new PreSyncEvent($entity, $request);
        $this->eventDispatcher->dispatch($preEvent);

        if ($preEvent->isCanceled()) {
            return $request;
        }

        $response = $this->api->expenseService->createOtherCost($request);

        if (null !== $response->id) {
            $entity->setIFirmaId($response->id);
        }

        $entity->setIFirmaStateHash(md5(serialize($request)));
        $entity->setIFirmaSyncedAt(new DateTimeImmutable());
        $this->eventDispatcher->dispatch(new PostSyncEvent($entity, $request, $response));

        return $response;
    }
}
