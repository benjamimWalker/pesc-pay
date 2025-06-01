<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function fakeSuccessfulCalls(): void
{
    Config::set('services.devi_tools_authorization_url', 'http://fake-auth-url.test');
    Config::set('services.devi_tools_notifications_url', 'http://fake-notify-url.test');

    Http::fake([
        'http://fake-auth-url.test' => Http::response([]),
        'http://fake-notify-url.test' => Http::response([]),
    ]);
}

function fakeFailedCalls(): void
{
    Config::set('services.devi_tools_authorization_url', 'http://fake-auth-url.test');
    Config::set('services.devi_tools_notifications_url', 'http://fake-notify-url.test');

    Http::fake([
        'http://fake-auth-url.test' => Http::response([], 403),
        'http://fake-notify-url.test' => Http::response([], 500),
    ]);
}
