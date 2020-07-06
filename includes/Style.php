<?php

declare(strict_types=1);

namespace Woda\WordPress\ScriptsStylesLoader;

final class Style extends AbstractAsset implements StyleInterface
{
    /** @var bool */
    public $enqueueEditor = false;
    /** @var string */
    public $media;

    /**
     *
     * @param string           $src    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress
     *                                 root directory.
     * @param string[]         $deps   Optional. An array of registered stylesheet handles this stylesheet depends on.
     *                                 Default empty array.
     * @param string           $handle Optional. Name of the stylesheet. Should be unique.
     *                                 If empty, handle will be generated from prefix and file name.
     * @param string|bool|null $ver    Optional. String specifying stylesheet version number, if it has one, which is
     *                                 added to the URL as a query string for cache busting purposes. If version is set
     *                                 to false, a version number is automatically added equal to current installed
     *                                 WordPress version. If set to null, no version is added.
     * @param string           $media  Optional. The media for which this stylesheet has been defined.
     *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media
     *                                 queries like '(orientation: portrait)' and '(max-width: 640px)'.
     */
    public function __construct(
        string $src,
        ?array $deps = null,
        ?string $handle = null,
        $ver = false,
        string $media = 'all'
    ) {
        parent::__construct($src, $deps, $handle, $ver);
        $this->media = $media;
    }

    public function addEditorStyle(): void
    {
        add_editor_style(str_replace(get_stylesheet_directory_uri() . '/', '', $this->src));
    }

    public function applyAsyncPatternToTag(string $tag): string
    {
        $tag = str_replace(
            ' rel=\'stylesheet\'',
            ' rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"',
            $tag
        );
        $tag .= sprintf('<noscript><link rel="stylesheet" %s></noscript>', $this->extractHrefFromTag($tag));
        return $tag;
    }

    public function enqueue(): void
    {
        wp_enqueue_style(
            $this->handle,
            $this->src,
            $this->deps,
            $this->getVersion(),
            $this->media
        );
    }

    public function enqueueEditor(): StyleInterface
    {
        $this->enqueueEditor = true;
        return $this;
    }

    public function shouldEnqueueInEditor(): bool
    {
        return $this->enqueueEditor;
    }

    private function extractHrefFromTag(string $tag): string
    {
        preg_match('/(href=\'.*?\')/', $tag, $matches, PREG_OFFSET_CAPTURE);
        if (isset($matches[0])) {
            return $matches[0][0];
        }
        return '';
    }
}
