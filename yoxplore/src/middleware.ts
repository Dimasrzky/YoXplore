import { withAuth } from 'next-auth/middleware';
import { NextResponse } from 'next/server';

export default withAuth(
  function middleware(req) {
    const token = req.nextauth.token;
    const isAdmin = token?.role === 'admin';
    const pathName = req.nextUrl.pathname;

    // Only allow admin users to access admin routes
    if (pathName.startsWith('/admin') && !isAdmin) {
      return NextResponse.redirect(new URL('/auth/admin/login', req.url));
    }
  },
  {
    callbacks: {
      authorized: ({ token }) => !!token
    }
  }
);

export const config = {
  matcher: ['/admin/:path*']
};