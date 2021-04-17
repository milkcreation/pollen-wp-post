<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Support\Exception\ProxyInvalidArgumentException;
use Pollen\Support\StaticProxy;
use RuntimeException;
use WP_Query;
use WP_Post;

/**
 * @see \Pollen\WpPost\WpPostProxyInterface
 */
trait WpPostProxy
{
    /**
     * Instance du gestionnaire de posts Wordpress.
     * @var WpPostManagerInterface
     */
    private $wpPostManager;

    /**
     * Instance du gestionnaire de posts Wordpress.
     *
     * @param true|string|int|WP_Post|WP_Query|array|null $query
     *
     * @return WpPostManagerInterface|WpPostQueryInterface|WpPostQueryInterface[]|array
     */
    public function wpPost($query = null)
    {
        if ($this->wpPostManager === null) {
            try {
                $this->wpPostManager = WpPostManager::getInstance();
            } catch (RuntimeException $e) {
                $this->wpPostManager = StaticProxy::getProxyInstance(
                    WpPostManagerInterface::class,
                    WpPostManager::class,
                    method_exists($this, 'getContainer') ? $this->getContainer() : null
                );
            }
        }

        if ($query === null) {
            return $this->wpPostManager;
        }

        if (is_array($query) || ($query instanceof WP_Query)) {
            return $this->wpPostManager->posts($query);
        }

        if ($query === true) {
            $query = null;

            global $wp_query;

            if ($wp_query && !$wp_query->is_singular) {
                return $this->wpPostManager->posts();
            }
        }

        if ($post = $this->wpPostManager->post($query)) {
            return $post;
        }

        throw new ProxyInvalidArgumentException('WpPostQueried is unavailable');
    }

    /**
     * Définition du gestionnaire de posts Wordpress.
     *
     * @param WpPostManagerInterface $wpPostManager
     *
     * @return void
     */
    public function setWpPostManager(WpPostManagerInterface $wpPostManager): void
    {
        $this->wpPostManager = $wpPostManager;
    }
}
