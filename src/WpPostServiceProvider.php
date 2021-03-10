<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Container\BaseServiceProvider;

class WpPostServiceProvider extends BaseServiceProvider
{
    /**
     * @var string[]
     */
    protected $provides = [
        WpPostManagerInterface::class
    ];

    /**
     * @inheritdoc
     */
    public function register(): void
    {
        $this->getContainer()->share(WpPostManagerInterface::class, function() {
            return new WpPostManager([], $this->getContainer());
        });
    }
}