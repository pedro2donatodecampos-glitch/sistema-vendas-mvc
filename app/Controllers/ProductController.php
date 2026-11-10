<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Models\Cart;
use App\Models\Product;
use App\Repositories\ProductRepository;

/**
 * Controller da tela "Cadastro de produtos" (formulário do PDV fornecido em aula, agora em MVC).
 */
final class ProductController
{
    private const MAX_IMAGE_BYTES = 2 * 1024 * 1024;
    private const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    private ProductRepository $products;

    public function __construct()
    {
        $this->products = new ProductRepository(Database::connection());
    }

    public function index(): void
    {
        View::render('products/index', ['title' => 'Produtos', 'products' => $this->products->all()]);
    }

    public function create(): void
    {
        View::render('products/form', ['title' => 'Novo produto', 'product' => null]);
    }

    public function edit(string $id): void
    {
        $product = $this->products->find((int) $id);
        if (!$product) {
            $this->notFound();
            return;
        }

        View::render('products/form', ['title' => 'Editar produto', 'product' => $product]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function update(string $id): void
    {
        $current = $this->products->find((int) $id);
        if (!$current) {
            $this->notFound();
            return;
        }
        $this->save($current);
    }

    public function delete(string $id): void
    {
        $product = $this->products->find((int) $id);
        if ($product) {
            $this->products->delete((int) $id);
            (new Cart())->remove((int) $id);
            self::removeImage($product['image']);
        }
        flash('success', 'Produto removido com sucesso.');
        redirect('/products');
    }

    private function save(?array $current): void
    {
        $id = $current === null ? null : (int) $current['id'];
        $back = $id === null ? '/products/create' : '/products/' . $id . '/edit';
        $product = Product::fromInput($_POST);

        $error = $product->validate();
        if ($error === null && $this->products->codeExists($product->code, $id)) {
            $error = 'Já existe um produto com o código ' . $product->code . '.';
        }
        $upload = $_FILES['image'] ?? null;
        $hasUpload = is_array($upload) && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        if ($error === null && $id === null && !$hasUpload) {
            $error = 'Selecione a imagem do produto.';
        }
        if ($error !== null) {
            flash('error', $error);
            redirect($back);
        }

        $image = $current['image'] ?? null;
        if ($hasUpload) {
            try {
                $image = $this->storeImage($upload);
            } catch (\DomainException $exception) {
                flash('error', $exception->getMessage());
                redirect($back);
            }
        }

        $this->products->save($product, $image, $id);
        if ($hasUpload && $current !== null) {
            self::removeImage($current['image']);
        }

        flash('success', $id === null ? 'Produto cadastrado com sucesso.' : 'Produto atualizado com sucesso.');
        redirect('/products');
    }

    private function storeImage(array $file): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new \DomainException('Não foi possível enviar a imagem. Tente novamente.');
        }
        if ($file['size'] > self::MAX_IMAGE_BYTES) {
            throw new \DomainException('A imagem deve ter no máximo 2 MB.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extension = self::IMAGE_TYPES[$mime] ?? null;
        if ($extension === null) {
            throw new \DomainException('Envie uma imagem JPG, PNG, WEBP ou GIF.');
        }

        $name = bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], self::uploadDir() . '/' . $name)) {
            throw new \DomainException('Não foi possível salvar a imagem.');
        }

        return '/uploads/' . $name;
    }

    private static function removeImage(?string $image): void
    {
        if ($image && str_starts_with($image, '/uploads/')) {
            $path = self::uploadDir() . '/' . basename($image);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private static function uploadDir(): string
    {
        $dir = dirname(__DIR__, 2) . '/public/uploads';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Não foi possível criar a pasta de imagens.');
        }
        return $dir;
    }

    private function notFound(): void
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Produto não encontrado']);
    }
}
