import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

const root = dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    root,
    publicDir: false,
    plugins: [vue()],
    build: {
        outDir: resolve(root, 'dist'),
        emptyOutDir: true,
        lib: {
            entry: resolve(root, 'frontend/index.js'),
            name: 'GetfyCheckoutCloneTicto',
            formats: ['es'],
            fileName: () => 'plugin-ui.js',
            cssFileName: 'plugin-ui',
        },
        rollupOptions: {
            external: ['vue'],
        },
    },
});
