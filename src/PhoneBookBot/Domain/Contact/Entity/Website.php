<?php
declare(strict_types=1);

namespace Sersid\PhoneBookBot\Domain\Contact\Entity;

use Sersid\Shared\ValueObject\StringValueObject;

final readonly class Website extends StringValueObject
{
    public function __construct(string $value = '')
    {
        parent::__construct($value);
    }
}