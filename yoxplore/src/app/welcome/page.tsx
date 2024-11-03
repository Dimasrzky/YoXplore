import { Metadata } from 'next';
import ClientWelcomePage from './welcome-content';

export const metadata: Metadata = {
  title: 'Welcome To Yoplore',
  icons: {
    icon: '/images/logo/Logo Yoxplore.png', 
  },
};

export default function WelcomePage() {
  return <ClientWelcomePage />;
}