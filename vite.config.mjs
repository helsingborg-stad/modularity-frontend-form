import { createViteConfig } from 'vite-config-factory'

const entries = {
    'js-init': './source/js/init.ts',
    'css-main': './source/sass/main.scss'
}

export default createViteConfig(entries, {
    outDir: 'dist',
    manifestFile: 'manifest.json'
})