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
      <div className="w-full flex justify-between items-center p-4 text-white bg-brown-400 sticky top-0 z-10">
        <img src="/images/logo/logo.png" alt="YOXPLORE Logo" className="w-40 px-4" />
        <button
          onClick={handleLoginClick}
          className="py-1 px-4 font-semibold text-white rounded hover:text-brown-900 hover:font-semibold font-sans"
        >
          Login
        </button>
      </div>

      {/* Hero Section */}
      <div
        className="py-56 flex flex-col justify-center text-left text-white bg-cover bg-center"
        style={{ backgroundImage: "url('/images/banners/bg landing.jpg')" }}>
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="text-7xl font-bold mb-4 flex space-x-1 pl-20 font-sans">
          {Array.from("Explore the sight").map((char, index) => (
            <motion.span key={index} variants={letterVariants}>
              {char === " " ? "\u00A0" : char}
            </motion.span>
          ))}
        </motion.div>

        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="text-7xl font-bold mb-4 flex space-x-1 pl-20 font-sans">
          {Array.from("of the Yogyakarta").map((char, index) => (
            <motion.span key={index} variants={letterVariants}>
              {char === " " ? "\u00A0" : char}
            </motion.span>
          ))}
        </motion.div>

        <motion.p
          variants={scrollVariants}
          initial="hidden"
          animate="visible"
          className="text-2xl font-semibold pl-20 font-sans">
          Find, Taste, and Explore Yogyakarta.
        </motion.p>
      </div>

       {/* Search Bar Section */}
       <div className="flex justify-center mt-[-30px] mb-1 px-4">
        <div className="w-full max-w-5xl">
          <form>
            <div className="relative">
              <input
                type="text"
                placeholder="Where you want to go?"
                className="w-full p-4 text-base border-2 border-gray-300 rounded-2xl focus:outline-none focus:ring-1 focus:ring-brown-400 focus:border-brown-400"
              />
              <button
                type="submit"
                className="absolute right-3 top-1/2 transform -translate-y-1/2 font-semibold text-brown-400 hover:text-brown-900 font-sans px-3"
              >
                Search
              </button>
            </div>
          </form>
        </div>
      </div>

      {/* Our Features Section */}
      <motion.div
        ref={aboutRef}
        variants={containerVariants}
        initial="hidden"
        animate={aboutUsControls}
        className="bg-white p-8 text-gray-800"
      >
        <motion.h2 className="text-4xl font-bold font-sans mb-4 text-brown-500 text-center" variants={scrollVariants}>
          Our Features
        </motion.h2>
        <motion.h5 className="text-lg font-bold font-sans mb-4 text-gray-600 text-center" variants={scrollVariants}>
          Try variety benefits when using our features
        </motion.h5>

        <div className="flex justify-center gap-10 mt-12 font-sans">
          {[
            { icon: "📍", title: "YoTrip", desc: "We provide the best places in Yogyakarta" },
            { icon: "🍴", title: "YoTaste", desc: "We provide the best foods in Yogyakarta" },
            { icon: "🛏️", title: "YoStay", desc: "We provide the best hotels in Yogyakarta" },
            { icon: "🎤", title: "YoConcert", desc: "We provide the best show in Yogyakarta" },
          ].map((feature, index) => (
            <motion.div
              key={index}
              className="flex flex-col items-center text-center"
              variants={scrollVariants}
            >
              <div className="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center text-2xl text-brown-500 mb-4">
                {feature.icon}
              </div>
              <h3 className="text-xl font-semibold text-brown-500">{feature.title}</h3>
              <p className="text-gray-600 mt-2">{feature.desc}</p>
            </motion.div>
          ))}
        </div>
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
