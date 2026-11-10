<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model de Cliente: representa os dados de um cliente e as regras de validação.
 */
final class Customer
{
    public function __construct(
        public readonly string $name,
        public readonly string $email = '',
        public readonly string $phone = '',
        public readonly string $document = '',
    ) {
    }

    public static function fromInput(array $input): self
    {
        return new self(
            trim((string) ($input['name'] ?? '')),
            trim((string) ($input['email'] ?? '')),
            trim((string) ($input['phone'] ?? '')),
            trim((string) ($input['document'] ?? '')),
        );
    }

    /** @return string|null Mensagem do primeiro erro encontrado ou null quando válido. */
    public function validate(): ?string
    {
        if ($this->name === '') {
            return 'Informe o nome do cliente.';
        }
        if (mb_strlen($this->name) > 120) {
            return 'O nome deve ter no máximo 120 caracteres.';
        }
        if ($this->email !== '' && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return 'Informe um e-mail válido.';
        }
        if ($this->document !== '' && !preg_match('/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/', $this->document)) {
            return 'Informe um CPF no formato 000.000.000-00.';
        }

        return null;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'document' => $this->document,
        ];
    }
}
