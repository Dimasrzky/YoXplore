'use client';

import { useState } from 'react';
import { signIn } from 'next-auth/react';
import { useRouter } from 'next/navigation';
import { AiFillEye, AiFillEyeInvisible, AiOutlineUser, AiOutlineLock, AiOutlineRedEnvelope } from 'react-icons/ai';

export default function ClientForgotPassPage() {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });
  const [showPassword, setShowPassword] = useState(false);
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
            <h1 className="text-2xl font-semibold text-brown-500">Forgot Password</h1>
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
              <label htmlFor="email" className="block text-sm font-medium text-brown-500">
                Email
              </label>
              <div className="mt-1 relative flex items-center border-b border-gray-300 focus-within:border-brown-500">
              <AiOutlineRedEnvelope className="text-brown-500 mr-3" size={30} />
                <input
                  id="email"
                  name="email"
                  type="email"
                  required
                  value={formData.email}
                  onChange={handleChange}
                  className="w-full py-3 focus:outline-none sm:text-sm placeholder-gray-400"
                  placeholder="example@gmail.com"
                />
              </div>
            </div>

            {/* Field Password */}
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-brown-500">
                Password
              </label>
              <div className="mt-1 relative flex items-center border-b border-gray-300 focus-within:border-brown-500">
                <AiOutlineLock className="text-brown-500 mr-3" size={30} />
                <input
                  id="password"
                  name="password"
                  type={showPassword ? 'text' : 'password'}
                  required
                  value={formData.password}
                  onChange={handleChange}
                  className="w-full py-3 focus:outline-none sm:text-sm placeholder-gray-400"
                  placeholder="••••••••"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="text-brown-500 ml-3"
                >
                  {showPassword ? <AiFillEyeInvisible size={24} /> : <AiFillEye size={24} />}
                </button>
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
                {loading ? 'Creating Account...' : 'Sign In'}
              </button>
            </div>
          </form>

          {/* Link ke Login */}
          <div className="mt-4 text-center">
            <p className="text-sm text-gray-600">
              Already have an account?{' '}
              <a href="./login" className="text-brown-600 hover:text-brown-700 font-medium">
                Login here
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}