window.AITranslator = {
    isTranslating: false,
    originalTexts: new Map(), // DOM node -> original text

    init() {
        const lang = localStorage.getItem('app_locale') || 'EN';
        if (lang !== 'EN') {
            this.translatePage(lang);
        }
    },

    async translatePage(targetLang) {
        if (targetLang === 'EN') {
            this.restoreOriginals();
            return;
        }

        if (this.isTranslating) return;
        this.isTranslating = true;

        try {
            const textNodes = this.getTextNodes(document.body);
            const textsToTranslate = [];
            const nodesToTranslate = [];

            textNodes.forEach(node => {
                const text = node.nodeValue.trim();
                if (text && text.length > 1 && !this.shouldIgnore(node)) {
                    // Save original if not already saved
                    if (!this.originalTexts.has(node)) {
                        this.originalTexts.set(node, text);
                    }

                    // Always translate from the original text to avoid re-translating translated text
                    textsToTranslate.push(this.originalTexts.get(node));
                    nodesToTranslate.push(node);
                }
            });

            if (textsToTranslate.length === 0) {
                this.isTranslating = false;
                return;
            }

            // We can batch the translations to avoid payload limits if necessary, 
            // but for a typical page, 100-200 nodes should be fine in one request.
            // Split into batches of 100.
            const batchSize = 100;
            for (let i = 0; i < textsToTranslate.length; i += batchSize) {
                const batchTexts = textsToTranslate.slice(i, i + batchSize);
                const batchNodes = nodesToTranslate.slice(i, i + batchSize);

                const response = await fetch('/api/translate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        texts: batchTexts,
                        targetLanguage: this.getFullLanguageName(targetLang)
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.translations && data.translations.length === batchTexts.length) {
                        data.translations.forEach((translatedText, index) => {
                            // Only update if it actually changed to avoid unnecessary DOM paints
                            if (batchNodes[index].nodeValue !== translatedText) {
                                batchNodes[index].nodeValue = translatedText;
                            }
                        });
                    }
                }
            }
        } catch (error) {
            console.error('AI Translation error:', error);
        } finally {
            this.isTranslating = false;
        }
    },

    restoreOriginals() {
        this.originalTexts.forEach((originalText, node) => {
            if (node.nodeValue !== originalText) {
                node.nodeValue = originalText;
            }
        });
    },

    getTextNodes(element) {
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function (node) {
                    if (node.parentNode.nodeName === 'SCRIPT' ||
                        node.parentNode.nodeName === 'STYLE' ||
                        node.parentNode.nodeName === 'NOSCRIPT' ||
                        node.parentNode.nodeName === 'CODE') {
                        return NodeFilter.FILTER_REJECT;
                    }
                    if (node.nodeValue.trim() === '') {
                        return NodeFilter.FILTER_SKIP;
                    }
                    return NodeFilter.FILTER_ACCEPT;
                }
            },
            false
        );

        const nodes = [];
        let currentNode;
        while (currentNode = walker.nextNode()) {
            nodes.push(currentNode);
        }
        return nodes;
    },

    shouldIgnore(node) {
        // Ignore single characters, numbers, very short strings that are likely not words
        const text = node.nodeValue.trim();
        if (text.length <= 1) return true;
        if (!isNaN(text)) return true; // It's just a number

        // Ignore JSON or code-like structures
        if (text.startsWith('{') || text.startsWith('[')) return true;

        return false;
    },

    getFullLanguageName(code) {
        const map = {
            'EN': 'English',
            'PH': 'Filipino',
            'BIS': 'Bisaya'
        };
        return map[code] || 'Filipino';
    }
};

// Initialize translation on page load
document.addEventListener('DOMContentLoaded', () => {
    window.AITranslator.init();
});
