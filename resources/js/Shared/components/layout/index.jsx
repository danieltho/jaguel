import { SideBarProvider } from '../../context/SideBarContext';
import { CategoryProvider } from '../../context/CategoryContext';
import Header from '../header/Header';
import Footer from '../Footer/Footer';
import ErrorBoundary from '../ErrorBoundary';

const Template = ({ children, isHomePage = false }) => {
    return (
        <ErrorBoundary>
            <SideBarProvider>
                <CategoryProvider>
                    <main className="bg-neutral-50 min-h-screen">
                        <Header isHomePage={isHomePage} />
                        {children}
                        <Footer />
                    </main>
                </CategoryProvider>
            </SideBarProvider>
        </ErrorBoundary>
    );
};

export default Template;
