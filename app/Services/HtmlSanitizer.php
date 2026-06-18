<?php

namespace App\Services;

class HtmlSanitizer
{
    /**
     * Sanitise rich HTML content (CKEditor output) by removing dangerous
     * elements and attributes while preserving safe formatting tags.
     */
    public static function clean(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // 1. Strip <script> and <style> blocks entirely (including their content)
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html);

        // 2. Strip dangerous tags (keep content of most, but not script/style)
        $html = preg_replace('#<(iframe|frame|frameset|object|embed|applet|base|form|input|button|textarea|select|meta|link)\b[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<(iframe|frame|frameset|object|embed|applet|base|form|input|button|textarea|select|meta|link)\b[^>]*/?\s*>#is', '', $html);

        // 3. Strip all on* event handler attributes (onclick, onload, onerror, etc.)
        $html = preg_replace('#\s+on\w+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);

        // 4. Strip javascript: and data: URIs from href/src/action attributes
        $html = preg_replace('#(href|src|action|formaction)\s*=\s*["\']?\s*(javascript|data|vbscript):#i', '$1="#"', $html);

        // 5. Strip style attributes containing javascript/expression
        $html = preg_replace('#\sstyle\s*=\s*(?:"[^"]*(?:javascript|expression)[^"]*"|\'[^\']*(?:javascript|expression)[^\']*\')#i', '', $html);

        return $html;
    }
}
