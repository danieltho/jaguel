# Frontend Architecture Memory - Ophelia E-commerce

## Project Stack
- React 19 + Inertia.js v2 + Laravel 12
- Vite 7, Tailwind CSS 4, HeadlessUI, Phosphor Icons, Motion (Framer)
- CSS Modules for component styles
- Entry point: `resources/js/app.jsx` (Inertia glob resolves `./Pages/**/*.jsx`)

## Domain Entities (from Laravel Models)
Category, CategoryGroup, Color, Coupon, CouponUsage, Customer, Order, OrderItem,
PaymentMethod, Product, ProductVariant, Size, Tag, User

## Business Domains Identified
- **catalog**: Products, ProductVariants, Categories, CategoryGroups, Colors, Sizes, Tags
- **orders**: Orders, OrderItems, PaymentMethods
- **coupons**: Coupons, CouponUsage
- **customers**: Customers (public-facing user/auth)
- **users**: Admin users (if admin panel exists)

## Inertia Constraint
`app.jsx` resolves pages via `import.meta.glob('./Pages/**/*.jsx', { eager: true })`
Pages MUST remain under `resources/js/Pages/` for Inertia routing to work.
Page names map 1:1 to Laravel `Inertia::render('PageName')` calls.

## Current Violations Found (Jan 2025 audit)
- Flat `Shared/` dir used as global dump -- categories API, sidebar context, and layout all co-located
- `fetchFeaturedProducts.js` and `fetchCategories.js` contain hardcoded mock data in global scope
- SideBar component deeply nested: `Shared/components/header/sidebar/feature/SideBar.jsx`
- No feature-based folder structure; all code in `Shared/`
- Category context provided at layout level but only consumed by sidebar

## Naming Conventions Observed
- PascalCase for components: `Header.jsx`, `Logo.jsx`, `SideBar.jsx`
- camelCase for hooks: `useSidebar.js`, `useCategories.js`
- camelCase for API files: `fetchCategories.js`
- CSS Modules: `ComponentName.module.css`

## See Also
- [architecture-plan.md](./architecture-plan.md) - Detailed restructuring plan
