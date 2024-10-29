export interface User {
    id: string;
    email: string;
    name: string;
    role: 'client' | 'admin';
    createdAt: Date;
  }