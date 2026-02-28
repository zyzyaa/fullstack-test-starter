import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/graphql': {
        target: 'http://fullstack-test.local',
        changeOrigin: true,
        rewrite: (path) => '/graphql',
      }
    }
  }
})
