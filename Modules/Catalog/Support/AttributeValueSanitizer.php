<?php

namespace Modules\Catalog\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class AttributeValueSanitizer
{
    private const ALLOWED_TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote', 'a'];

    public function sanitizeRichText(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="attribute-rich-text-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('attribute-rich-text-root');
        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->cleanChildren($root);
        $sanitized = '';
        foreach ($root->childNodes as $child) {
            $sanitized .= $document->saveHTML($child);
        }

        return $sanitized;
    }

    private function cleanChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'svg', 'math'], true)) {
                $node->removeChild($child);

                continue;
            }

            $this->cleanChildren($child);
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild !== null) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            $href = $tag === 'a' ? $child->getAttribute('href') : '';
            while ($child->attributes->length > 0) {
                $child->removeAttributeNode($child->attributes->item(0));
            }
            if ($tag === 'a') {
                if ($href !== '' && preg_match('/^(https?:|mailto:|\/(?!\/)|#)/i', $href)) {
                    $child->setAttribute('href', $href);
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
            }
        }
    }
}
