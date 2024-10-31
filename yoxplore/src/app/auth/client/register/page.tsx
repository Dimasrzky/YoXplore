import { Metadata } from 'next';
import ClientRegisterPage from './register-form';

export const metadata: Metadata = {
  title: 'Create Your Account',
  description: 'Login to YoXplore Admin Dashboard',
  icons: {
    icon: '/images/logo/Logo Yoxplore.png', 
  },
};

export default function LoginPage() {
  return <ClientRegisterPage />;
}