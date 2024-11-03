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

  // Variants for staggered letter slide-up animations with faster timing
  const containerVariants = {
    hidden: {},
    visible: {
      transition: {
        staggerChildren: 0.035,
      },
    },
  };

  const letterVariants = {
    hidden: { y: 50, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: {
        duration: 0.2,
        ease: 'easeOut',
      },
    },
  };

  // Animation variant for the About Us section
  const aboutUsVariants = {
    hidden: { opacity: 0, y: 50 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: 'easeOut' } },
  };

  const featureVariants = {
    hidden: { opacity: 0, y: 50 },
    visible: { opacity: 1, y: 0, transition: { duration: 1, ease: 'easeOut' } },
  };

  const CoffeeIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      {/* SVG path dari ikon */}
    </svg>
  );

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
          initial={{ y: 30, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ delay: 0.7, duration: 0.5 }}
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
        <p className="text-lg leading-relaxed"> 
        YoXplore is a travel recommendation website in the city of Yogyakarta which is famous for its rich culture, history, and natural beauty. This website is designed to help tourists, both local and foreign, in planning their visits more easily, efficiently, and informatively. YoTrip will help users find various tourist destinations ranging from those that are already popular to hidden locations that are rarely known to many people. YoTaste provides recommendations for the best places to eat ranging from starred restaurants to local stalls serving traditional Yogyakarta food. YoStay provides recommendations for hotels, lodgings, homestays, and villas in various locations in Yogyakarta. YoConcert provides the latest information on concerts, music festivals, theater performances, and other art events held in Yogyakarta.
        </p>
      </motion.div>
    </div>
  );
}
