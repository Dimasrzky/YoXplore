import { Metadata } from 'next';
import ClientForgotPassPage from './forgot-form';

export const metadata: Metadata = {
  title: 'Forgot Password',
  description: 'Login to YoXplore Admin Dashboard',
  icons: {
    icon: '/images/logo/Logo Yoxplore.png', 
  },
};

export default function LoginPage() {
  return <ClientForgotPassPage />;
}