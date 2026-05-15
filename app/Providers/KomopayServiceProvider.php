<?php

namespace App\Providers;

use App\Komopay\Contracts\CustomerApi;
use App\Komopay\Contracts\CustomerAuthApi;
use App\Komopay\Contracts\MerchantApi;
use App\Komopay\Contracts\MerchantAuthApi;
use App\Komopay\Http\KomopayHttpClient;
use App\Komopay\Presenters\CardPresenter;
use App\Komopay\Presenters\CounterpartyDirectory;
use App\Komopay\Presenters\MockCounterpartyDirectory;
use App\Komopay\Presenters\NullCounterpartyDirectory;
use App\Komopay\Presenters\TerminalPresenter;
use App\Komopay\Presenters\TransactionPresenter;
use App\Komopay\Services\Auth\HttpCustomerAuthApi;
use App\Komopay\Services\Auth\HttpMerchantAuthApi;
use App\Komopay\Services\Auth\MockCustomerAuthApi;
use App\Komopay\Services\Auth\MockMerchantAuthApi;
use App\Komopay\Services\Customer\HttpCustomerApi;
use App\Komopay\Services\Customer\MockCustomerApi;
use App\Komopay\Services\Merchant\HttpMerchantApi;
use App\Komopay\Services\Merchant\MockMerchantApi;
use App\Komopay\Support\TokenStore;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the KomoPay abstraction layer.
 *
 * The same `CustomerApi` / `MerchantApi` / `*AuthApi` interfaces are
 * resolved to either the Mock* or Http* implementation based on
 * `config('komopay.use_mock')` (env: KOMOPAY_USE_MOCK_API).
 *
 * Switching modes requires zero changes in Livewire / Blade code.
 */
class KomopayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Shared HTTP client — only ever instantiated when something resolves it.
        $this->app->singleton(KomopayHttpClient::class, function (Application $app): KomopayHttpClient {
            return new KomopayHttpClient(
                http:      $app->make(HttpFactory::class),
                baseUrl:   (string) config('komopay.base_url'),
                apiPrefix: (string) config('komopay.api_prefix'),
                timeout:   (int) config('komopay.timeout'),
                apiKey:    (string) config('komopay.api_key'),
            );
        });

        // Per-actor token stores.
        $this->app->bind('komopay.tokens.customer', fn (Application $app) => new TokenStore(
            $app->make('session.store'), (string) config('komopay.session.customer_token_key'),
        ));
        $this->app->bind('komopay.tokens.merchant', fn (Application $app) => new TokenStore(
            $app->make('session.store'), (string) config('komopay.session.merchant_token_key'),
        ));

        // Mock vs real switch — single source of truth.
        $useMock = (bool) config('komopay.use_mock');

        // Customer surface.
        $this->app->bind(CustomerAuthApi::class, $useMock
            ? MockCustomerAuthApi::class
            : fn (Application $app) => new HttpCustomerAuthApi($app->make(KomopayHttpClient::class)),
        );
        $this->app->bind(CustomerApi::class, $useMock
            ? MockCustomerApi::class
            : fn (Application $app) => new HttpCustomerApi(
                $app->make(KomopayHttpClient::class),
                $app->make('komopay.tokens.customer'),
                $app->make(CustomerAuthApi::class),
            ),
        );

        // Merchant surface.
        $this->app->bind(MerchantAuthApi::class, $useMock
            ? MockMerchantAuthApi::class
            : fn (Application $app) => new HttpMerchantAuthApi($app->make(KomopayHttpClient::class)),
        );
        $this->app->bind(MerchantApi::class, $useMock
            ? MockMerchantApi::class
            : fn (Application $app) => new HttpMerchantApi(
                $app->make(KomopayHttpClient::class),
                $app->make('komopay.tokens.merchant'),
            ),
        );

        // UI presenter directory — mock has labels, real-API has none.
        $this->app->bind(CounterpartyDirectory::class, $useMock
            ? MockCounterpartyDirectory::class
            : NullCounterpartyDirectory::class,
        );

        $this->app->bind(TransactionPresenter::class, fn (Application $app) =>
            new TransactionPresenter($app->make(CounterpartyDirectory::class)),
        );

        $this->app->bind(CardPresenter::class, fn (Application $app) =>
            new CardPresenter($app->make(CounterpartyDirectory::class)),
        );

        $this->app->bind(TerminalPresenter::class, fn (Application $app) =>
            new TerminalPresenter($app->make(CounterpartyDirectory::class)),
        );
    }
}
