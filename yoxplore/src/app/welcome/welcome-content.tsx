'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { motion, useAnimation } from 'framer-motion';
import { useInView } from 'react-intersection-observer';
import { MicrophoneIcon } from '@heroicons/react/24/outline';
import { FaCoffee, FaBed } from 'react-icons/fa';

export default function WelcomePage() {
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  // Controls for animation
  const controls = useAnimation();
  const [ref, inView] = useInView({ triggerOnce: true, threshold: 0.2 });

  useEffect(() => {
    if (inView) {
      controls.start('visible');
    }
  }, [controls, inView]);

  const handleLoginClick = () => {
    router.push('/auth/client/login');
  };

  // Variants for staggered letter slide-left animations
  const containerVariants = {
    hidden: {},
    visible: {
      transition: {
        staggerChildren: 0.035,
      },
    },
  };

  const letterVariants = {
    hidden: { x: -100, opacity: 0 },
    visible: {
      x: 0,
      opacity: 1,
      transition: {
        duration: 0.3,
        ease: 'easeOut',
      },
    },
  };

  // Animation variant for the description text with slide-in effect
  const descriptionVariants = {
    hidden: { x: -100, opacity: 0 },
    visible: {
      x: 0,
      opacity: 1,
      transition: { delay: 0.5, duration: 0.5, ease: 'easeOut' },
    },
  };

  // Animation variant for the About Us section
  const aboutUsVariants = {
    hidden: { opacity: 0, y: 50 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: 'easeOut' } },
  };

  return (
    <div>
      {/* Navbar with sticky positioning */}
      <div
        className="w-full flex justify-between items-center p-4 text-white bg-brown-500 sticky top-0 z-10"
      >
        <div className="flex items-center space-x-4">
          <img src="/images/logo/logo.png" alt="YOXPLORE Logo" className="w-40 px-4" />
        </div>
        
        <div className="flex items-center space-x-4">
          <button
            onClick={handleLoginClick}
            className="py-1 px-4 font-semibold text-white rounded hover:bg-white hover:text-brown-500"
          >
            Login
          </button>
        </div>
      </div>

      {/* Hero section with fullscreen background */}
      <div
        className="py-72 flex flex-col items-center justify-center text-center text-white bg-cover bg-center"
        style={{ backgroundImage: "url('/images/logo/welcome.jpg')" }}
      >
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="text-7xl font-bold mb-4 flex space-x-1"
        >
          {Array.from("WELCOME TO YOXPLORE").map((char, index) => (
            <motion.span key={index} variants={letterVariants}>
              {char === " " ? "\u00A0" : char}
            </motion.span>
          ))}
        </motion.div>

        <motion.p
          variants={descriptionVariants}
          initial="hidden"
          animate="visible"
          className="text-2xl font-medium"
        >
          Find, Taste, and Explore Yogyakarta.
        </motion.p>
      </div>

      {/* About Us Section with scroll-triggered animation */}
      <motion.div
        ref={ref}
        variants={aboutUsVariants}
        initial="hidden"
        animate={controls}
        className="bg-white p-8 text-gray-800 text-left"
      >
        <h2 className="text-3xl font-semibold mb-4 text-brown-500 text-center">About Us</h2>
        <p className="text-lg leading-relaxed px-12 text-brown-600"> 
        YoXplore adalah sebuah website rekomendasi wisata di Kota Yogyakarta yang terkenal dengan kekayaan budaya, sejarah, dan keindahan alamnya. Website ini didesain untuk membantu wisatawan, baik lokal maupun mancanegara, dalam merencanakan kunjungan mereka dengan lebih mudah, efisien, dan informatif. YoTrip akan membantu pengguna dapat menemukan berbagai destinasi wisata mulai dari yang sudah populer hingga lokasi-lokasi tersembunyi yang jarang diketahui banyak orang. YoTaste menyediakan rekomendasi tempat makan terbaik mulai dari restoran berbintang hingga warung lokal yang menyajikan makanan tradisional khas Yogyakarta. YoStay yang memberikan rekomendasi hotel, penginapan, homestay, hingga villa di berbagai lokasi di Yogyakarta. YoConcert memberikan informasi terkini tentang konser, festival musik, pertunjukan teater, dan acara seni lainnya yang diadakan di Yogyakarta.
        </p>
      </motion.div>

      {/* Most Popular Places Section */}
      <div className="bg-white p-8">
      <h2 className="text-3xl font-semibold mb-6 text-brown-500 text-center py-2">Most Popular Places</h2>
      <div className="flex flex-wrap justify-center gap-28">
        {/* Card 1 */}
        <div className="w-64 shadow-lg rounded-lg overflow-hidden bg-white">
          <img
            src="/images/popular/kraton.jpg"  // Ganti dengan path gambar
            alt="Kraton Yogyakarta"
            className="w-full h-48 object-cover"
          />
          <div className="p-4 text-center">
            <h3 className="text-lg font-medium text-brown-500">Kraton Yogyakarta</h3>
          </div>
        </div>

        {/* Card 2 */}
        <div className="w-64 shadow-lg rounded-lg overflow-hidden bg-white">
          <img
            src="/images/popular/taman_sari.jpg"  // Ganti dengan path gambar
            alt="Taman Sari"
            className="w-full h-48 object-cover"
          />
          <div className="p-4 text-center">
            <h3 className="text-lg font-medium text-brown-500">Taman Sari</h3>
          </div>
        </div>

        {/* Card 3 */}
        <div className="w-64 shadow-lg rounded-lg overflow-hidden bg-white">
          <img
            src="/images/popular/pentas_wayang.jpg"  // Ganti dengan path gambar
            alt="Pentas Wayang"
            className="w-full h-48 object-cover"
          />
          <div className="p-4 text-center">
            <h3 className="text-lg font-medium text-brown-500">Pentas Wayang</h3>
          </div>
        </div>

        {/* Card 4 */}
        <div className="w-64 shadow-lg rounded-lg overflow-hidden bg-white">
          <img
            src="/images/popular/prambanan.jpg"  // Ganti dengan path gambar
            alt="Candi Prambanan"
            className="w-full h-48 object-cover"
          />
          <div className="p-4 text-center">
            <h3 className="text-lg font-medium text-brown-500">Candi Prambanan</h3>
          </div>
        </div>
        </div>
        </div>

         {/* Most Popular Foods Section */}
      <div className="bg-white p-8">
      <h2 className="text-3xl font-semibold mb-6 text-brown-500 text-center py-5">Most Popular Foods</h2>
      <div className="flex flex-wrap justify-center gap-28">
        {/* Card 1 */}
        <div className="w-64 shadow-lg rounded-lg overflow-hidden bg-white">
          <img
            src="/images/popular/kraton.jpg"  // Ganti dengan path gambar
            alt="Kraton Yogyakarta"
            className="w-full h-48 object-cover"
          />
          <div className="p-4 text-center">
            <h3 className="text-lg font-medium text-brown-500">Kraton Yogyakarta</h3>
          </div>
        </div>

        {/* Card 2 */}
        <div className="w-64 shadow-lg rounded-lg overflow-hidden bg-white">
          <img
            src="/images/popular/taman_sari.jpg"  // Ganti dengan path gambar
            alt="Taman Sari"
            className="w-full h-48 object-cover"
          />
          <div className="p-4 text-center">
            <h3 className="text-lg font-medium text-brown-500">Taman Sari</h3>
          </div>
        </div>

        {/* Card 3 */}
        <div className="w-64 shadow-lg rounded-lg overflow-hidden bg-white">
          <img
            src="/images/popular/pentas_wayang.jpg"  // Ganti dengan path gambar
            alt="Pentas Wayang"
            className="w-full h-48 object-cover"
          />
          <div className="p-4 text-center">
            <h3 className="text-lg font-medium text-brown-500">Pentas Wayang</h3>
          </div>
        </div>

        {/* Card 4 */}
        <div className="w-64 shadow-lg rounded-lg overflow-hidden bg-white">
          <img
            src="/images/popular/prambanan.jpg"  // Ganti dengan path gambar
            alt="Candi Prambanan"
            className="w-full h-48 object-cover"
          />
          <div className="p-4 text-center">
            <h3 className="text-lg font-medium text-brown-500">Candi Prambanan</h3>
          </div>
        </div>
        </div>
        </div>

        <br></br>

        {/* Footer Section */}
        <footer className="bg-brown-900 text-white py-10">
        <div className="container mx-auto px-8 flex flex-wrap justify-between gap-8">
          {/* Contact Us */}
          <div className="w-full md:w-1/3 mb-4">
            <h3 className="text-xl font-semibold mb-4">Contact us</h3>
            <ul>
              <li className="flex items-center mb-2">
                <span className="mr-2">🕒</span> Monday - Friday, 09.00 - 17.00
              </li>
              <li className="flex items-center mb-2">
                <span className="mr-2">📧</span> yoxplore@gmail.com
              </li>
              <li className="flex items-center mb-2">
                <span className="mr-2">📞</span> 08123456789
              </li>
              <li className="flex items-center mb-2">
                <span className="mr-2">📍</span> Universitas Islam Indonesia, Gedung K.H. Mas Mansyur, Daerah Istimewa Yogyakarta 55584
              </li>
            </ul>
          </div>

          {/* Products */}
          <div className="w-full md:w-1/3 mb-4">
            <h3 className="text-xl font-semibold mb-4">Products</h3>
            <ul className="space-y-2">
              <li className="hover:text-gray-300"><a href="#">YoTrip</a></li>
              <li className="hover:text-gray-300"><a href="#">YoTaste</a></li>
              <li className="hover:text-gray-300"><a href="#">YoConcert</a></li>
              <li className="hover:text-gray-300"><a href="#">YoStay</a></li>
            </ul>
          </div>

          {/* Follow Us On */}
          <div className="w-full md:w-1/3 mb-4">
            <h3 className="text-xl font-semibold mb-4">Follow us on</h3>
            <ul className="flex flex-wrap gap-4">
              <li className="hover:text-gray-300"><a href="#">Instagram</a></li>
              <li className="hover:text-gray-300"><a href="#">TikTok</a></li>
              <li className="hover:text-gray-300"><a href="#">Twitter</a></li>
              <li className="hover:text-gray-300"><a href="#">YouTube</a></li>
              <li className="hover:text-gray-300"><a href="#">Facebook</a></li>
              <li className="hover:text-gray-300"><a href="#">Telegram</a></li>
            </ul>
          </div>
        </div>

        {/* Copyright */}
        <div className="text-center mt-8 border-t border-white pt-4">
          <p>Copyright © 2024 YoXplore. All rights reserved</p>
        </div>
      </footer>

    </div>
  );
}
