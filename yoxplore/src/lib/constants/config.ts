export const APP_CONFIG = {
    name: 'YoXplore',
    description: 'Your ultimate tourism platform',
    version: '1.0.0',
    api: {
      baseUrl: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000/api',
    },
    images: {
      domains: ['localhost', 'firebasestorage.googleapis.com'],
    },
  };