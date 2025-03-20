<?php

namespace Tests\Feature;

use App\Services\NYTBestSellersHistoryApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;


class NytBestSellersApiTest extends TestCase
{

    protected string $apiKey;
    protected string $apiUrl;

    public function setUp(): void
    {
        parent::setUp();
        $this->apiUrl = config('services.nyt.api_url');
        $this->apiKey = config('services.nyt.api_key');
    }

    /**
     * @dataProvider successValidationProvider
     *
     */
    public function test_success_request(array $requestParams, array $expectedResponse): void
    {
        $mockBestSellersService = \Mockery::mock(NYTBestSellersHistoryApiService::class);
        $mockBestSellersService->shouldReceive('getNytBestSellersHistory')
            ->once()
            ->with($requestParams)
            ->andReturn($expectedResponse);

        $this->app->instance(NYTBestSellersHistoryApiService::class, $mockBestSellersService);

        $response = $this->getJson('api/v1/nyt-bestsellers-history?' . http_build_query($requestParams));
        \Mockery::close();

        $response->assertStatus(200)->assertJson($expectedResponse);

    }

    public static function successValidationProvider(): array
    {
        return [
            [
                ['author' => 'Gary Vaynerchuk'],
                [
                    'results' => [
                        [
                            'title'  => '#ASKGARYVEE"',
                            'author' => 'Gary Vaynerchuk',
                            'isbns'   => [['isbn10' => '0062273124', 'isbn13' => '9780062273123']]
                        ],
                        [
                            'title'  => 'CRUSHING IT!"',
                            'author' => 'Gary Vaynerchuk',
                            'isbns'   => [['isbn10' => '0062674676', 'isbn13' => '9780062674678']]
                        ]
                    ]
                ]
            ],
            [
                ['title' => 'CRUSH'],
                [
                    'results' => [
                        [
                            'title'  => 'CRUSH IT!',
                            'author' => 'Gary Vaynerchuk',
                            'isbns'   => []
                        ],
                        [
                            'title'  => 'CRUSHING IT!"',
                            'author' => 'Gary Vaynerchuk',
                            'isbns'   => [['isbn10' => '0062674676', 'isbn13' => '9780062674678']]
                        ]
                    ]
                ]
            ],
            [
                ['offset' => 60],
                [
                    'results' => [
                    ]
                ]
            ],
        ];
    }



    /**
     * @dataProvider failedValidationProvider
     *
     */
    public function test_failed_validation(array $params, int $status, array $json):void
    {
        $response = $this->getJson('api/v1/nyt-bestsellers-history?' . http_build_query($params));

        $response->assertStatus($status)->assertJson($json);
    }

    public static function failedValidationProvider():array
    {
        return [
            [
                ['author' => str_repeat('ABCdef', 260)], 422,
                [
                    'message' => 'The author field must not be greater than 255 characters.',
                    'errors'  => ['author' => ['The author field must not be greater than 255 characters.']
                    ]
                ]
            ],
            [
                ['isbn' => '039917857A'], 422,
                [
                    'message' => 'The isbn.0 field format is invalid.',
                    'errors'  => ['isbn.0' => ['The isbn.0 field format is invalid.']
                    ]
                ]
            ],
            [
                ['isbn' => '03991785701'], 422,
                [
                    'message' => 'The isbn.0 field format is invalid.',
                    'errors'  => ['isbn.0' => ['The isbn.0 field format is invalid.']
                    ]
                ]
            ],
            [
                ['isbn' => '0399178570;9780399178573A'], 422,
                [
                    'message' => 'The isbn.1 field format is invalid.',
                    'errors'  => ['isbn.1' => ['The isbn.1 field format is invalid.']
                    ]
                ]
            ],
            [
                ['title' => str_repeat('ABCdef', 401)], 422,
                [
                    'message' => 'The title field must not be greater than 400 characters.',
                    'errors'  => ['title' => ['The title field must not be greater than 400 characters.']
                    ]
                ]
            ],
            [
                ['offset' => '15'], 422,
                [
                    'message' => 'The offset field must be a multiple of 20.',
                    'errors'  => ['offset' => ['The offset field must be a multiple of 20.']
                    ]
                ]
            ],
            [
                ['isbn' => '0399178570;9780399178573A', 'offset' => '-1'], 422,
                [
                    'message' => 'The offset field must be at least 0. (and 2 more errors)',
                    'errors'  => [
                        'offset' => [
                            'The offset field must be at least 0.',
                            'The offset field must be a multiple of 20.'
                            ],
                        'isbn.1' => ['The isbn.1 field format is invalid.']
                    ]
                ]
            ],
        ];
    }
}
