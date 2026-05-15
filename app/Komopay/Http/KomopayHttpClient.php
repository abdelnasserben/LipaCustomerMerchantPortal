<?php

namespace App\Komopay\Http;

use App\Komopay\Exceptions\AuthException;
use App\Komopay\Exceptions\BusinessException;
use App\Komopay\Exceptions\KomopayException;
use App\Komopay\Exceptions\NetworkException;
use App\Komopay\Exceptions\NotImplementedException;
use App\Komopay\Exceptions\ValidationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

/**
 * Thin wrapper around Laravel's HTTP client that:
 *   - prefixes every URL with the configured base + /api/v1
 *   - applies bearer/api-key headers
 *   - generates correlation ids
 *   - parses ApiResponse / PagedResponse / ApiError envelopes (spec 2.2 / 2.3)
 *   - normalises every backend failure into a typed KomopayException subclass
 *
 * Service classes never touch Http::class directly — they call this client.
 */
class KomopayHttpClient
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $baseUrl,
        private readonly string $apiPrefix,
        private readonly int $timeout,
        private readonly string $apiKey,
    ) {}

    public function get(string $path, array $query = [], array $options = []): ApiResponse
    {
        return $this->send('GET', $path, ['query' => $query], $options);
    }

    public function post(string $path, array $body = [], array $options = []): ApiResponse
    {
        return $this->send('POST', $path, ['json' => $body], $options);
    }

    public function put(string $path, array $body = [], array $options = []): ApiResponse
    {
        return $this->send('PUT', $path, ['json' => $body], $options);
    }

    public function patch(string $path, array $body = [], array $options = []): ApiResponse
    {
        return $this->send('PATCH', $path, ['json' => $body], $options);
    }

    public function delete(string $path, array $body = [], array $options = []): ApiResponse
    {
        return $this->send('DELETE', $path, ['json' => $body], $options);
    }

    /**
     * @param array{query?: array, json?: array} $payload
     * @param array{
     *   bearer?: string|null,
     *   idempotencyKey?: string|null,
     *   correlationId?: string|null,
     *   headers?: array<string,string>,
     * } $options
     */
    private function send(string $method, string $path, array $payload, array $options): ApiResponse
    {
        $url = $this->baseUrl . $this->apiPrefix . '/' . ltrim($path, '/');

        $request = $this->http
            ->timeout($this->timeout)
            ->acceptJson()
            ->asJson()
            ->withHeaders($this->defaultHeaders($options));

        if (!empty($options['bearer'])) {
            $request = $request->withToken($options['bearer']);
        }

        try {
            $response = $this->dispatch($request, $method, $url, $payload);
        } catch (ConnectionException $e) {
            throw new NetworkException(
                'Could not reach KomoPay backend: ' . $e->getMessage(),
                'NETWORK_ERROR',
                0,
                [],
                null,
                $e,
            );
        } catch (\Throwable $e) {
            if ($e instanceof KomopayException) {
                throw $e;
            }
            throw new NetworkException(
                'Unexpected transport failure: ' . $e->getMessage(),
                'NETWORK_ERROR',
                0,
                [],
                null,
                $e,
            );
        }

        if ($response->successful() || $response->status() === 202) {
            return $this->parseSuccess($response);
        }

        throw $this->mapError($response);
    }

    private function dispatch(PendingRequest $request, string $method, string $url, array $payload): Response
    {
        return match ($method) {
            'GET'    => $request->get($url, $payload['query'] ?? []),
            'POST'   => $request->post($url, $payload['json'] ?? []),
            'PUT'    => $request->put($url, $payload['json'] ?? []),
            'PATCH'  => $request->patch($url, $payload['json'] ?? []),
            'DELETE' => $request->delete($url, $payload['json'] ?? []),
            default  => throw new NetworkException("Unsupported method: {$method}"),
        };
    }

    private function defaultHeaders(array $options): array
    {
        $headers = [
            'X-Correlation-Id' => $options['correlationId'] ?? (string) Str::uuid(),
        ];

        if (!empty($options['idempotencyKey'])) {
            $headers['Idempotency-Key'] = $options['idempotencyKey'];
        }

        if ($this->apiKey !== '') {
            $headers['X-Api-Key'] = $this->apiKey;
        }

        foreach ($options['headers'] ?? [] as $k => $v) {
            $headers[$k] = $v;
        }

        return $headers;
    }

    private function parseSuccess(Response $response): ApiResponse
    {
        $status = $response->status();

        // 204 No Content — no envelope.
        if ($status === 204) {
            return new ApiResponse(null, null, null, $status, $response->headers());
        }

        $body = $response->json();
        if (!is_array($body)) {
            throw new NetworkException("Malformed JSON body (HTTP {$status}).", 'MALFORMED_RESPONSE', $status);
        }

        // PagedResponse — spec 2.2.
        if (array_key_exists('pagination', $body)) {
            return new ApiResponse(
                data: $body['data'] ?? [],
                pagination: $body['pagination'] ?? null,
                timestamp: $body['timestamp'] ?? null,
                httpStatus: $status,
                headers: $response->headers(),
            );
        }

        // ApiResponse<T> — spec 2.2.
        return new ApiResponse(
            data: $body['data'] ?? null,
            pagination: null,
            timestamp: $body['timestamp'] ?? null,
            httpStatus: $status,
            headers: $response->headers(),
        );
    }

    private function mapError(Response $response): KomopayException
    {
        $status = $response->status();
        $body = $response->json();
        $error = is_array($body) ? ($body['error'] ?? $body) : [];

        $code          = is_array($error) ? ($error['code'] ?? 'UNKNOWN') : 'UNKNOWN';
        $message       = is_array($error) ? ($error['message'] ?? 'Request failed') : 'Request failed';
        $details       = is_array($error) ? (array) ($error['details'] ?? []) : [];
        $correlationId = is_array($error) ? ($error['correlationId'] ?? null) : null;

        // Spec 2.3 / section 12 — pick the right typed exception.
        return match (true) {
            $status === 501 => new NotImplementedException($message, $code, $status, $details, $correlationId),
            $status === 401 || $status === 403 => new AuthException($message, $code, $status, $details, $correlationId),
            $status === 400 => new ValidationException($message, $code, $status, $details, $correlationId),
            $status === 404 || $status === 409 || $status === 422 || $status === 429
                => new BusinessException($message, $code, $status, $details, $correlationId),
            default => new KomopayException($message, $code, $status, $details, $correlationId),
        };
    }
}
