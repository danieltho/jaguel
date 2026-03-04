import { useEffect, useRef } from 'react';

export const useInfinity = ({
  hasMore,
  loading,
  onLoadMore,
  rootMargin = '200px'
}) => {
  const sentinelRef = useRef(null);

  useEffect(() => {
    if (!sentinelRef.current) return;
    if (loading || !hasMore) return;

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          onLoadMore();
        }
      },
      { rootMargin }
    );

    observer.observe(sentinelRef.current);

    return () => observer.disconnect();
  }, [loading, hasMore, onLoadMore, rootMargin]);

  return sentinelRef;
};