import { Review } from './review';
import { MenuItem } from './menuitem';

export type PriceRange = 'low' | 'medium' | 'high' | 'luxury';

export interface OpeningHours {
  day: string;
  open: string;
  close: string;
}

export interface MenuCategory {
  category: string;
  items: MenuItem[];
}

export interface Restaurant {
  id: string;
  name: string;
  description: string;
  location: {
    address: string;
    city: string;
    coordinates: {
      latitude: number;
      longitude: number;
    }
  };
  cuisine: string[];
  priceRange: PriceRange;
  openingHours: OpeningHours[];
  images: string[];
  menu: MenuCategory[];
  rating: number;
  reviews: Review[];
  featured: boolean;
}