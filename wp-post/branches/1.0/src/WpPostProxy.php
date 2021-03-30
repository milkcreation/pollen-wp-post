<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Psr\Container\ContainerInterface as Container;
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
            $container = method_exists($this, 'getContainer') ? $this->getContainer() : null;

            if ($container instanceof Container && $container->has(WpPostManagerInterface::class)) {
                $this->wpPostManager = $container->get(WpPostManagerInterface::class);
            } else {
                try {
                    $this->wpPostManager = WpPostManager::getInstance();
                } catch(RuntimeException $e) {
                    $this->wpPostManager = new WpPostManager();
                }
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

        return $this->wpPostManager->post($query);
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
