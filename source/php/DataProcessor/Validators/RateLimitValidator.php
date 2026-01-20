<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResult;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResultInterface;
use WP_Error;
use WpService\WpService;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use WP_REST_Request;

/**
 * Rate limit validator to prevent abuse and DoS attacks
 * 
 * This validator implements rate limiting using WordPress object cache
 * to track and limit the number of form submissions per identifier
 * (typically IP address + user agent) within a time window.
 * 
 * This should be applied as the last validator to ensure rate limiting
 * only occurs after all other validations have passed.
 * 
 * Note: Requires a persistent object cache (e.g., Redis, Memcached) to be 
 * configured in WordPress for proper rate limiting across requests.
 */
class RateLimitValidator implements ValidatorInterface
{
    // Maximum number of submissions allowed per time window
    private const SUBMISSION_LIMIT = 10;
    
    // Time window in seconds (1 hour)
    private const TIME_WINDOW = 3600;
    
    // Cache group for rate limiting
    private const CACHE_GROUP = 'modularity_frontend_form_rate_limit';
    
    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private ConfigInterface $config,
        private ModuleConfigInterface $moduleConfigInstance,
        private ValidationResultInterface $validationResult = new ValidationResult()
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate(array $data, WP_REST_Request $request): ?ValidationResultInterface
    {
        $identifier = $this->getRateLimitIdentifier();
        $action = 'submit_form';
        
        if ($this->isRateLimited($identifier, $action)) {
            $this->validationResult->setError(
                new WP_Error(
                    'rate_limit_exceeded',
                    $this->wpService->__('Too many submissions. Please try again later.', 'modularity-frontend-form'),
                    ['status' => 429]
                )
            );
        }
        
        return $this->validationResult;
    }

    /**
     * Check if the identifier has exceeded the rate limit
     *
     * @param string $identifier Unique identifier (typically IP + user agent hash)
     * @param string $action Action being rate limited
     * @return bool True if rate limited (exceeded), false if within limits
     */
    private function isRateLimited(string $identifier, string $action): bool
    {
        $cacheKey = $this->getCacheKey($identifier, $action);
        $cacheData = $this->wpService->wpCacheGet($cacheKey, self::CACHE_GROUP);
        
        // Initialize cache data if not present
        if ($cacheData === false) {
            $cacheData = [
                'count' => 0,
                'expires' => time() + self::TIME_WINDOW
            ];
        }
        
        // Check if the time window has expired, reset if so
        if ($cacheData['expires'] <= time()) {
            $cacheData = [
                'count' => 0,
                'expires' => time() + self::TIME_WINDOW
            ];
        }
        
        // Check if limit exceeded
        if ($cacheData['count'] >= self::SUBMISSION_LIMIT) {
            // Log rate limit event for monitoring
            $this->wpService->doAction('modularity_frontend_form_rate_limit_exceeded', [
                'identifier' => $identifier,
                'action' => $action,
                'count' => $cacheData['count'],
                'limit' => self::SUBMISSION_LIMIT
            ]);
            
            return true;
        }
        
        // Increment counter and store in cache
        $cacheData['count']++;
        $this->wpService->wpCacheSet(
            $cacheKey,
            $cacheData,
            self::CACHE_GROUP,
            self::TIME_WINDOW
        );
        
        return false;
    }

    /**
     * Generate a cache key for rate limiting
     *
     * @param string $identifier Unique identifier
     * @param string $action Action being rate limited
     * @return string Cache key
     */
    private function getCacheKey(string $identifier, string $action): string
    {
        // Create a unique cache key from identifier and action
        return md5($identifier . $action);
    }

    /**
     * Get a unique identifier for rate limiting based on IP and user agent
     * 
     * This combines IP address and user agent to create a more accurate identifier
     * while still being reasonably anonymous. It helps prevent bypassing via user agent
     * rotation while allowing multiple users behind the same NAT.
     *
     * @return string Hashed identifier
     */
    private function getRateLimitIdentifier(): string
    {
        // Get IP address
        $ipAddress = $this->getClientIp();
        
        // Get user agent and sanitize to prevent header injection
        $userAgent = $this->wpService->sanitizeTextField($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
        
        // Combine and hash for privacy
        return md5($ipAddress . $userAgent);
    }

    /**
     * Get the client IP address, respecting proxy headers
     * 
     * WARNING: X-Forwarded-For and X-Real-IP headers can be spoofed.
     * In production, ensure your web server/load balancer strips these headers
     * from untrusted sources and only sets them from trusted proxies.
     *
     * @return string IP address
     */
    private function getClientIp(): string
    {
        // In a hardened environment, you might want to check:
        // - If the server is behind a known proxy (e.g., Cloudflare, AWS LB)
        // - Validate the proxy IP is in a trusted list
        // - Only then trust X-Forwarded-For
        
        // For now, trust proxy headers but validate the IP format
        // TODO: Implement trusted proxy validation for production use
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // X-Forwarded-For can contain multiple IPs, get the first one
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        // Validate IP address format before returning
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        // If validation fails, fall back to REMOTE_ADDR which is trustworthy
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
