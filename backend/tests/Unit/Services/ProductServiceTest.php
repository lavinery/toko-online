<?php

namespace Tests\Unit\Services;

use App\Services\ProductService;
use App\Services\ImageService;
use App\Services\InventoryService;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTOs\ProductDTO;
use App\Models\Product;
use Tests\TestCase;
use Mockery;

class ProductServiceTest extends TestCase
{
    protected ProductService $productService;
    protected $productRepository;
    protected $imageService;
    protected $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->imageService = Mockery::mock(ImageService::class);
        $this->inventoryService = Mockery::mock(InventoryService::class);

        $this->productService = new ProductService(
            $this->productRepository,
            $this->imageService,
            $this->inventoryService
        );
    }

    public function test_can_get_product_by_slug(): void
    {
        $product = Product::factory()->make(['slug' => 'test-product']);

        $this->productRepository
            ->shouldReceive('with')
            ->once()
            ->andReturnSelf();

        $this->productRepository
            ->shouldReceive('findBySlug')
            ->with('test-product')
            ->once()
            ->andReturn($product);

        $result = $this->productService->getProductBySlug('test-product');

        $this->assertEquals($product, $result);
    }

    public function test_can_create_product(): void
    {
        $productDTO = new ProductDTO(
            name: 'Test Product',
            slug: 'test-product',
            sku: 'TEST-001',
            description: 'Test description',
            shortDescription: 'Short desc',
            price: 100000,
            comparePrice: null,
            weight: 500,
            dimensions: null,
            status: 'active',
            isFeatured: false,
            metaData: null
        );

        $product = Product::factory()->make($productDTO->toArray());

        $this->productRepository
            ->shouldReceive('create')
            ->with($productDTO->toArray())
            ->once()
            ->andReturn($product);

        $this->inventoryService
            ->shouldReceive('createInventory')
            ->with($product->id, null, 0)
            ->once();

        $product->shouldReceive('load')
            ->with(['categories', 'images', 'variants.inventory'])
            ->once()
            ->andReturnSelf();

        $result = $this->productService->createProduct($productDTO);

        $this->assertEquals($product, $result);
    }

    public function test_can_check_product_availability(): void
    {
        $product = Product::factory()->make();

        $this->inventoryService
            ->shouldReceive('checkAvailability')
            ->with($product->id, null, 1)
            ->once()
            ->andReturn(true);

        $result = $this->productService->checkProductAvailability($product, null, 1);

        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}