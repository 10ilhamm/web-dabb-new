/**
 * CMS RichTextEditor — plugin compatibility shim.
 *
 * The new self-built editor (rte.js) ships with all toolbar features built in
 * (link, image, video, table, color, source, fullscreen, emoji, etc.).
 *
 * This file is preserved as a no-op so existing <script> tags that reference
 * `all_plugins.js` keep working. New plugins can be registered here via:
 *
 *   RichTextEditor.registerPlugin('my-plugin', function (editor) { ... });
 */
(function (global) {
    'use strict';
    if (!global.RichTextEditor) {
        // Editor core not loaded yet — nothing to wire up.
        return;
    }
    // No-op: every plugin is bundled in core.
})(typeof window !== 'undefined' ? window : this);
