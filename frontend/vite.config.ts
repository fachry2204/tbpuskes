import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'
import { resolve } from 'node:path'
import { rm } from 'node:fs/promises'

// https://vite.dev/config/
export default defineConfig({
  define: {
    'import.meta.env.VITE_API_BASE_URL': JSON.stringify('/api/v1'),
  },
  plugins: [
    {
      name: 'clean-public-assets',
      async buildStart() {
        await rm(resolve(__dirname, '../public/assets'), { recursive: true, force: true })
      },
    },
    vue(),
  ],
  build: {
    outDir: resolve(__dirname, '../public'),
    emptyOutDir: false,
  },
})
