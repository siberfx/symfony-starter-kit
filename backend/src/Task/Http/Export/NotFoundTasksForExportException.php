<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use Exception;

/**
 * Нет задач для экспорта
 */
final class NotFoundTasksForExportException extends Exception
{
}
