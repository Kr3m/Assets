<?php

namespace Bonfire\Assets\Filters;

use Bonfire\Assets\Asset;

interface FilterInterface {

    public function run(Asset $asset);
}