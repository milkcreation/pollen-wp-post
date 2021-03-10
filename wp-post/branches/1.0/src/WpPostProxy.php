<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Psr\Container\ContainerInterface as Container;
use RuntimeException;

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
     * @return WpPostManagerInterface
     */
    public function wpPost(): WpPostManagerInterface
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

        return $this->wpPostManager;
    }

    /**
     * Définition du gestionnaire de posts Wordpress.
     *
     * @param WpPostManagerInterface $wpPostManager
     *
     * @return static
     */
    public function setWpPostManager(WpPostManagerInterface $wpPostManager): self
    {
        $this->wpPostManager = $wpPostManager;

        return $this;
    }
}
