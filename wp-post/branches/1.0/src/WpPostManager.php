<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Pagination\Adapters\WpQueryPaginatorInterface;
use Pollen\Support\Concerns\BootableTrait;
use Pollen\Support\Concerns\ConfigBagAwareTrait;
use Pollen\Support\Proxy\ContainerProxy;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;
use WP_Post_Type;

class WpPostManager implements WpPostManagerInterface
{
    use BootableTrait;
    use ConfigBagAwareTrait;
    use ContainerProxy;

    /**
     * Instance principale.
     * @var static|null
     */
    private static $instance;

    /**
     * Liste des instances de type de post déclarée.
     * @var WpPostTypeInterface[]|array
     */
    protected $postTypes = [];

    /**
     * @param array $config
     * @param Container|null $container Instance du conteneur d'injection de dépendances.
     *
     * @return void
     */
    public function __construct(array $config = [], ?Container $container = null)
    {
        $this->setConfig($config);

        if ($container !== null) {
            $this->setContainer($container);
        }

        if ($this->config('boot_enabled', true)) {
            $this->boot();
        }

        if (!self::$instance instanceof static) {
            self::$instance = $this;
        }
    }

    /**
     * Récupération de l'instance principale.
     *
     * @return static
     */
    public static function getInstance(): WpPostManagerInterface
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        throw new RuntimeException(sprintf('Unavailable [%s] instance', __CLASS__));
    }

    /**
     * @inheritDoc
     */
    public function boot(): WpPostManagerInterface
    {
        if (!$this->isBooted()) {
            add_action('init', function () {
                global $wp_post_types;

                foreach ($this->postTypes as $name => $postType) {
                    if (!isset($wp_post_types[$name])) {
                        register_post_type($name, $postType->params()->all());
                    }

                    if ($wp_post_types[$name] instanceof WP_Post_Type) {
                        $postType->setWpPostType($wp_post_types[$name]);
                    }

                    if ($taxonomies = $postType->params('taxonomies', [])) {
                        foreach ($taxonomies as $taxonomy) {
                            register_taxonomy_for_object_type($taxonomy, $postType->getName());
                        }
                    }
                }
            }, 11);

            add_action('init', function () {
                global $wp_post_types;

                foreach ($wp_post_types as $name => $attrs) {
                    if (!$this->getType($name)) {
                        $this->registerType($name, get_object_vars($attrs));
                    }
                }
            }, 999999);

            $this->setBooted();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getType(string $name): ?WpPostTypeInterface
    {
        return $this->postTypes[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function paginator(): ?WpQueryPaginatorInterface
    {
        return WpPostQuery::paginator();
    }

    /**
     * @inheritDoc
     */
    public function post($post = null): ?WpPostQueryInterface
    {
        return WpPostQuery::create($post);
    }

    /**
     * @inheritDoc
     */
    public function posts($query = null): array
    {
        return WpPostQuery::fetch($query);
    }

    /**
     * @inheritDoc
     */
    public function registerType(string $name, $args = []): WpPostTypeInterface
    {
        $factory = $args instanceof WpPostTypeInterface ? $args : new WpPostType($name, $args);

        return $this->postTypes[$name] = $factory->setWpPostManager($this)->boot();
    }
}