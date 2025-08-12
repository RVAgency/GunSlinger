import { NextRequest, NextResponse } from 'next/server'
import bcrypt from 'bcryptjs'

export async function POST(request: NextRequest) {
  try {
    const { email, password } = await request.json()

    if (!email || !password) {
      return NextResponse.json({ error: 'Email and password required' }, { status: 400 })
    }

    const hashedPassword = await bcrypt.hash(password, 10)

    const response = await fetch(`https://api.airtable.com/v0/${process.env.AIRTABLE_BASE_ID}/auth`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${process.env.AIRTABLE_PERSONAL_ACCESS_TOKEN}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        fields: {
          email,
          password: hashedPassword,
          status: false
        }
      })
    })

    if (!response.ok) {
      const error = await response.json()
      console.error('Airtable error:', error)
      return NextResponse.json({ 
        error: error.error?.message || 'Failed to create account' 
      }, { status: 500 })
    }

    return NextResponse.json({ message: 'Account created successfully' })

  } catch (error) {
    console.error('Signup error:', error)
    return NextResponse.json({ 
      error: error instanceof Error ? error.message : 'Server error' 
    }, { status: 500 })
  }
}