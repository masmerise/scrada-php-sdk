<?php declare(strict_types=1);

namespace Scrada\Pos\Type;

use Scrada\Core\Type\Collection;
use Scrada\Pos\AddReceipts\Payment;

/** @extends Collection<Payment> */
final readonly class Payments extends Collection {}
