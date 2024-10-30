export const validateRating = (rating: number): boolean => {
    return rating >= 0 && rating <= 5;
  };
  
  export const calculateAverageRating = (reviews: Review[]): number => {
    if (reviews.length === 0) return 0;
    
    const sum = reviews.reduce((acc, review) => acc + review.rating, 0);
    return Number((sum / reviews.length).toFixed(1));
  };