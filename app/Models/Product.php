<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model de Produto: mesmos campos do formulário "Cadastro de Produtos" do PDV da aula.
 */
final class Product
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $category,
        public readonly string $expiresAt,
        public readonly ?int $stock,
        public readonly ?float $unitPrice,
        public readonly ?float $packagePrice,
    ) {
    }

    public static function fromInput(array $input): self
    {
        $int = filter_var($input['stock'] ?? null, FILTER_VALIDATE_INT);
        $unit = filter_var($input['unit_price'] ?? null, FILTER_VALIDATE_FLOAT);
        $package = filter_var($input['package_price'] ?? null, FILTER_VALIDATE_FLOAT);

        return new self(
            mb_strtoupper(trim((string) ($input['code'] ?? ''))),
            trim((string) ($input['name'] ?? '')),
            trim((string) ($input['category'] ?? '')),
            trim((string) ($input['expires_at'] ?? '')),
            $int === false ? null : $int,
            $unit === false ? null : round($unit, 2),
            $package === false ? null : round($package, 2),
        );
    }

    public function validate(): ?string
    {
        if ($this->code === '' || $this->name === '' || $this->category === '') {
            return 'Preencha código, nome e categoria do produto.';
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $this->expiresAt);
        if (!$date || $date->format('Y-m-d') !== $this->expiresAt) {
            return 'Informe uma data de validade válida.';
        }
        if ($this->stock === null || $this->stock < 0) {
            return 'A quantidade em estoque deve ser um número inteiro maior ou igual a zero.';
        }
        if ($this->unitPrice === null || $this->unitPrice < 0 || $this->packagePrice === null || $this->packagePrice < 0) {
            return 'Informe preços válidos (maiores ou iguais a zero).';
        }

        return null;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'category' => $this->category,
            'expires_at' => $this->expiresAt,
            'stock' => $this->stock,
            'unit_price' => $this->unitPrice,
            'package_price' => $this->packagePrice,
        ];
    }

    public static function isExpired(?string $expiresAt): bool
    {
        return $expiresAt !== null && $expiresAt !== '' && $expiresAt < date('Y-m-d');
    }
}
