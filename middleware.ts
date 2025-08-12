import { NextResponse } from 'next/server'
import type { NextRequest } from 'next/server'

export function middleware(request: NextRequest) {
  const authUser = request.cookies.get('auth-user')?.value
  
  console.log('Middleware - Auth user:', authUser ? 'exists' : 'missing', 'Path:', request.nextUrl.pathname)

  if (request.nextUrl.pathname.startsWith('/dashboard')) {
    if (!authUser) {
      console.log('No auth user, redirecting to signin')
      return NextResponse.redirect(new URL('/signin', request.url))
    }

    try {
      const userData = JSON.parse(authUser)
      console.log('User data valid, allowing access', userData.email)
    } catch (error) {
      console.log('Invalid user data, redirecting to signin')
      return NextResponse.redirect(new URL('/signin', request.url))
    }
  }

  // Block authenticated users from accessing auth pages
  if (request.nextUrl.pathname.startsWith('/signin') || request.nextUrl.pathname.startsWith('/signup')) {
    if (authUser) {
      try {
        JSON.parse(authUser)
        console.log('Authenticated user trying to access auth page, redirecting to dashboard')
        return NextResponse.redirect(new URL('/dashboard/emails', request.url))
      } catch (error) {
        // Invalid user data, allow access to auth pages
      }
    }
  }

  if (request.nextUrl.pathname === '/') {
    if (authUser) {
      try {
        JSON.parse(authUser)
        return NextResponse.redirect(new URL('/dashboard/emails', request.url))
      } catch (error) {
        // Invalid user data, continue to signin
      }
    }
    return NextResponse.redirect(new URL('/signin', request.url))
  }

  return NextResponse.next()
}

export const config = {
  matcher: ['/', '/dashboard/:path*', '/signin', '/signup']
}