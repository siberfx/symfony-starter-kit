<?php

declare(strict_types=1);

namespace App\Setting\Command;

use App\Setting\Domain\SettingType;
use Webmozart\Assert\Assert;

/**
 * Команда сохранения настройки
 */
final readonly class SaveSettingCommand
{
    /**
     * @param non-empty-string $type
     * @param non-empty-string $value
     */
    public function __construct(
        public string $type,
        public string $value,
    ) {
        Assert::inArray($type, array_column(SettingType::cases(), 'value'), 'Указан неверный тип');
    }
}
