<?php

declare(strict_types=1);

namespace Pollen\WpPost;

interface WpPostProxyInterface
{
    /**
     * Instance du gestionnaire de posts Wordpress.
     *
     * @return WpPostManagerInterface
     */
    public function wpPost(): WpPostManagerInterface;

    /**
     * Définition du gestionnaire de posts Wordpress.
     *
     * @param WpPostManagerInterface $wpPostManager
     *
     * @return static
     */
    public function setWpPostManager(WpPostManagerInterface $wpPostManager): self;
}
