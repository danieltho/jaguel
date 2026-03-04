import { createContext, useContext} from 'react';
import { useCategories } from '../hook/useCategories';


const CategoryContext = createContext(null);

export const CategoryProvider = ({ children }) => {
  const categoryState = useCategories();

  return (
    <CategoryContext.Provider value={categoryState}>
      {children}
    </CategoryContext.Provider>
  );
};

export const useCategoryContext = () => useContext(CategoryContext);