<?php


namespace BitPress\AutoDiscovery\Discovery\Concerns;

trait DetectsServiceProviders
{
    public function isServiceProvider()
    {
        return (
            preg_match('/use\s+\\\\?Illuminate\\\\Support\\\\ServiceProvider/', $this->file->getContents()) === 1 ||
            preg_match('/extends\s+\\\\Illuminate\\\\Support\\\\ServiceProvider/', $this->file->getContents()) === 1
        );
    }
}