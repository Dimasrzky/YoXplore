export interface User {
  id: string;
  email: string;
  name: string;
  role: 'client' | 'admin';
  phoneNumber?: string;
  profileImage?: string;
  createdAt: Date;
  lastLogin: Date;
}