import { Restaurant, PriceRange, OpeningHours, MenuItem } from '../types';

// Check if restaurant is open
export const isRestaurantOpen = (restaurant: Restaurant): boolean => {
  const now = new Date();
  const currentDay = now.toLocaleDateString('en-US', { weekday: 'long' });
  const currentTime = now.toLocaleTimeString('en-US', { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: false 
  });

  const todayHours = restaurant.openingHours.find(
    hours => hours.day.toLowerCase() === currentDay.toLowerCase()
  );

  if (!todayHours) return false;

  return currentTime >= todayHours.open && currentTime <= todayHours.close;
};

// Get price range symbol
export const getPriceRangeSymbol = (priceRange: PriceRange): string => {
  const symbols = {
    low: '$',
    medium: '$$',
    high: '$$$',
    luxury: '$$$$'
  };
  return symbols[priceRange];
};

// Get available menu items
export const getAvailableMenuItems = (restaurant: Restaurant): MenuItem[] => {
  return restaurant.menu.flatMap(category => 
    category.items.filter(item => item.available)
  );
};

// Calculate average menu price
export const getAverageMenuPrice = (restaurant: Restaurant): number => {
  const allItems = restaurant.menu.flatMap(category => category.items);
  const totalPrice = allItems.reduce((sum, item) => sum + item.price, 0);
  return totalPrice / allItems.length;
};

// Search menu items by dietary preference
export const searchMenuByDietary = (
  restaurant: Restaurant, 
  dietary: 'vegetarian' | 'vegan' | 'gluten-free'
): MenuItem[] => {
  return restaurant.menu.flatMap(category =>
    category.items.filter(item => item.dietary?.includes(dietary))
  );
};

// Format opening hours
export const formatOpeningHours = (hours: OpeningHours): string => {
  return `${hours.day}: ${hours.open} - ${hours.close}`;
};

// Check if restaurant serves specific cuisine
export const servesCuisine = (restaurant: Restaurant, cuisine: string): boolean => {
  return restaurant.cuisine.some(c => 
    c.toLowerCase() === cuisine.toLowerCase()
  );
};