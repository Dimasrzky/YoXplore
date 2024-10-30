import { Review } from './review';

export interface Room {
  type: string;
  description: string;
  price: number;
  capacity: number;
  amenities: string[];
  images: string[];
  available: number;
}

export interface Location {
  address: string;
  city: string;
  coordinates: {
    latitude: number;
    longitude: number;
  };
}

export interface Policies {
  checkIn: string;
  checkOut: string;
  cancellation: string;
}

export interface Hotel {
  id: string;
  name: string;
  description: string;
  location: Location;
  rooms: Room[];
  facilities: string[];
  images: string[];
  rating: number;
  reviews: Review[];
  policies: Policies;
  featured: boolean;
}