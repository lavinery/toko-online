<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    private const PRODUCT_IMAGE_PATH = 'products';
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    public function uploadProductImage(UploadedFile $file, Product $product, bool $isPrimary = false): ProductImage
    {
        $this->validateImage($file);

        $filename = $this->generateFilename($file, $product);
        $path = $file->storeAs(self::PRODUCT_IMAGE_PATH, $filename, 'public');

        return $product->images()->create([
            'path' => '/' . $path,
            'alt_text' => $product->name,
            'is_primary' => $isPrimary,
            'sort_order' => $product->images()->count() + 1,
        ]);
    }

    public function attachImagesToProduct(Product $product, array $images): void
    {
        foreach ($images as $index => $imageData) {
            if ($imageData instanceof UploadedFile) {
                $this->uploadProductImage($imageData, $product, $index === 0);
            } elseif (is_array($imageData) && isset($imageData['url'])) {
                // Handle external URLs or existing images
                $product->images()->create([
                    'path' => $imageData['url'],
                    'alt_text' => $imageData['alt_text'] ?? $product->name,
                    'is_primary' => $imageData['is_primary'] ?? ($index === 0),
                    'sort_order' => $imageData['sort_order'] ?? ($index + 1),
                ]);
            }
        }
    }

    public function updateProductImages(Product $product, array $images): void
    {
        // Delete existing images
        $this->deleteProductImages($product);

        // Add new images
        $this->attachImagesToProduct($product, $images);
    }

    public function deleteProductImages(Product $product): void
    {
        foreach ($product->images as $image) {
            $this->deleteImageFile($image);
        }

        $product->images()->delete();
    }

    public function deleteProductImage(ProductImage $image): bool
    {
        $this->deleteImageFile($image);
        return $image->delete();
    }

    public function reorderProductImages(Product $product, array $imageIds): void
    {
        foreach ($imageIds as $index => $imageId) {
            $product->images()
                ->where('id', $imageId)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function setPrimaryImage(Product $product, int $imageId): void
    {
        // Remove primary flag from all images
        $product->images()->update(['is_primary' => false]);

        // Set new primary image
        $product->images()
            ->where('id', $imageId)
            ->update(['is_primary' => true]);
    }

    private function validateImage(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Invalid file upload');
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed size');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \InvalidArgumentException('Invalid file type. Allowed types: ' . implode(', ', self::ALLOWED_EXTENSIONS));
        }
    }

    private function generateFilename(UploadedFile $file, Product $product): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);
        
        return "{$product->slug}-{$timestamp}-{$random}.{$extension}";
    }

    private function deleteImageFile(ProductImage $image): void
    {
        if (Str::startsWith($image->path, '/products/')) {
            $filePath = ltrim($image->path, '/');
            Storage::disk('public')->delete($filePath);
        }
    }
}