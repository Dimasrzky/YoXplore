import { Metadata } from 'next';
import AdminLoginPage from './login-form';

export const metadata: Metadata = {
  title: 'Login as Admin',
  description: 'Login to YoXplore Admin Dashboard',
  icons: {
    icon: '/images/logo/Logo Yoxplore.png', 
  },
};

export default function LoginPage() {
  return <AdminLoginPage />;
}