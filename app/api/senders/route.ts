import { NextResponse } from 'next/server';

// Common headers for SendGrid API
const getHeaders = (apiKey: string) => ({
  'Authorization': `Bearer ${apiKey}`,
  'Content-Type': 'application/json',
});

// Transform sender data
const transformSender = (sender: any) => ({
  id: sender.id,
  email: sender.from.email,
  name: sender.from.name,
  verified: sender.verified
});

export async function GET() {
  try {
    const apiKey = process.env.SENDGRID_API_KEY;

    if (!apiKey) {
      return NextResponse.json({ error: 'SendGrid API key missing' }, { status: 500 });
    }

    const response = await fetch('https://api.sendgrid.com/v3/marketing/senders', {
      headers: getHeaders(apiKey)
    });

    if (!response.ok) {
      throw new Error(`SendGrid API error: ${response.status}`);
    }

    const data = await response.json();
    const senders = data.results?.map(transformSender) || [];

    return NextResponse.json({ senders });
  } catch (error: unknown) {
    return NextResponse.json({ error: 'Failed to fetch senders' }, { status: 500 });
  }
}