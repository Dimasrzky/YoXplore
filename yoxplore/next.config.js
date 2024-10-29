/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  // Mengoptimalkan build time
  swcMinify: true,
  // Menonaktifkan pengumpulan data telemetri
  typescript: {
    ignoreBuildErrors: true, // Hanya untuk development
  },
  eslint: {
    ignoreDuringBuilds: true, // Hanya untuk development
  },
  // Mengoptimalkan loading modules
  modularizeImports: {
    'lucide-react': {
      transform: 'lucide-react/dist/esm/icons/{{ kebabCase member }}',
    }
  }
}

module.exports = nextConfig