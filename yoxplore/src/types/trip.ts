import { Review } from './review';

export interface Trip {
  id: string;
  name: string;
  description: string;
  destination: {
    name: string;
    city: string;
    country: string;
    coordinates: {
      latitude: number;
      longitude: number;
    }
  };
  duration: {
    days: number;
    nights: number;
  };
  price: {
    adult: number;
    child?: number;
  };
  includes: string[];
  excludes: string[];
  itinerary: {
    day: number;
    activities: string[];
  }[];
  images: string[];
  maxParticipants: number;
  availableSlots: number;
  startDates: Date[];
  category: string[];
  featured: boolean;
  rating: number;
  reviews: Review[];
}