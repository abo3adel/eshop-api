<?php

use App\Category;
use App\Http\Controllers\ProductController;
use App\Product;
use Illuminate\Support\Arr;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ProductControllerTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE_URL = '/products/';

    /**
     * @dataProvider loadingAllProductsDataProvider
     */
    public function testLoadingAllProducts(string $segment)
    {
        // $this->withoutExceptionHandling();
        $this->passportSignIn();

        $this->get(self::BASE_URL . $segment)
            ->seeStatusCode(200)
            ->seeJsonContains(
                ['current_page' => 1, 'per_page' => ProductController::PER_PAGE]
            );

        $this->get(self::BASE_URL . $segment . '?page=2')
            ->seeStatusCode(200)
            ->seeJsonContains(
                ['current_page' => 2]
            );
    }

    /**
     * @dataProvider loadingAllProductsDataProvider
     */
    public function testLoadingAllProductsIdsWithSettingPerPage(string $segment)
    {
        $this->passportSignIn();

        $this->get(self::BASE_URL . $segment . '/20')
            ->seeStatusCode(200)
            ->seeJsonContains(
                ['current_page' => 1, 'per_page' => 20]
            );
    }

    public function testshowingProductWithSlug()
    {
        $this->passportSignIn();

        $p = Product::find(random_int(1, 1500));
        unset($p->rates);

        $this->get(self::BASE_URL . $p->slug)
            ->seeStatusCode(200)
            ->seeJson($p->toArray());
    }

    public function testLoadingProductListBySubCategorySlug()
    {
        $this->passportSignIn();

        $sub = Category::whereNotNull('category_id')
            ->limit(1)
            ->get('slug')[0];

        $this->get(self::BASE_URL . 'sub/' . $sub->slug)
            ->seeStatusCode(200)
            ->seeJsonContains(
                ['current_page' => 1, 'per_page' => ProductController::PER_PAGE]
            );

        $this->get(self::BASE_URL . 'sub/' . $sub->slug . '?page=2')
            ->seeStatusCode(200)
            ->seeJsonContains(
                ['current_page' => 2]
            );

        $this->get(self::BASE_URL . 'sub/' . $sub->slug . '/30?page=2')
            ->seeStatusCode(200)
            ->seeJsonContains(
                ['current_page' => 2, 'per_page' => 30]
            );
    }

    public function testLoadingListOfProductsByIds()
    {
        $this->passportSignIn();

        $products = Product::all();

        $ids = Arr::random($products->pluck('id')->toArray(), 10);
        
        // load with rates
        $this->get(
            self::BASE_URL . 'collect/'.implode(',', $ids) . '?rates=1'
        )->seeStatusCode(200)
            ->seeJsonContains((Product::find($ids[0]))->toArray());

        // load without rates
        $this->get(
            self::BASE_URL . 'collect/'.implode(',', $ids)
        )->seeStatusCode(200)
            ->seeJsonContains((Product::without('rates')->find($ids[0]))->toArray());
    }

    public function testLoadingListOfProductsByIdsRequiresIdsListIsLessThanOneThousand()
    {
        // $this->withoutExceptionHandling();
        $this->passportSignIn();

        $products = Product::all();

        $ids = Arr::random($products->pluck('id')->toArray(), 501);
        
        // load with rates
        $this->get(
            self::BASE_URL . 'collect/'.implode(',', $ids) . '?rates=1'
        )->seeStatusCode(413);
    }

    public function loadingAllProductsDataProvider(): array
    {
        return [
            [
                'load Products ids' => 'ids',
                'load products list' => 'list',
            ]
        ];
    }
}