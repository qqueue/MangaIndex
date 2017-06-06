<?php

namespace Minify;

class Minify extends \CeesVanEgmond\Minify\Minify
{
    
    protected $overrideEnable = null;

    public function enable()
    {
        $this->overrideEnable = true;
    }

    public function disable()
    {
        $this->overrideEnable = false;
    }

    protected function minifyForCurrentEnvironment()
    {
        if ($this->overrideEnable !== null) {
            return $this->overrideEnable;
        } else {
            return parent::minifyForCurrentEnvironment();
        }
    }
}
