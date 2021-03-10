<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Support\Concerns\BootableTrait;
use Pollen\Support\Concerns\ParamsBagAwareTrait;
use Pollen\Translation\LabelsBag;
use WP_Post_Type;
use WP_REST_Controller;

/**
 * @property-read string $label
 * @property-read object $labels
 * @property-read string $description
 * @property-read bool $public
 * @property-read bool $hierarchical
 * @property-read bool $exclude_from_search
 * @property-read bool $publicly_queryable
 * @property-read bool $show_ui
 * @property-read bool $show_in_menu
 * @property-read bool $show_in_nav_menus
 * @property-read bool $show_in_admin_bar
 * @property-read int $menu_position
 * @property-read string $menu_icon
 * @property-read string $capability_type
 * @property-read bool $map_meta_cap
 * @property-read string $register_meta_box_cb
 * @property-read array $taxonomies
 * @property-read bool|string $has_archive
 * @property-read string|bool $query_var
 * @property-read bool $can_export
 * @property-read bool $delete_with_user
 * @property-read bool $_builtin
 * @property-read string $_edit_link
 * @property-read object $cap
 * @property-read array|false $rewrite
 * @property-read array|bool $supports
 * @property-read bool $show_in_rest
 * @property-read string|bool $rest_base
 * @property-read string|bool $rest_controller_class
 * @property-read WP_REST_Controller $rest_controller
 */
class WpPostType implements WpPostTypeInterface
{
    use BootableTrait;
    use ParamsBagAwareTrait;
    use WpPostProxy;

    /**
     * Indicateur d'instanciation.
     * @var boolean
     */
    private $prepared = false;

    /**
     * Instance du gestionnaire d'intitulés.
     * @var LabelsBag|null
     */
    protected $labelBag;

    /**
     * Nom de qualification.
     * @var string
     */
    protected $name = '';

    /**
     * Instance du type de post Wordpress associé.
     * @return WP_Post_Type|null
     */
    protected $wpPostType;

    /**
     * @param string $name
     * @param array $params
     *
     * @return void
     */
    public function __construct(string $name, array $params = [])
    {
        $this->name = $name;
        $this->params($params);
    }

    /**
     * @inheritDoc
     */
    public function boot(): WpPostTypeInterface
    {
        if (!$this->isBooted()) {
            //events()->trigger('post-type.factory.booting', [&$this]);

            $this->parseParams();

            $this->setBooted();
            //events()->trigger('post-type.factory.booted', [&$this]);
        }

        return $this;
    }

    /**
     * Récupération des données de délégation de l'objet WP_POST associé.
     *
     * @param int|string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->wpPost->{$key} ?? null;
    }

    /**
     * Vérification d'une données de délégation de l'objet WP_POST associé.
     *
     * @param int|string $key
     *
     *
     * @return bool
     */
    public function __isset($key): bool
    {
        return isset($this->wpPost->{$key});
    }

    /**
     * Définition d'une donnée de délégation de l'objet WP_POST associé.
     *
     * @param int|string $key
     * @param mixed $value
     *
     *
     * @return void
     */
    public function __set($key, $value): void
    {
    }

    /**
     * Résolution de sortie de la classe sous forme de chaîne de caractère.
     * {@internal Retourne le nom de qualification du type de post.}
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function defaultParams(): array
    {
        return [
            //'label'              => '',
            //'labels'             => '',
            'description'           => '',
            'public'                => true,
            //'exclude_from_search'   => false,
            //'publicly_queryable'    => true,
            //'show_ui'               => true,
            //'show_in_nav_menus'     => true,
            //'show_in_menu'          => true,
            //'show_in_admin_bar'     => true,
            'menu_position'         => null,
            'menu_icon'             => null,
            'capability_type'       => 'post',
            // @todo capabilities   => [],
            'map_meta_cap'          => null,
            'hierarchical'          => false,
            'supports'              => ['title', 'editor'],
            // @todo 'register_meta_box_cb'  => '',
            'taxonomies'            => [],
            'has_archive'           => false,
            'rewrite'               => [
                'slug'       => $this->getName(),
                'with_front' => false,
                'feeds'      => true,
                'pages'      => true,
                'ep_mask'    => EP_PERMALINK,
            ],
            'permalink_epmask'      => EP_PERMALINK,
            'query_var'             => true,
            'can_export'            => true,
            'delete_with_user'      => null,
            'show_in_rest'          => false,
            'rest_base'             => $this->getName(),
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function label(?string $key = null, string $default = '')
    {
        if (is_null($key)) {
            return $this->labelBag;
        }

        return $this->labelBag->get($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function parseParams(): void
    {
        $labels = $this->params('labels', []);
        if (is_object($labels)) {
            $this->params(['labels' => get_object_vars($labels)]);
        }

        $this->params(['label' => $this->params('label', _x($this->getName(), 'post type general name', 'tify'))]);

        $this->params(['plural' => $this->params('plural', $this->params('labels.name', $this->params('label')))]);

        $this->params(
            ['singular' => $this->params('singular', $this->params('labels.singular_name', $this->params('label')))]
        );

        $this->params(['gender' => $this->params('gender', false)]);

        $this->labelBag = WpPostTypeLabelsBag::create(
            array_merge(
                [
                    'singular' => $this->params('singular'),
                    'plural'   => $this->params('plural'),
                    'gender'   => $this->params('gender'),
                ],
                $this->params('labels', [])
            ),
            $this->params('label')
        );
        $this->params(['labels' => $this->labelBag->all()]);

        $this->params(
            [
                'exclude_from_search' => $this->params()->has('exclude_from_search')
                    ? $this->params('exclude_from_search') : !$this->params('public'),
            ]
        );

        $this->params(
            [
                'publicly_queryable' => $this->params()->has('publicly_queryable')
                    ? $this->params('publicly_queryable') : $this->params('public'),
            ]
        );

        $this->params(
            [
                'show_ui' => $this->params()->has('show_ui')
                    ? $this->params('show_ui') : $this->params('public'),
            ]
        );

        $this->params(
            [
                'show_in_nav_menus' => $this->params()->has('show_in_nav_menus')
                    ? $this->params('show_in_nav_menus') : $this->params('public'),
            ]
        );

        $this->params(
            [
                'show_in_menu' => $this->params()->has('show_in_menu')
                    ? $this->params('show_in_menu') : $this->params('show_ui'),
            ]
        );

        $this->params(
            [
                'show_in_admin_bar' =>
                    $this->params()->has('show_in_admin_bar')
                        ? $this->params('show_in_admin_bar') : $this->params('show_in_menu'),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function supports(string $feature): bool
    {
        return post_type_supports($this->getName(), $feature);
    }

    /**
     * @inheritDoc
     */
    public function setWpPostType(WP_Post_Type $post_type): WpPostTypeInterface
    {
        $this->wpPostType = $post_type;

        return $this;
    }
}