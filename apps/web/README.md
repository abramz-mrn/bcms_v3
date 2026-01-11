# BCMS Frontend (Next.js)

Next.js 22 frontend application for the BCMS (Billing & Customer Management System).

## Tech Stack

- **Next.js 22** with App Router
- **React 19**
- **TypeScript**
- **Tailwind CSS**

## Getting Started

### Prerequisites

- Node.js 20 or higher
- npm or yarn

### Installation

```bash
npm install
# or
yarn install
```

### Environment Variables

Create a `.env.local` file:

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_APP_NAME=BCMS
```

### Development Server

```bash
npm run dev
# or
yarn dev
```

Open [http://localhost:3000](http://localhost:3000) with your browser.

### Build for Production

```bash
npm run build
npm start
```

## Project Structure

```
app/
├── login/              # Login page
├── dashboard/          # Dashboard (protected)
├── layout.tsx          # Root layout
└── page.tsx           # Home page
```

## Features Implemented

- ✅ Login page with authentication
- ✅ Dashboard layout
- ✅ Basic stats cards
- ✅ Token-based authentication
- ✅ Responsive design with Tailwind

## TODO

- [ ] Customers list page
- [ ] Customer detail page
- [ ] Invoices list page
- [ ] Invoice detail page
- [ ] Products management
- [ ] Routers management
- [ ] Tickets system
- [ ] Global state management (Context API or Zustand)
- [ ] Data fetching with SWR
- [ ] Form validation
- [ ] Error handling
- [ ] Loading states
- [ ] Toast notifications

## Default Login Credentials

After seeding the backend database:

- **Email**: abramz@bcms.com
- **Password**: password123

## Learn More

- [Next.js Documentation](https://nextjs.org/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
