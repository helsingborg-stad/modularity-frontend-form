<?php

namespace ModularityFrontendForm\RateLimiter;

use WpService\WpService;

/**
 * Rate limiter to prevent abuse and DoS attacks
 * 
 * This class implements a simple rate limiting mechanism using WordPress transients
 * to track and limit the number of actions (e.g., form submissions) per identifier
 * (typically IP address + user agent) within a time window.
 */
class RateLimiter
{
    // Maximum number of submissions allowed per time window
    private const SUBMISSION_LIMIT = 10;
    
    // Time window in seconds (1 hour)
    private const TIME_WINDOW = 3600;
    
    // Maximum file uploads allowed per time window
    private const FILE_UPLOAD_LIMIT = 20;
    
    public function __construct(private WpService $wpService) {}
    
    /**
     * Check if the identifier has exceeded the rate limit for the given action
     *
     * @param string $identifier Unique identifier (typically IP + user agent hash)
     * @param string $action Action being rate limited (e.g., 'submit_form', 'upload_file')
     * @param int|null $customLimit Optional custom limit for this specific check
     * @return bool True if rate limited (exceeded), false if within limits
     */
    public function isRateLimited(string $identifier, string $action, ?int $customLimit = null): bool
    {
        $transientKey = $this->getTransientKey($identifier, $action);
        $count = $this->wpService->getTransient($transientKey) ?: 0;
        
        $limit = $customLimit ?? $this->getDefaultLimit($action);
        
        if ($count >= $limit) {
            // Log rate limit event for monitoring
            $this->wpService->doAction('modularity_frontend_form_rate_limit_exceeded', [
                'identifier' => $identifier,
                'action' => $action,
                'count' => $count,
                'limit' => $limit
            ]);
            
            return true;
        }
        
        // Increment counter and set expiration
        $this->wpService->setTransient(
            $transientKey,
            $count + 1,
            self::TIME_WINDOW
        );
        
        return false;
    }
    
    /**
     * Get the default limit for a given action
     *
     * @param string $action The action to get the limit for
     * @return int The rate limit
     */
    private function getDefaultLimit(string $action): int
    {
        return match($action) {
            'submit_form' => self::SUBMISSION_LIMIT,
            'upload_file' => self::FILE_UPLOAD_LIMIT,
            default => self::SUBMISSION_LIMIT
        };
    }
    
    /**
     * Generate a transient key for rate limiting
     *
     * @param string $identifier Unique identifier
     * @param string $action Action being rate limited
     * @return string Transient key
     */
    private function getTransientKey(string $identifier, string $action): string
    {
        // Use WordPress transient naming conventions
        // Max 172 characters for transient keys
        return 'mff_rl_' . substr(md5($identifier . $action), 0, 32);
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
    public function getRateLimitIdentifier(): string
    {
        // Get IP address - use WordPress helper to respect proxy headers
        $ipAddress = $this->getClientIp();
        
        // Get user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Combine and hash for privacy
        return md5($ipAddress . $userAgent);
    }
    
    /**
     * Get the client IP address, respecting proxy headers
     *
     * @return string IP address
     */
    private function getClientIp(): string
    {
        // Try to get real IP if behind proxy
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // X-Forwarded-For can contain multiple IPs, get the first one
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        // Validate IP address
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Reset rate limit for an identifier and action (admin use only)
     *
     * @param string $identifier Unique identifier
     * @param string $action Action to reset
     * @return bool True if successfully reset
     */
    public function resetRateLimit(string $identifier, string $action): bool
    {
        $transientKey = $this->getTransientKey($identifier, $action);
        return $this->wpService->deleteTransient($transientKey);
    }
    
    /**
     * Get current count for an identifier and action (admin/monitoring use)
     *
     * @param string $identifier Unique identifier
     * @param string $action Action to check
     * @return int Current count
     */
    public function getCurrentCount(string $identifier, string $action): int
    {
        $transientKey = $this->getTransientKey($identifier, $action);
        return $this->wpService->getTransient($transientKey) ?: 0;
    }
}
