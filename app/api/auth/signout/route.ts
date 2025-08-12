import { NextResponse } from 'next/server'

export async function POST() {
  const res = NextResponse.json({ message: 'Signed out successfully' })
  res.cookies.delete('auth-user')
  return res
}