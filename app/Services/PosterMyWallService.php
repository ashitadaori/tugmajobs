<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class PosterMyWallService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->baseUrl = config('services.postermywall.base_url') ?? 'https://api.postermywall.com';
        $this->apiKey = config('services.postermywall.api_key') ?? '';
        $this->clientId = config('services.postermywall.client_id');
        $this->clientSecret = config('services.postermywall.client_secret');
    }

    /**
     * Get OAuth access token for API requests
     */
    protected function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $cacheKey = 'postermywall_access_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? null;
                $expiresIn = $data['expires_in'] ?? 3600;

                if ($token) {
                    Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 60));
                    $this->accessToken = $token;
                    return $token;
                }
            }

            Log::warning('PosterMyWall: Failed to get access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Exception $e) {
            Log::error('PosterMyWall: Error getting access token', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Make authenticated API request
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $headers = [
            'Accept' => 'application/json',
        ];

        // Use API key or OAuth token
        if ($this->apiKey) {
            $headers['X-API-Key'] = $this->apiKey;
        } else {
            $token = $this->getAccessToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }
        }

        try {
            $http = Http::withHeaders($headers)->timeout(30);

            $response = match (strtoupper($method)) {
                'GET' => $http->get($url, $data),
                'POST' => $http->post($url, $data),
                'PUT' => $http->put($url, $data),
                'DELETE' => $http->delete($url, $data),
                default => throw new Exception("Unsupported HTTP method: {$method}"),
            };

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::warning('PosterMyWall API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'API request failed',
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error('PosterMyWall API exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search templates by category or keyword
     */
    public function searchTemplates(string $query = '', array $filters = []): array
    {
        $params = array_merge([
            'q' => $query,
            'category' => $filters['category'] ?? 'hiring',
            'page' => $filters['page'] ?? 1,
            'per_page' => $filters['per_page'] ?? 20,
            'sort' => $filters['sort'] ?? 'popular',
        ], $filters);

        return $this->request('GET', '/v1/templates', $params);
    }

    /**
     * Get template categories
     */
    public function getCategories(): array
    {
        $cacheKey = 'postermywall_categories';

        if (Cache::has($cacheKey)) {
            return [
                'success' => true,
                'data' => Cache::get($cacheKey),
            ];
        }

        $result = $this->request('GET', '/v1/categories');

        if ($result['success'] && isset($result['data'])) {
            Cache::put($cacheKey, $result['data'], now()->addHours(24));
        }

        return $result;
    }

    /**
     * Get a specific template by ID
     */
    public function getTemplate(string $templateId): array
    {
        return $this->request('GET', "/v1/templates/{$templateId}");
    }

    /**
     * Get template preview/thumbnail URL
     */
    public function getTemplatePreview(string $templateId, string $size = 'medium'): array
    {
        return $this->request('GET', "/v1/templates/{$templateId}/preview", [
            'size' => $size,
        ]);
    }

    /**
     * Create a design from a template
     */
    public function createDesign(string $templateId, array $customizations = []): array
    {
        return $this->request('POST', '/v1/designs', [
            'template_id' => $templateId,
            'customizations' => $customizations,
        ]);
    }

    /**
     * Update an existing design
     */
    public function updateDesign(string $designId, array $customizations): array
    {
        return $this->request('PUT', "/v1/designs/{$designId}", [
            'customizations' => $customizations,
        ]);
    }

    /**
     * Get a design by ID
     */
    public function getDesign(string $designId): array
    {
        return $this->request('GET', "/v1/designs/{$designId}");
    }

    /**
     * Delete a design
     */
    public function deleteDesign(string $designId): array
    {
        return $this->request('DELETE', "/v1/designs/{$designId}");
    }

    /**
     * Export/download a design
     */
    public function exportDesign(string $designId, string $format = 'png', array $options = []): array
    {
        return $this->request('POST', "/v1/designs/{$designId}/export", array_merge([
            'format' => $format, // png, jpg, pdf
            'quality' => $options['quality'] ?? 'high',
            'width' => $options['width'] ?? null,
            'height' => $options['height'] ?? null,
        ], $options));
    }

    /**
     * Get design download URL
     */
    public function getDesignDownloadUrl(string $designId, string $format = 'png'): array
    {
        return $this->request('GET', "/v1/designs/{$designId}/download", [
            'format' => $format,
        ]);
    }

    /**
     * Get user's saved designs
     */
    public function getUserDesigns(int $page = 1, int $perPage = 20): array
    {
        return $this->request('GET', '/v1/user/designs', [
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Duplicate a design
     */
    public function duplicateDesign(string $designId): array
    {
        return $this->request('POST', "/v1/designs/{$designId}/duplicate");
    }

    /**
     * Get hiring/job-related templates specifically
     */
    public function getHiringTemplates(int $page = 1, int $perPage = 20): array
    {
        return $this->searchTemplates('hiring', [
            'category' => 'business',
            'subcategory' => 'hiring',
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Get the editor embed URL for a design
     */
    public function getEditorUrl(string $designId, array $options = []): string
    {
        $params = http_build_query(array_merge([
            'design_id' => $designId,
            'api_key' => $this->apiKey,
            'callback_url' => $options['callback_url'] ?? route('employer.posters.callback'),
        ], $options));

        return "{$this->baseUrl}/editor?{$params}";
    }

    /**
     * Verify API connection and credentials
     */
    public function verifyConnection(): array
    {
        $result = $this->request('GET', '/v1/user/profile');

        return [
            'connected' => $result['success'],
            'message' => $result['success'] ? 'Connected successfully' : ($result['error'] ?? 'Connection failed'),
        ];
    }

    /**
     * Check if the service is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) || (!empty($this->clientId) && !empty($this->clientSecret));
    }
}
