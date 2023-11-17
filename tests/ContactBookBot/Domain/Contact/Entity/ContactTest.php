<?php
declare(strict_types=1);

namespace Tests\ContactBookBot\Domain\Contact\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use Sersid\ContactBookBot\Domain\Category\Entity\Category;
use Sersid\ContactBookBot\Domain\Category\Entity\Name as CategoryName;
use Sersid\ContactBookBot\Domain\Contact\Entity\Address;
use Sersid\ContactBookBot\Domain\Contact\Entity\Contact;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Sersid\ContactBookBot\Domain\Contact\Entity\Phone;
use Sersid\ContactBookBot\Domain\Contact\Entity\Phones;
use Sersid\ContactBookBot\Domain\Contact\Entity\Website;
use Sersid\ContactBookBot\Domain\Contact\Event;
use Sersid\ContactBookBot\Domain\Contact\Entity\Name;
use Sersid\ContactBookBot\Domain\Contact\Entity\Status;
use Sersid\Shared\ValueObject\Uuid;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertSame;

#[CoversClass(Contact::class)]
#[TestDox('Тесты контакта')]
final class ContactTest extends TestCase
{
    private static Uuid $uuid;
    private static Category $category;
    private static Name $name;
    private static Phones $phones;
    private static Address $address;
    private static Website $website;
    private static Contact $contact;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$uuid = Uuid::next();
        self::$category = new Category(
            new Uuid('f3190be0-ecd2-4cd0-bf09-9d999bd17620'),
            new CategoryName('Управляющая компания')
        );
        self::$name = new Name();
        self::$phones = new Phones();
        self::$address = new Address();
        self::$website = new Website();

        self::$contact = new Contact(
            uuid: self::$uuid,
            category: self::$category,
            name: self::$name,
            phones: self::$phones,
            address: self::$address,
            website: self::$website,
        );
    }

    #[TestDox('Тест создания контакта')]
    public function testCreate(): void
    {
        assertSame(self::$uuid, self::$contact->getUuid());
        assertSame(self::$category, self::$contact->getCategory());
        assertSame(self::$name, self::$contact->getName());
        assertSame(Status::Draft, self::$contact->getStatus());
    }

    #[TestDox('Тест создания события при создании контакта')]
    #[Depends('testCreate')]
    public function testEventOnCreated(): void
    {
        /** @var Event\ContactCreatedEvent $event */
        $event = self::$contact->releaseEvents()[0];

        assertInstanceOf(Event\ContactCreatedEvent::class, $event);
        assertSame(self::$contact, $event->getContact());
    }

    #[TestDox('Тест попытки изменения категории на такую же категорию')]
    public function testNoChangeCategory(): void
    {
        $category = new Category(
            new Uuid('f3190be0-ecd2-4cd0-bf09-9d999bd17620'),
            new CategoryName('Управляющая компания')
        );

        self::$contact->changeCategory($category);

        assertNotSame(self::$contact->getCategory(), $category);
        assertSame([], self::$contact->releaseEvents());
    }

    #[TestDox('Тест изменения категории')]
    public function testChangeCategory(): void
    {
        $category = new Category(Uuid::next(), new CategoryName('Новая категория'));

        self::$contact->changeCategory($category);

        assertSame($category, self::$contact->getCategory());
    }

    #[TestDox('Тест создания события при изменении категории')]
    #[Depends('testChangeCategory')]
    public function testEventOnChangeCategory(): void
    {
        /** @var Event\ContactChangedCategoryEvent $event */
        $event = self::$contact->releaseEvents()[0];

        assertInstanceOf(Event\ContactChangedCategoryEvent::class, $event);
        assertSame(self::$contact, $event->getContact());
        assertSame(self::$category, $event->getOldCategory());
    }

    #[TestDox('Тест попытки переименовать контакт в то же имя')]
    public function testNoRename(): void
    {
        $newName = new Name();

        self::$contact->rename($newName);

        assertNotSame(self::$contact->getName(), $newName);
        assertSame([], self::$contact->releaseEvents());
    }

    #[TestDox('Тест переименования контакта')]
    public function testRename(): void
    {
        $newName = new Name('Новое имя контакта');

        self::$contact->rename($newName);

        assertSame(self::$contact->getName(), $newName);
    }

    #[TestDox('Тест создания события при переименовании контакта')]
    #[Depends('testRename')]
    public function testEventOnRename(): void
    {
        /** @var Event\ContactRenamedEvent $event */
        $event = self::$contact->releaseEvents()[0];

        assertInstanceOf(Event\ContactRenamedEvent::class, $event);
        assertSame(self::$contact, $event->getContact());
        assertSame(self::$name, $event->getOldName());
    }

    #[TestDox('Тест добавления телефона')]
    public function testAddPhone(): void
    {
        $phone = new Phone('88005553535');

        self::$contact->addPhone($phone);

        self::assertSame($phone, self::$phones[0]);
    }

    #[TestDox('Тест создания события при добавлении телефона')]
    #[Depends('testAddPhone')]
    public function testEventOnAddPhone(): void
    {
        /** @var Event\ContactPhoneAddedEvent $event */
        $event = self::$contact->releaseEvents()[0];

        assertInstanceOf(Event\ContactPhoneAddedEvent::class, $event);
        assertSame(self::$contact, $event->getContact());
        assertSame(self::$phones[0], $event->getPhone());
    }

    #[TestDox('Тест попытки изменить адрес без изменения содержимого адреса')]
    public function testNoChangeAddress(): void
    {
        $address = new Address();

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
    #[Depends('testRename')]
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

    #[TestDox('Тест изменения вебсайта')]
    public function testChangeWebsite(): void
    {
        $website = new Website('www.website.com');

        self::$contact->changeWebsite($website);

        assertSame(self::$contact->getWebsite(), $website);
    }

    #[TestDox('Тест создания события при изменении вебсайта')]
    #[Depends('testChangeWebsite')]
    public function testEventOnChangeWebsite(): void
    {
        /** @var Event\ContactChangedWebsiteEvent $event */
        $event = self::$contact->releaseEvents()[0];

        assertInstanceOf(Event\ContactChangedWebsiteEvent::class, $event);
        assertSame(self::$contact, $event->getContact());
        assertSame(self::$website, $event->getOldWebsite());
    }
}
