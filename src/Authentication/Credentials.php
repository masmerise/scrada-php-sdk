<?php declare(strict_types=1);

namespace Scrada\Authentication;

use Scrada\Authentication\Key\ApiKey;
use Scrada\Authentication\Key\Password;
use SensitiveParameter;

final readonly class Credentials
{
    private function __construct(
        public ApiKey $key,
        public Password $password,
    ) {}

    public static function present(
        #[SensitiveParameter] string $key,
        #[SensitiveParameter] string $password,
    ): self {
        return new self(
            ApiKey::fromString($key),
            Password::fromString($password),
        );
    }

    public function toHash(): string
    {
        return md5("{$this->key->toString()}:{$this->password->toString()}");
    }
}
