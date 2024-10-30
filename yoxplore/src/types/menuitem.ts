export interface MenuItem {
    id: string;
    name: string;
    description: string;
    price: number;
    image?: string;
    available: boolean;
    spicyLevel?: number;
    dietary?: ('vegetarian' | 'vegan' | 'gluten-free')[];
  }