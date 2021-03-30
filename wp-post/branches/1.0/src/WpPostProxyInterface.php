<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use WP_Post;
use Wp_Query;

interface WpPostProxyInterface
{
    /**
     * Instance du gestionnaire de posts Wordpress.
     *
     * @param true|string|int|WP_Post|WP_Query|array|null $query
     *
     * @return WpPostManagerInterface|WpPostQueryInterface|WpPostQueryInterface[]|array
     */
    public function wpPost($query = null);

    /**
     * Définition du gestionnaire de posts Wordpress.
     *
     * @param WpPostManagerInterface $wpPostManager
     *
     * @return void
     */
    public function setWpPostManager(WpPostManagerInterface $wpPostManager): void;
}
