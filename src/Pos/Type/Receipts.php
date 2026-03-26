<?php declare(strict_types=1);

namespace Scrada\Pos\Type;

use Scrada\Core\Type\Collection;
use Scrada\Pos\AddReceipts\Receipt;

/** @extends Collection<Receipt> */
final readonly class Receipts extends Collection {}
