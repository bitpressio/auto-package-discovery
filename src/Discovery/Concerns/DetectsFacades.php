<?php


namespace BitPress\AutoDiscovery\Discovery\Concerns;

trait DetectsFacades
{
    public function isFacade()
    {
        return (
            preg_match('/use\s+\\\\?Illuminate\\\\Support\\\\Facades\\\\Facade/', $this->file->getContents()) === 1 ||
            preg_match('/extends\s+\\\\Illuminate\\\\Support\\\\Facades\\\\Facade/', $this->file->getContents()) === 1
        );
    }
}