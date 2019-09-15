<?php


namespace BitPress\AutoDiscovery\Discovery;

use Symfony\Component\Finder\SplFileInfo;

class SourceFile
{
    use Concerns\DetectsServiceProviders;
    use Concerns\DetectsFacades;

    /**
     * @var \Symfony\Component\Finder\SplFileInfo
     */
    private $file;

    /**
     * SourceFile constructor.
     * @param $file
     */
    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
    }

    public function getClassInfo()
    {
        preg_match('/namespace\s+([^;]+);/', $this->file->getContents(), $matches);
        $namespace = $matches[1] ?? null;
        preg_match('/class\s+([^\s]+)\s/', $this->file->getContents(), $matches);
        $class = $matches[1] ?? null;

        return [$namespace, $class];
    }

    public function fullyQualifiedClass()
    {
        [$namespace, $class] = $this->getClassInfo();

        return $namespace . '\\' . $class;
    }
}