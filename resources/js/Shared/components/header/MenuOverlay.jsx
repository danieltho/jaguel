import { motion, AnimatePresence } from 'motion/react';
import { useSidebar } from '../../hook/useSidebar';

export default function MenuOverlay() {
  const { isMenuOpen, closeMenu } = useSidebar();

  return (
    <AnimatePresence>
      {isMenuOpen && (
        <motion.div
          key="overlay"
          initial={{ opacity: 0 }}
          animate={{ opacity: 0.7 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.3, ease: 'easeOut' }}
          className='fixed inset-0 bg-black z-20'
          onClick={closeMenu}
        />
      )}
    </AnimatePresence>
  );
}