'use client';

import { useState } from 'react';
import { signIn } from 'next-auth/react';
import { useRouter } from 'next/navigation';
import { FcGoogle } from 'react-icons/fc';
import { motion } from 'framer-motion'; // Tambahkan framer-motion untuk animasi
import { FaEye, FaEyeSlash } from 'react-icons/fa';

export default function LoginForm() {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });
  const [focused, setFocused] = useState({
    email: false,
    password: false,
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const router = useRouter();

  const handleFocus = (field: 'email' | 'password') => {
    setFocused(prev => ({ ...prev, [field]: true }));
  };

  const handleBlur = (field: 'email' | 'password') => {
    setFocused(prev => ({ ...prev, [field]: formData[field].length > 0 }));
  };

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
      const result = await signIn('credentials', {
        email: formData.email,
        password: formData.password,
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

  const [showPassword, setShowPassword] = useState(false);

  // ... rest of your handleSubmit and handleGoogleSignIn functions

  return (
    <motion.div 
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.5 }}
      className="w-screen h-screen flex items-center justify-center bg-gray-50"
    >
      <div className="flex w-full h-full overflow-hidden">
        {/* Bagian Kiri: Gambar Latar dengan animasi fade in */}
        <motion.div 
          initial={{ x: -100, opacity: 0 }}
          animate={{ x: 0, opacity: 1 }}
          transition={{ duration: 0.7 }}
          className="hidden md:flex w-full"
        >
          <img 
            src="/images/logo/bg login 2.jpeg" 
            alt="bg-login" 
            className="object-cover h-full w-full transition-transform duration-700"
          />
        </motion.div>
        {/* Bagian Kanan: Form Login dengan animasi slide in */}
        <motion.div 
          initial={{ x: 100, opacity: 0 }}
          animate={{ x: 0, opacity: 1 }}
          transition={{ duration: 0.7 }}
          className="flex flex-col justify-center p-10 w-2/3 bg-white"
        >
          <motion.div 
            initial={{ y: -20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.3 }}
            className="flex justify-center mb-6"
          >
            <h1 className="text-2xl font-semibold text-brown-500 font-sans">Login Your Account</h1>
          </motion.div>
          <br></br>

          {error && (
            <motion.div 
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              className="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded"
            >
              <p className="text-sm text-red-700">{error}</p>
            </motion.div>
          )}

          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Email Field dengan Floating Label */}
            <div className="relative">
              <input
                id="email"
                name="email"
                type="email"
                required
                value={formData.email}
                onFocus={() => handleFocus('email')}
                onBlur={() => handleBlur('email')}
                onChange={handleChange}
                className="peer w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brown-500 focus:border-brown-500 placeholder-transparent"
                placeholder="Email"
              />
              <label
                htmlFor="email"
                className={`absolute left-4 transition-all duration-200 
                  ${focused.email || formData.email 
                    ? '-top-6 text-sm text-brown-500'
                    : 'top-3 text-gray-400'
                  } 
                  peer-focus:-top-6 peer-focus:text-sm peer-focus:text-brown-500 font-sans`}
              >
                Email
              </label>
            </div>

            <br></br>

            {/* Password Field dengan Ikon Mata */}
            <div className="relative">
              <input
                id="password"
                name="password"
                type={showPassword ? "text" : "password"}
                required
                value={formData.password}
                onFocus={() => handleFocus('password')}
                onBlur={() => handleBlur('password')}
                onChange={handleChange}
                className="peer w-full px-4 py-3 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brown-500 focus:border-brown-500 placeholder-transparent"
                placeholder="Password"
              />
              <label
                htmlFor="password"
                className={`absolute left-4 transition-all duration-200 
                  ${focused.password || formData.password 
                    ? '-top-6 text-sm text-brown-500'
                    : 'top-3 text-gray-400'
                  } 
                  peer-focus:-top-6 peer-focus:text-sm peer-focus:text-brown-500 font-sans`}
              >
                Password
              </label>

              {/* Ikon Mata untuk toggle password */}
              <div
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-4 top-1/2 transform -translate-y-1/2 cursor-pointer text-gray-500"
              >
                {showPassword ? <FaEyeSlash /> : <FaEye />}
              </div>
            </div>

            {/* Submit Button dengan animasi */}
            <motion.div
              whileHover={{ scale: 1.02 }}
              whileTap={{ scale: 0.98 }}
            >
              <button
                type="submit"
                disabled={loading}
                className={`w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-brown-600 hover:bg-brown-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brown-500 transform transition-all duration-200 ${
                  loading ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5'
                }`}
              >
                {loading ? (
                  <motion.div
                    animate={{ rotate: 360 }}
                    transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
                    className="font-sans w-6 h-6 border-2 border-white border-t-transparent rounded-full"
                  />
                ) : (
                  'Login'
                )}
              </button>
            </motion.div>
          </form>

          {/* Forgot Password Link dengan hover effect */}
          <motion.div 
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.5 }}
            className="mt-4 text-right"
          >
            <a 
              href="./forgotPass" 
              className="text-brown-600 hover:text-brown-700 font-medium transition-colors duration-200 hover:underline font-sans"
            >
              Forgot Password?
            </a>
          </motion.div>

          {/* Divider dengan animasi */}
          <motion.div 
            initial={{ scaleX: 0 }}
            animate={{ scaleX: 1 }}
            transition={{ delay: 0.6 }}
            className="relative my-6"
          >
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t border-gray-300"></div>
            </div>
            <div className="relative flex justify-center text-sm">
              <span className="px-2 bg-white text-gray-500">Or sign in with Google</span>
            </div>
          </motion.div>

          {/* Google Sign In Button dengan animasi */}
          <motion.div 
            initial={{ y: 20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.7 }}
            className="mb-6 flex justify-center"
          >
            <motion.button
              whileHover={{ scale: 1.02 }}
              whileTap={{ scale: 0.98 }}
              onClick={handleGoogleSignIn}
              disabled={loading}
              className="w-2/4 flex items-center justify-center gap-2 py-3 px-4 border border-gray-300 rounded-md shadow-sm text-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200"
            > 
              <FcGoogle className="w-6 h-6" />Signin with Google
            </motion.button>
          </motion.div>

          {/* Register Link dengan animasi */}
          <motion.div 
            initial={{ y: 20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.8 }}
            className="mt-4 text-center"
          >
            <p className="text-sm text-gray-600 font-sans">
              Doesn't have an account?{' '}
              <a 
                href="./register" 
                className="text-brown-600 hover:text-brown-700 font-medium transition-colors duration-200 hover:underline font-sans"
              >
                Create Account
              </a>
            </p>
          </motion.div>
        </motion.div>
      </div>
    </motion.div>
  );
}