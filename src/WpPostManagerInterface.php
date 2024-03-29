<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Pagination\Adapters\WpQueryPaginatorInterface;
use Pollen\Support\Concerns\BootableTraitInterface;
use Pollen\Support\Concerns\ConfigBagAwareTraitInterface;
use Pollen\Support\Proxy\ContainerProxyInterface;
use WP_Query;
use WP_Post;

interface WpPostManagerInterface extends BootableTraitInterface, ConfigBagAwareTraitInterface, ContainerProxyInterface
{
    /**
     * Chargement.
     *
     * @return static
     */
    public function boot(): WpPostManagerInterface;

    /**
     * Liste des instances de posts courants ou associés à une requête WP_Query ou associés à une liste d'arguments.
     *
     * @param WP_Query|array|null $query
     *
     * @return WpPostQueryInterface[]|array
     */
    public function fetch($query = null): array;

    /**
     * Instance du post courant ou associé à une définition.
     *
     * @param string|int|WP_Post|null $post
     *
     * @return WpPostQueryInterface|null
     */
    public function get($post = null): ?WpPostQueryInterface;

    /**
     * Récupération d'une instance de type de post.
     *
     * @param string $name
     *
     * @return WpPostTypeInterface|null
     */
    public function getType(string $name): ?WpPostTypeInterface;

    /**
     * Récupération de l'instance de pagination de la dernière requête de récupération d'une liste d'éléments.
     *
     * @return WpQueryPaginatorInterface|null
     */
    public function paginator(): ?WpQueryPaginatorInterface;

    /**
     * Instance du gestionnaire de type de post.
     *
     * @return WpPostTypeManagerInterface
     */
    public function postTypeManager(): WpPostTypeManagerInterface;

    /**
     * Déclaration d'un type de post.
     *
     * @param string $name
     * @param array|WpPostTypeInterface $postTypeDef
     *
     * @return WpPostTypeInterface|null
     */
    public function registerType(string $name, $postTypeDef = []): ?WpPostTypeInterface;
}