'use client';

import { useState } from 'react';
import { signIn } from 'next-auth/react';
import { useRouter } from 'next/navigation';

export default function ClientLoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const router = useRouter();

  const handleSubmit = async (e: React.FormEvent) => { 
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const result = await signIn('credentials', {
        email,
        password,
        redirect: false,
      });

      if (result?.error) {
        setError('Invalid credentials');
        return;
      }

      router.push('/client/dashboard');
    } catch (err) {
      setError('An unexpected error occurred');
    } finally {
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

        {/* Bagian Kanan: Form Login */}
        <div className="flex flex-col justify-center p-10 w-2/3 bg-white">
          <div className="flex justify-center mb-6">
            <h1 className="text-2xl font-semibold text-gray-800">Login Your Account</h1>
          </div>

          {/* Pesan Error */}
          {error && (
            <div className="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          )}

          {/* Form Login */}
          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Field Email */}
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-800">
                Email
              </label>
              <div className="mt-1">
                <input
                  id="email"
                  name="email"
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brown-500 focus:border-brown-500 sm:text-sm"
                  placeholder="example@gmail.com"
                />
              </div>
            </div>

            {/* Field Password */}
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gray-800">
                Password
              </label>
              <div className="mt-1">
                <input
                  id="password"
                  name="password"
                  type="password"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
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
                {loading ? 'Loading...' : 'Login'}
              </button>
            </div>
          </form>

          {/* Link Lupa Password */}
          <div className="mt-4 text-right">
            <a href="/forgot-password" className="text-sm text-brown-600 hover:text-brown-700">
              Forgot Password?
            </a>
          </div>

          {/* Login dengan Google */}
          <div className="mt-4 flex justify-center">
            <button
              onClick={() => signIn('google')}
              className="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
            >
              Sign in with Google
            </button>
          </div>

          {/* Link Buat Akun Baru */}
          <div className="mt-2 text-center">
            <a href="/signup" className="text-sm text-gray-600 hover:text-gray-700">
              Create New Account
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}