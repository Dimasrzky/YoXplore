import { Metadata } from 'next';
import LoginForm from './login-form';

export const metadata: Metadata = {
  title: 'Login Your Account',
  description: 'Login to YoXplore Admin Dashboard',
  icons: {
    icon: '/images/logo/Logo Yoxplore.png', 
  },
};

export default function LoginPage() {
  return <LoginForm />;
}