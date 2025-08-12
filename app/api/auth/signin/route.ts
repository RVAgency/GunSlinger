import { NextRequest, NextResponse } from 'next/server'
import bcrypt from 'bcryptjs'

export async function POST(request: NextRequest) {
  try {
    const { email, password } = await request.json()

    if (!email || !password) {
      return NextResponse.json({ error: 'Email and password required' }, { status: 400 })
    }

    const response = await fetch(`https://api.airtable.com/v0/${process.env.AIRTABLE_BASE_ID}/auth?filterByFormula={email}="${email}"`, {
      headers: {
        'Authorization': `Bearer ${process.env.AIRTABLE_PERSONAL_ACCESS_TOKEN}`
      }
    })

    const data = await response.json()
    const user = data.records?.[0]

    if (!user) {
      return NextResponse.json({ error: 'Invalid credentials' }, { status: 401 })
    }

    if (!user.fields.status) {
      return NextResponse.json({ error: 'Account not active' }, { status: 401 })
    }

    const isValid = await bcrypt.compare(password, user.fields.password)
    if (!isValid) {
      return NextResponse.json({ error: 'Invalid credentials' }, { status: 401 })
    }

    const userData = JSON.stringify({ userId: user.id, email: user.fields.email })

    const res = NextResponse.json({ message: 'Signed in successfully' })

    res.cookies.set('auth-user', userData, {
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'lax',
      path: '/',
      maxAge: 7 * 24 * 60 * 60 // 7 days in seconds
    })

    return res

  } catch (error) {
    return NextResponse.json({ error: 'Server error' }, { status: 500 })
  }
}