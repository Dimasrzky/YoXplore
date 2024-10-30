export const ROUTES = {
    HOME: '/',
    AUTH: {
      LOGIN: '/login',
      REGISTER: '/register',
      FORGOT_PASSWORD: '/forgot-password',
    },
    CLIENT: {
      HOME: '/client/home',
      PROFILE: '/client/profile',
      SETTINGS: '/client/settings',
      YOCONCERT: '/client/yo-concert',
      YOTRIP: '/client/yo-trip',
      YOTASTE: '/client/yo-taste',
      YOSTAY: '/client/yo-stay',
    },
    ADMIN: {
      DASHBOARD: '/admin/dashboard',
      USERS: '/admin/users',
      MANAGEMENT: {
        CONCERTS: '/admin/management/concerts',
        TRIPS: '/admin/management/trips',
        RESTAURANTS: '/admin/management/restaurants',
        HOTELS: '/admin/management/hotels',
      },
    },
  };