<?php
declare(strict_types=1);

namespace Tests\ContactBookBot\Contact\Domain\Entity;

use PHPUnit\Framework\Attributes\TestDox;
use Sersid\ContactBookBot\Contact\Domain\Entity\Address;
use Sersid\ContactBookBot\Contact\Domain\Entity\Website;
use Sersid\ContactBookBot\Contact\Domain\Event;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertSame;

#[TestDox('Тесты изменения адреса контакта')]
final class ContactChangeAddressTest extends ContactTestCase
{
    #[TestDox('Тест попытки изменить адрес без изменения содержимого адреса')]
    public function testNoChangeAddress(): void
    {
        $address = new Address();

        self::$contact->releaseEvents();
        self::$contact->changeAddress($address);

        assertNotSame(self::$contact->getAddress(), $address);
        assertSame([], self::$contact->releaseEvents());
    }

    #[TestDox('Тест изменения адреса')]
    public function testChangeAddress(): void
    {
        $address = new Address('ул. Пушкина, 1');

        self::$contact->changeAddress($address);

        assertSame(self::$contact->getAddress(), $address);
    }

    #[TestDox('Тест создания события при изменении адреса')]
    public function testEventOnChangeAddress(): void
    {
        /** @var Event\ContactChangedAddressEvent $event */
        $event = self::$contact->releaseEvents()[0];

        assertInstanceOf(Event\ContactChangedAddressEvent::class, $event);
        assertSame(self::$contact, $event->getContact());
        assertSame(self::$address, $event->getOldAddress());
    }

    #[TestDox('Тест попытки изменить вебсайт на тот же')]
    public function testNoChangeWebsite(): void
    {
        $website = new Website();

        self::$contact->changeWebsite($website);

        assertNotSame(self::$contact->getWebsite(), $website);
        assertSame([], self::$contact->releaseEvents());
    }
}