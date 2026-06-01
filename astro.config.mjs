import { defineConfig } from 'astro/config';

export default defineConfig({
  site: 'https://www.tefcpt.org',
  base: '/',
  output: 'static',
  trailingSlash: 'ignore',
});
