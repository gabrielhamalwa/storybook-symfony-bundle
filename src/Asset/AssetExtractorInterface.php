<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Asset;

use Storybook\SymfonyBundle\Dto\AssetCollection;

interface AssetExtractorInterface
{
    /**
     * Extract normalized assets for the configured entrypoint.
     */
    public function extract(): AssetCollection;
}
