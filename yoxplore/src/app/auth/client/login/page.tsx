'use client';

import { useState } from 'react';
import { signIn } from 'next-auth/react';
import { useRouter } from 'next/navigation';
import { FcGoogle } from 'react-icons/fc';

export default function ClientRegisterPage() {
  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    password: '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const router = useRouter();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      // Ganti dengan endpoint API register Anda
      const response = await fetch('/api/auth/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Registration failed');
      }

      // Setelah registrasi berhasil, login otomatis
      const result = await signIn('credentials', {
        email: formData.email,
        password: formData.password,
        redirect: false,
      });

      if (result?.error) {
        setError('Registration successful but failed to login');
        return;
      }

      router.push('/client/dashboard');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An unexpected error occurred');
    } finally {
      setLoading(false);
    }
  };

  const handleGoogleSignIn = async () => {
    try {
      setLoading(true);
      await signIn('google', {
        callbackUrl: '/client/dashboard',
        redirect: true
      });
    } catch (error) {
      setError('Failed to sign in with Google');
      setLoading(false);
    }
  };

  return (
    <div className="w-screen h-screen flex items-center justify-center bg-gray-50">
      <div className="flex w-full h-full overflow-hidden">
        {/* Bagian Kiri: Gambar Latar */}
        <div className="hidden md:flex w-full">
          <img src="/images/logo/bg login 2.jpeg" alt="bg-login" className="object-cover h-full w-full" />
        </div>

        {/* Bagian Kanan: Form Register */}
        <div className="flex flex-col justify-center p-10 w-2/3 bg-white">
          <div className="flex justify-center mb-6">
            <h1 className="text-2xl font-semibold text-brown-500">Login Your Account</h1>
          </div>

          {/* Pesan Error */}
          {error && (
            <div className="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          )}

          {/* Form Register */}
          <form onSubmit={handleSubmit} className="space-y-6">

            {/* Field Email */}
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-brown-500">
                Email
              </label>
              <div className="mt-1">
                <input
                  id="email"
                  name="email"
                  type="email"
                  required
                  value={formData.email}
                  onChange={handleChange}
                  className="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brown-500 focus:border-brown-500 sm:text-sm"
                  placeholder="example@gmail.com"
                />
              </div>
            </div>

            {/* Field Password */}
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-brown-500">
                Password
              </label>
              <div className="mt-1">
                <input
                  id="password"
                  name="password"
                  type="password"
                  required
                  value={formData.password}
                  onChange={handleChange}
                  className="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brown-500 focus:border-brown-500 sm:text-sm"
                  placeholder="••••••••"
                />
              </div>
            </div>

            {/* Tombol Submit */}
            <div>
              <button
                type="submit"
                disabled={loading}
                className={`w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-brown-600 hover:bg-brown-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brown-500 ${
                  loading ? 'opacity-50 cursor-not-allowed' : ''
                }`}
              >
                {loading ? 'Creating Account...' : 'Create Account'}
              </button>
            </div>
          </form>

          <div className="mt-4 text-right">
            <p className="text-sm text-gray-600">
              <a href="./login" className="text-brown-600 hover:text-brown-700 font-medium">
                Forgot Password
              </a>
            </p>
          </div>

          <div className="relative my-6">
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t border-gray-300"></div>
            </div>
            <div className="relative flex justify-center text-sm">
              <span className="px-2 bg-white text-gray-500">Or register with Google</span>
            </div>
          </div>

          {/* Login dengan Google */}
          <div className="mb-6 flex justify-center">
            <button
              onClick={handleGoogleSignIn}
              disabled={loading}
              className="w-2/4 flex items-center justify-center gap-2 py-3 px-4 border border-gray-300 rounded-md shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
            > 
              <FcGoogle className="w-6 h-6" />Sign in with Google
            </button>
          </div>

          {/* Link ke Login */}
          <div className="mt-4 text-center">
            <p className="text-sm text-gray-600">
              Doesn't have an account?{' '}
              <a href="./register" className="text-brown-600 hover:text-brown-700 font-medium">
                Create Account
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}