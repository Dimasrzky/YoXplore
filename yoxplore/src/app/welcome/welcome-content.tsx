'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { motion, useAnimation } from 'framer-motion';
import { useInView } from 'react-intersection-observer';

export default function WelcomePage() {
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  const aboutUsControls = useAnimation();
  const placesControls = useAnimation();
  const foodsControls = useAnimation();

  const [aboutRef, aboutInView] = useInView({ triggerOnce: true, threshold: 0.2 });
  const [placesRef, placesInView] = useInView({ triggerOnce: true, threshold: 0.2 });
  const [foodsRef, foodsInView] = useInView({ triggerOnce: true, threshold: 0.2 });

  useEffect(() => {
    if (aboutInView) aboutUsControls.start('visible');
  }, [aboutUsControls, aboutInView]);

  useEffect(() => {
    if (placesInView) placesControls.start('visible');
  }, [placesControls, placesInView]);

  useEffect(() => {
    if (foodsInView) foodsControls.start('visible');
  }, [foodsControls, foodsInView]);

  const handleLoginClick = () => {
    router.push('/auth/client/login');
  };

  const containerVariants = {
    hidden: {},
    visible: { transition: { staggerChildren: 0.035 } },
  };

  const letterVariants = {
    hidden: { y: 100, opacity: 0, scale: 0.5 },
    visible: { 
      y: 0, 
      opacity: 1, 
      scale: 1, 
      transition: { duration: 0.3, ease: 'easeOut' } 
    },
  };

  const scrollVariants = {
    hidden: { opacity: 0, y: 50 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: 'easeOut' } },
  };

  const itemVariants = {
    hidden: { opacity: 0, y: 50 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: 'easeOut' } },
  };

  return (
    <div>
      {/* Navbar */}
      <div className="w-full flex justify-between items-center p-4 text-white bg-brown-500 sticky top-0 z-10">
        <img src="/images/logo/logo.png" alt="YOXPLORE Logo" className="w-40 px-4" />
        <button
          onClick={handleLoginClick}
          className="py-1 px-4 font-semibold text-white rounded hover:bg-white hover:text-brown-500"
        >
          Login
        </button>
      </div>

      {/* Hero Section */}
      <div
        className="py-72 flex flex-col items-center justify-center text-center text-white bg-cover bg-center"
        style={{ backgroundImage: "url('/images/logo/welcome.jpg')" }}>
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="text-7xl font-bold mb-4 flex space-x-1">
          {Array.from("WELCOME TO YOXPLORE").map((char, index) => (
            <motion.span key={index} variants={letterVariants}>
              {char === " " ? "\u00A0" : char}
            </motion.span>
          ))}
        </motion.div>

        <motion.p
          variants={scrollVariants}
          initial="hidden"
          animate="visible"
          className="text-2xl font-medium">
          Find, Taste, and Explore Yogyakarta.
        </motion.p>
      </div>

      {/* About Us Section */}
      <motion.div
        ref={aboutRef}
        variants={containerVariants}
        initial="hidden"
        animate={aboutUsControls}
        className="bg-white p-8 text-gray-800">
        <motion.h2 className="text-3xl font-semibold mb-4 text-brown-500 text-center" variants={scrollVariants}>
          About Us
        </motion.h2>
        <motion.p className="text-lg leading-relaxed px-12 text-brown-600 text-justify" variants={scrollVariants}>
          YoXplore adalah sebuah website rekomendasi wisata di Kota Yogyakarta yang terkenal dengan kekayaan budaya, sejarah, dan keindahan alamnya. Website ini didesain untuk membantu wisatawan, baik lokal maupun mancanegara, dalam merencanakan kunjungan mereka dengan lebih mudah, efisien, dan informatif. YoTrip akan membantu pengguna dapat menemukan berbagai destinasi wisata mulai dari yang sudah populer hingga lokasi-lokasi tersembunyi yang jarang diketahui banyak orang. YoTaste menyediakan rekomendasi tempat makan terbaik mulai dari restoran berbintang hingga warung lokal yang menyajikan makanan tradisional khas Yogyakarta. YoStay yang memberikan rekomendasi hotel, penginapan, homestay, hingga villa di berbagai lokasi di Yogyakarta. YoConcert memberikan informasi terkini tentang konser, festival musik, pertunjukan teater, dan acara seni lainnya yang diadakan di Yogyakarta.
        </motion.p>
      </motion.div>

      {/* Most Popular Places Section */}
      <motion.div
        ref={placesRef}
        variants={containerVariants}
        initial="hidden"
        animate={placesControls}
        className="bg-white p-8">
        <motion.h2 className="text-3xl font-semibold mb-6 text-brown-500 text-center py-2" variants={itemVariants}>
          Most Popular Places
        </motion.h2>
        <div className="flex flex-wrap justify-center gap-28">
          {["kraton", "taman_sari", "pentas_wayang", "prambanan"].map((place) => (
            <motion.div
              key={place}
              className="w-64 shadow-lg rounded-lg overflow-hidden bg-white transform group hover:scale-105 hover:shadow-xl transition-all duration-300 ease-in-out"
              variants={itemVariants}>
              <motion.img
                src={`/images/popular/${place}.jpg`}
                alt={place}
                className="w-full h-48 object-cover transition-transform duration-500 ease-in-out group-hover:scale-110"/>
              <div className="p-4 text-center">
                <h3 className="text-lg font-medium text-brown-500">{place.replace("_", " ")}</h3>
              </div>
            </motion.div>
          ))}
        </div>
      </motion.div>

      {/* Most Popular Foods Section */}
      <motion.div
        ref={foodsRef}
        variants={containerVariants}
        initial="hidden"
        animate={foodsControls}
        className="bg-white p-8"
      >
        <motion.h2 className="text-3xl font-semibold mb-6 text-brown-500 text-center py-2" variants={itemVariants}>
          Most Popular Foods
        </motion.h2>
        <div className="flex flex-wrap justify-center gap-28">
          {["gudeg", "sate_klathak", "bakpia", "es_dawe"].map((food) => (
            <motion.div
              key={food}
              className="w-64 shadow-lg rounded-lg overflow-hidden bg-white transform group hover:scale-105 hover:shadow-xl transition-all duration-300 ease-in-out"
              variants={itemVariants}>
              <motion.img
                src={`/images/popular/${food}.jpg`}
                alt={food}
                className="w-full h-48 object-cover transition-transform duration-500 ease-in-out group-hover:scale-110"/>
              <div className="p-4 text-center">
                <h3 className="text-lg font-medium text-brown-500">{food.replace("_", " ")}</h3>
              </div>
            </motion.div>
          ))}
        </div>
      </motion.div>

      {/* Footer Section */}
      <footer className="bg-brown-900 text-white py-11">
        <div className="container mx-auto px-10 grid grid-cols-1 md:grid-cols-3 gap-10">
          
          {/* Contact Us */}
          <div className="flex flex-col space-y-4">
            <h3 className="text-xl font-semibold mb-2">Contact Us</h3>
            <ul className="space-y-2 text-sm">
              <li>
                <span className="font-medium">Time:</span> Monday - Friday, 09.00 - 17.00
              </li>
              <li>
                <span className="font-medium">Email:</span> yoxplore@gmail.com
              </li>
              <li>
                <span className="font-medium">Phone:</span> 08123456789
              </li>
              <li>
                <span className="font-medium">Address:</span> Universitas Islam Indonesia, Yogyakarta
              </li>
            </ul>
          </div>

          {/* Products */}
          <div className="flex flex-col space-y-4">
            <h3 className="text-xl font-semibold mb-2">Products</h3>
            <ul className="space-y-2 text-sm">
              <li className="hover:text-gray-300">
                <a href="#">YoTrip</a>
              </li>
              <li className="hover:text-gray-300">
                <a href="#">YoTaste</a>
              </li>
              <li className="hover:text-gray-300">
                <a href="#">YoConcert</a>
              </li>
              <li className="hover:text-gray-300">
                <a href="#">YoStay</a>
              </li>
            </ul>
          </div>

          {/* Follow Us On */}
          <div className="flex flex-col space-y-4">
            <h3 className="text-xl font-semibold mb-2">Follow Us On</h3>
            <ul className="space-y-2 text-sm">
              <li className="hover:text-gray-300">
                <a href="#">Instagram</a>
              </li>
              <li className="hover:text-gray-300">
                <a href="#">TikTok</a>
              </li>
              <li className="hover:text-gray-300">
                <a href="#">Twitter</a>
              </li>
              <li className="hover:text-gray-300">
                <a href="#">YouTube</a>
              </li>
            </ul>
          </div>
        </div>
      </footer>
      {/* Copyright Section */}
      <div className="text-center mt-1px border-t bg-brown-900 py-4">
          <p className="text-sm text-white">&copy; 2024 YoXplore. All rights reserved</p>
        </div>
    </div>
  );
}
