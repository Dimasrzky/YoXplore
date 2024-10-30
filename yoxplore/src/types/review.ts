export interface Review {
    id: string;
    userId: string;
    userName: string;
    rating: number;
    comment: string;
    images?: string[];
    createdAt: Date;
    updatedAt?: Date;
  }