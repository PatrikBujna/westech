<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * Raised when the request body exceeds the configured size limit. Maps to 413.
 */
final class PayloadTooLargeException extends RuntimeException
{
}
