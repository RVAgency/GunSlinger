import { NextResponse } from 'next/server';

// Common headers for SendGrid API
const getHeaders = (apiKey: string) => ({
  'Authorization': `Bearer ${apiKey}`,
  'Content-Type': 'application/json',
});

// Transform design data to template format
const transformDesignToTemplate = (design: any) => ({
  id: design.id,
  name: design.name,
  subject: design.subject || 'Design template'
});

export async function GET() {
  try {
    const apiKey = process.env.SENDGRID_API_KEY;

    if (!apiKey) {
      return NextResponse.json({ error: 'SendGrid API key missing' }, { status: 500 });
    }

    const response = await fetch('https://api.sendgrid.com/v3/designs', {
      headers: getHeaders(apiKey)
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.log('Designs API error details:', errorText);
      throw new Error(`SendGrid API error: ${response.status} - ${errorText}`);
    }

    const data = await response.json();
    const templates = data.result?.map(transformDesignToTemplate) || [];

    return NextResponse.json({ templates });
  } catch (error: unknown) {
    console.error('Error fetching templates:', error);
    return NextResponse.json({ error: 'Failed to fetch templates' }, { status: 500 });
  }
}