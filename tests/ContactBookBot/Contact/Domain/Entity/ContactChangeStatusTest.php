<?php
declare(strict_types=1);

namespace Tests\ContactBookBot\Contact\Domain\Entity;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Sersid\ContactBookBot\Contact\Domain\Entity\Address;
use Sersid\ContactBookBot\Contact\Domain\Entity\Contact;
use Sersid\ContactBookBot\Contact\Domain\Entity\MapLocation;
use Sersid\ContactBookBot\Contact\Domain\Entity\Phone;
use Sersid\ContactBookBot\Contact\Domain\Entity\Phones;
use Sersid\ContactBookBot\Contact\Domain\Entity\Status;
use Sersid\ContactBookBot\Contact\Domain\Entity\Website;
use function PHPUnit\Framework\assertSame;

#[TestDox('Тесты изменения телефонов контакта')]
final class ContactChangeStatusTest extends ContactTestCase
{
    #[TestDox('Тест попытки опубликовать контакт без контактных данных')]
    public function testPublishEmptyContact(): void
    {
        $this->expectExceptionMessage('Необходимо указать контактную информацию');

        self::$contact->publish();
    }

    public static function publishDataProvider(): array
    {
        return [
            'есть адрес' => [
                ['address' => new Address('ул. Пушкина, д. 1')]
            ],
            'есть координаты на карте' => [
                ['address' => new Address('', new MapLocation(51.6607, 39.2003))]
            ],
            'есть вебсайт' => [
                ['website' => new Website('www.example.com')]
            ],
            'есть телефон' => [
                ['phones' => new Phones([new Phone('88005553535')])]
            ],
        ];
    }

    #[TestDox('Тест публикации контакта')]
    #[DataProvider('publishDataProvider')]
    public function testPublish(array $arrange): void
    {
        $args = array_merge(['uuid' => self::$uuid, 'category' => self::$category, 'name' => self::$name], $arrange);
        $contact = new Contact(...$args);

        $contact->publish();

        assertSame(Status::Published, $contact->getStatus());
    }
}