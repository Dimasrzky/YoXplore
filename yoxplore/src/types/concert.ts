export interface Concert {
  id: string;
  title: string;
  description: string;
  location: {
    address: string;
    city: string;
    coordinates: {
      latitude: number;
      longitude: number;
    }
  };
  date: Date;
  time: string;
  price: {
    regular: number;
    vip?: number;
  };
  capacity: number;
  availableSeats: number;
  images: string[];
  status: 'upcoming' | 'ongoing' | 'completed' | 'cancelled';
  organizer: string;
  category: string[];
  featured: boolean;
}
