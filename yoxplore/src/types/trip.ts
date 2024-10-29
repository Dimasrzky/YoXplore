export interface Trip {
    id: string;
    name: string;
    description: string;
    destination: string;
    duration: number;
    price: number;
    includes: string[];
    imageUrl: string;
  }