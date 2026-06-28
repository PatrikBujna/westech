<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * Raised for malformed requests (e.g. invalid JSON body). Maps to 400.
 */
final class BadRequestException extends RuntimeException
{
}
