<?php

declare(strict_types=1);

namespace Woda\WordPress\ScriptsStylesLoader;

final class Script extends AbstractAsset implements ScriptInterface
{
    /** @var bool */
    public $inFooter;
    /** @var bool */
    public $loadDeferred;

    /**
     *
     * @param string           $src       Full URL of the script, or path of the script relative to the WordPress root
     *                                    directory.
     * @param string[]         $deps      Optional. An array of registered script handles this script depends on.
     *                                    Default empty array.
     * @param string           $handle    Optional. Name of the script. Should be unique.
     *                                    If empty, handle will be generated from prefix and file name.
     * @param string|bool|null $ver       Optional. String specifying script version number, if it has one, which is
     *                                    added to the URL as a query string for cache busting purposes. If version is
     *                                    set to false, a version number is automatically added equal to current
     *                                    installed WordPress version. If set to null, no version is added.
     * @param bool             $inFooter  Optional. Whether to enqueue the script before </body> instead of in the
     *                                    <head>. Default 'false'.
     */
    public function __construct(
        string $src,
        ?array $deps = null,
        ?string $handle = null,
        $ver = false,
        bool $inFooter = false
    )
    {
        parent::__construct($src, $deps, $handle, $ver);
        $this->inFooter = $inFooter;
    }

    public function applyAsyncPatternToTag(string $tag): string
    {
        return str_replace(' src', ' async=\'async\' src', $tag);
    }

    public function applyDeferPatternToTag(string $tag): string
    {
        return str_replace(' src', ' defer=\'defer\' src', $tag);
    }

    public function enqueue(): void
    {
        wp_enqueue_script(
            $this->handle,
            $this->src,
            $this->deps,
            $this->getVersion(),
            $this->inFooter
        );
    }

    public function loadDeferred(): ScriptInterface
    {
        $this->loadDeferred = true;
        return $this;
    }

    public function shouldEnqueueInEditor(): bool
    {
        return false;
    }
}
