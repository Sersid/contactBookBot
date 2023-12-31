<?php
declare(strict_types=1);

namespace Sersid\ContactBookBot\Category\UseCase;

use Psr\EventDispatcher\EventDispatcherInterface;
use Sersid\ContactBookBot\Category\Domain\Entity\CategoryRepositoryInterface;
use Sersid\ContactBookBot\Category\Domain\Event\CategoryTurnedOffEvent;

final readonly class TurnOff
{
    public function __construct(
        private GetByUuid $getByUuid,
        private CategoryRepositoryInterface $repository,
        private EventDispatcherInterface $eventDispatcher,
    )
    {
    }

    public function handle(string $uuid): void
    {
        $category = $this->getByUuid->handle($uuid);
        $oldStatus = $category->getStatus();
        $category->turnOff();

        $this->repository->update($category);

        $this->eventDispatcher->dispatch(new CategoryTurnedOffEvent($category, $oldStatus));
    }
}