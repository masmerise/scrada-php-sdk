<?php declare(strict_types=1);

namespace Scrada\Pos\Type;

use Scrada\Core\Type\Collection;
use Scrada\Pos\AddReceipts\ReceiptLine;

/** @extends Collection<ReceiptLine> */
final readonly class ReceiptLines extends Collection {}
