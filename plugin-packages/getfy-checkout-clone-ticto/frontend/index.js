import TictoCheckoutTemplate from './TictoCheckoutTemplate.vue';

function ensureStyles() {
    if (typeof document === 'undefined') {
        return;
    }
    if (document.querySelector('link[data-getfy-plugin-css="getfy-checkout-clone-ticto"]')) {
        return;
    }
    try {
        const cssUrl = new URL(/* @vite-ignore */ './plugin-ui.css', import.meta.url).href;
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = cssUrl;
        link.dataset.getfyPluginCss = 'getfy-checkout-clone-ticto';
        document.head.appendChild(link);
    } catch (_) {
        // CSS opcional se o asset não existir.
    }
}

ensureStyles();

window.__GETFY_PLUGIN_UI__ = window.__GETFY_PLUGIN_UI__ || {};
window.__GETFY_PLUGIN_UI__['getfy-checkout-clone-ticto'] = {
    TictoCheckoutTemplate,
};

export { TictoCheckoutTemplate };
