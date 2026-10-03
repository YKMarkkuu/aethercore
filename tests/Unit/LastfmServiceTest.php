<?php

namespace Tests\Unit;

use App\Services\LastfmService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LastfmServiceTest extends TestCase
{
    public function test_top_artists_are_returned_and_normalized(): void
    {
        config()->set('services.lastfm.api_key', 'test-key');

        Http::fake([
            'https://ws.audioscrobbler.com/2.0/*' => Http::response([
                'topartists' => [
                    'artist' => [
                        [
                            'name' => 'The Weeknd',
                            'playcount' => 42,
                            'image' => [
                                ['#text' => 'https://lastfm.freetls.fastly.net/i/u/300x300/abc.jpg', 'size' => 'large'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $service = new LastfmService();
        $artists = $service->getTopArtists('demo-user', 8, 'overall');

        $this->assertCount(1, $artists);
        $this->assertSame('The Weeknd', $artists[0]['name']);
        $this->assertSame(42, $artists[0]['playcount']);
    }
}
