<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Support\Proxy\ContainerProxyInterface;

interface WpPostTypeManagerInterface extends ContainerProxyInterface
{
    /**
     * Récupération de la liste des instance de type de post déclarés.
     *
     * @return WpPostTypeInterface[]|array
     */
    public function all(): array;

    /**
     * Récupération d'une instance de type de post déclaré.
     *
     * @param string $name.
     *
     * @return WpPostTypeInterface|null
     */
    public function get(string $name): ?WpPostTypeInterface;

    /**
     * Déclaration d'un type de post.
     *
     * @param string $name
     * @param WpPostTypeInterface|array $postTypeDef
     *
     * @return WpPostTypeInterface
     */
    public function register(string $name, $postTypeDef): WpPostTypeInterface;
}