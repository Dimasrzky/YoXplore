import { Hotel, Room, Review } from '../types';

// Check room availability
export const checkRoomAvailability = (room: Room): boolean => {
  return room.available > 0;
};

// Get available rooms
export const getAvailableRooms = (hotel: Hotel): Room[] => {
  return hotel.rooms.filter(room => checkRoomAvailability(room));
};

// Calculate average rating
export const calculateAverageRating = (reviews: Review[]): number => {
  if (reviews.length === 0) return 0;
  const total = reviews.reduce((acc, review) => acc + review.rating, 0);
  return Number((total / reviews.length).toFixed(1));
};

// Get room price range
export const getRoomPriceRange = (hotel: Hotel): { min: number; max: number } => {
  const prices = hotel.rooms.map(room => room.price);
  return {
    min: Math.min(...prices),
    max: Math.max(...prices)
  };
};

// Format room price
export const formatRoomPrice = (price: number): string => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
  }).format(price);
};

// Get total room capacity
export const getTotalRoomCapacity = (hotel: Hotel): number => {
  return hotel.rooms.reduce((total, room) => total + room.capacity, 0);
};

// Get room by type
export const getRoomByType = (hotel: Hotel, type: string): Room | undefined => {
  return hotel.rooms.find(room => room.type.toLowerCase() === type.toLowerCase());
};

// Check if hotel has specific facility
export const hasFacility = (hotel: Hotel, facility: string): boolean => {
  return hotel.facilities.some(f => f.toLowerCase() === facility.toLowerCase());
};