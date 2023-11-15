<?php
declare(strict_types=1);

namespace Sersid\PhoneBookBot\Domain\Contact\Entity;

use Sersid\PhoneBookBot\Domain\Category\Entity\Category;
use Sersid\PhoneBookBot\Domain\Contact\Event;
use Sersid\Shared\AggregateRoot;
use Sersid\Shared\EventTrait;
use Sersid\Shared\ValueObject\Uuid;

final class Contact implements AggregateRoot
{
    use EventTrait;
    public function __construct(
        private readonly Uuid $uuid,
        private Category $category,
        private readonly Name $name,
        private readonly array $phones = [],
        private readonly Address $address = new Address(),
        private readonly Website $website = new Website(),
        private readonly Status $status = Status::Enable
    ) {
        $this->recordEvent(new Event\ContactCreatedEvent($this));
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getPhones(): array
    {
        return $this->phones;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getWebsite(): Website
    {
        return $this->website;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function changeCategory(Category $category): void
    {
        $this->recordEvent(new Event\ContactChangedCategoryEvent($this, $this->category));
        $this->category = $category;
    }
}