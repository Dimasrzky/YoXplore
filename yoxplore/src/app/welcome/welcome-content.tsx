'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { motion } from 'framer-motion';

export default function WelcomePage() {
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  const handleLoginClick = () => {
    router.push('/login');
  };

  // Variants for staggered letter slide-and-fade animations with faster timing
  const containerVariants = {
    hidden: {},
    visible: {
      transition: {
        staggerChildren: 0.05, // Faster stagger between each letter
      },
    },
  };

  const letterVariants = {
    hidden: { y: 50, opacity: 0 }, // Start from below, invisible
    visible: { y: 0, opacity: 1, transition: { duration: 0.3, ease: "easeOut" } }, // Faster slide and fade-in
  };

  return (
    <div
      className="w-screen h-screen flex flex-col"
      style={{ backgroundColor: '#A0522D' }}
    >
      {/* Navbar with logo instead of text on the left side */}
      <div className="w-full flex justify-between items-center p-4 text-white bg-brown-500">
        {/* Updated logo on the left side of the navbar */}
        <div className="flex items-center space-x-4">
          <img src="/images/logo/logo.png" alt="YOXPLORE Logo" className="w-40 px-4" />
        </div>
        
        {/* Login button on the right side of the navbar */}
        <div className="flex items-center space-x-4">
          <button
            onClick={handleLoginClick}
            className="py-1 px-4 font-semibold text-white rounded hover:bg-white hover:text-brown-500"
          >
            Login
          </button>
        </div>
      </div>

      {/* Hero section with increased font sizes */}
      <div
        className="flex-grow flex flex-col items-center justify-center text-center text-white bg-cover bg-center"
        style={{ backgroundImage: "url('/images/logo/welcome.jpg')" }}
      >
        {/* Container for letter animations */}
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="visible"
          className="text-7xl font-bold mb-4 flex space-x-1"
        >
          {Array.from("WELCOME TO YOXPLORE").map((char, index) => (
            <motion.span key={index} variants={letterVariants}>
              {char === " " ? "\u00A0" : char} {/* Preserve spaces */}
            </motion.span>
          ))}
        </motion.div>

        {/* Animating the subtitle text with larger font */}
        <motion.p
          initial={{ y: 30, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ delay: 0.8, duration: 0.5 }} // Faster transition for subtitle
          className="text-2xl font-medium"
        >
          Find, Taste, and Explore Yogyakarta.
        </motion.p>
      </div>
    </div>
  );
}
