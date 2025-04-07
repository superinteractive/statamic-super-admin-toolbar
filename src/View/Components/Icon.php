<?php

declare(strict_types=1);

namespace SuperInteractive\SuperAdminToolbar\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use DOMDocument;

final class Icon extends Component
{
    /**
     * The original attributes found on the <svg> tag.
     * e.g. ['xmlns' => 'http://www.w3.org/2000/svg', 'viewBox' => '0 0 24 24', ...]
     */
    public array $svgAttributes = [];

    /**
     * The inner child nodes of the <svg> element (everything between <svg> ... </svg>).
     */
    public string $innerContent = '';

    /**
     * Create a new component instance.
     *
     * @param  string  $src  The SVG filename (without .svg extension).
     */
    public function __construct(public string $src)
    {
        $this->loadAndParseSvg();
    }

    /**
     * Load the SVG file, parse attributes, and set the inner content.
     */
    private function loadAndParseSvg(): void
    {
        // Basic sanitization to prevent directory traversal.
        if (Str::contains($this->src, ['/', '\\', '..'])) {
            Log::warning("Icon Component: Attempted to access potentially unsafe path in icon name '{$this->src}'.");
            return;
        }

        // Build the full file path to your SVG.
        try {
            // Adjust the path to match your add-on structure.
            $basePath = dirname(__DIR__, 3);
            $filePath = $basePath . '/resources/svg/' . $this->src . '.svg';
        } catch (\Throwable $e) {
            Log::error("Icon Component: Could not determine SVG path for icon '{$this->src}'.", ['exception' => $e]);
            return;
        }

        if (! file_exists($filePath)) {
            return;
        }

        if (! is_readable($filePath)) {
            Log::warning("Icon Component: SVG file not readable at path: {$filePath}");
            return;
        }

        $rawContent = @file_get_contents($filePath);

        if (empty($rawContent)) {
            return;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();

        // Try to parse as XML first; fallback to HTML if necessary.
        if (! @$dom->loadXML($rawContent, LIBXML_NOBLANKS | LIBXML_NOCDATA)) {
            if (! @$dom->loadHTML($rawContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
                Log::error("Icon Component: Failed to parse SVG content for '{$this->src}'. Path: {$filePath}");
                libxml_clear_errors();
                libxml_use_internal_errors(false);
                return;
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        $svgElement = $dom->getElementsByTagName('svg')->item(0);

        if (! $svgElement) {
            Log::error("Icon Component: No <svg> tag found in '{$this->src}'. Path: {$filePath}");
            return;
        }

        // 1) Extract the original <svg> attributes into $this->svgAttributes.
        foreach ($svgElement->attributes as $attr) {
            $this->svgAttributes[$attr->nodeName] = $attr->nodeValue;
        }

        // 2) Extract the child nodes (everything between <svg>...</svg>).
        $inner = '';
        foreach ($svgElement->childNodes as $child) {
            $inner .= $dom->saveXML($child);
        }
        $this->innerContent = $inner;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('super-admin-toolbar::components.icon');
    }
}
