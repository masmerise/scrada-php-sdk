<?php declare(strict_types=1);

namespace Scrada\Pos\Type;

use Scrada\Core\Type\Collection;
use Scrada\Pos\Type\Primitive\ReceiptId;

/** @extends Collection<ReceiptId> */
final readonly class ReceiptIds extends Collection {}
