<?php

declare(strict_types=1);

/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette;

/**
 * N+1 query detector for association lazy-loading.
 *
 * Tracks how many times the same (ownerClass, associationName) pair triggers a
 * live DB lookup within a single detection window. When the count crosses the
 * configured threshold, a warning is emitted (via trigger_error / a custom
 * handler) so developers can add eager-loading before shipping to production.
 *
 * Usage:
 *   N1Detector::enable();              // start tracking
 *   N1Detector::disable();             // stop tracking
 *   N1Detector::reset();               // clear hit counters
 *   N1Detector::getHits();             // ['UserModel.posts' => 3, ...]
 *   N1Detector::setThreshold(2);       // warn after 2 repeated loads (default: 2)
 *   N1Detector::onDetect(fn($key, $count) => ...); // custom handler instead of trigger_error
 *
 * Detection is disabled by default — enabling it in production is safe but adds
 * a small overhead; it is intended for development and test environments.
 *
 * @package \Roulette
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class N1Detector
{
    private static bool $enabled = false;

    private static int $threshold = 2;

    /** @var array<string,int> hit counter keyed by "OwnerClass.associationName" */
    private static array $hits = [];

    /** @var callable|null  Custom handler; receives ($key, $count). Null → trigger_error. */
    private static mixed $handler = null;

    public static function enable(): void
    {
        self::$enabled = true;
    }

    public static function disable(): void
    {
        self::$enabled = false;
    }

    public static function isEnabled(): bool
    {
        return self::$enabled;
    }

    public static function reset(): void
    {
        self::$hits = [];
    }

    /**
     * Full reset for long-running processes — clears hits, handler, and disables detection.
     * Call between requests when using Swoole, Octane, or RoadRunner.
     */
    public static function fullReset(): void
    {
        self::$hits      = [];
        self::$handler   = null;
        self::$enabled   = false;
        self::$threshold = 2;
    }

    public static function setThreshold(int $n): void
    {
        self::$threshold = max(1, $n);
    }

    public static function getThreshold(): int
    {
        return self::$threshold;
    }

    /** @return array<string,int> */
    public static function getHits(): array
    {
        return self::$hits;
    }

    /**
     * Register a custom N+1 handler.
     * The callable receives two arguments: $key (string) and $count (int).
     * Pass null to revert to the default trigger_error behaviour.
     */
    public static function onDetect(?callable $handler): void
    {
        self::$handler = $handler;
    }

    /**
     * Record one association load.
     * Called by AssociationAbstract::loadRelation() when enabled.
     *
     * @param  string $ownerClass      Fully-qualified class of the record being queried.
     * @param  string $associationName Name of the association (e.g. 'posts').
     */
    public static function record(string $ownerClass, string $associationName): void
    {
        if (!self::$enabled) return;

        $key = $ownerClass . '.' . $associationName;
        self::$hits[$key] = (self::$hits[$key] ?? 0) + 1;

        if (self::$hits[$key] >= self::$threshold) {
            self::warn($key, self::$hits[$key]);
        }
    }

    private static function warn(string $key, int $count): void
    {
        if (is_callable(self::$handler)) {
            (self::$handler)($key, $count);
            return;
        }

        trigger_error(
            "N+1 detected: $key loaded $count times. Consider eager-loading this association.",
            E_USER_WARNING
        );
    }
}
