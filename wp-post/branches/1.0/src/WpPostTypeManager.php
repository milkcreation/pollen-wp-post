<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Support\Proxy\ContainerProxy;
use Psr\Container\ContainerInterface as Container;

class WpPostTypeManager implements WpPostTypeManagerInterface
{
    use ContainerProxy;

    /**
     * Instance du gestionnaire des post Wordpress.
     * @var WpPostManagerInterface
     */
    protected $wpPost;

    /**
     * Liste des types de post déclarés.
     * @var WpPostTypeInterface[]|array
     */
    public $postTypes = [];

    /**
     * @param WpPostManagerInterface $wpPost
     * @param Container|null $container
     */
    public function __construct(WpPostManagerInterface $wpPost, ?Container $container = null)
    {
        $this->wpPost = $wpPost;

        if ($container !== null) {
            $this->setContainer($container);
        }
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->postTypes;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): ?WpPostTypeInterface
    {
        return $this->postTypes[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function register(string $name, $postTypeDef): WpPostTypeInterface
    {
        if (!$postTypeDef instanceof WpPostTypeInterface) {
            $postType = new WpPostType($name, is_array($postTypeDef) ? $postTypeDef : []);
        } else {
            $postType = $postTypeDef;
        }
        $this->postTypes[$name] = $postType;

        return $postType;
    }
}