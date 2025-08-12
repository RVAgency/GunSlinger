import { NextResponse } from 'next/server';

// Common headers for SendGrid API
const getHeaders = (apiKey: string) => ({
  'Authorization': `Bearer ${apiKey}`,
  'Content-Type': 'application/json'
});

// Transform contact data for SendGrid format
const transformContact = (contact: any) => ({
  email: contact.email,
  first_name: contact.firstName || '',
  last_name: contact.lastName || '',
  ...(contact.phone && { phone_number: contact.phone })
});

// Transform list data for response
const transformList = (list: any) => {
  const parts = list.name.split('|');
  return {
    id: list.id,
    name: parts[0],
    contact_count: list.contact_count,
    source_file_name: parts[1] || null
  };
};

export async function POST(request: Request) {
  try {
    const { name, contacts, sourceFileName } = await request.json();
    const apiKey = process.env.SENDGRID_API_KEY;
    
    if (!apiKey) {
      return NextResponse.json({ error: 'SendGrid API key missing' }, { status: 500 });
    }

    const headers = getHeaders(apiKey);
    const listNameWithFile = `${name}|${sourceFileName}`;

    // Create contact list
    const listResponse = await fetch('https://api.sendgrid.com/v3/marketing/lists', {
      method: 'POST',
      headers,
      body: JSON.stringify({ name: listNameWithFile })
    });

    if (!listResponse.ok) {
      const error = await listResponse.json();
      throw new Error(error.errors?.[0]?.message || 'Failed to create contact list');
    }

    const contactList = await listResponse.json();

    // Add contacts if provided
    if (contacts?.length > 0) {
      const contactsResponse = await fetch('https://api.sendgrid.com/v3/marketing/contacts', {
        method: 'PUT',
        headers,
        body: JSON.stringify({
          list_ids: [contactList.id],
          contacts: contacts.map(transformContact)
        })
      });

      if (!contactsResponse.ok) {
        const error = await contactsResponse.json();
        throw new Error(error.errors?.[0]?.message || 'Failed to add contacts');
      }
    }

    return NextResponse.json({ contactList });
  } catch (error: unknown) {
    return NextResponse.json(
      { error: error instanceof Error ? error.message : 'Failed to create contact list' },
      { status: 500 }
    );
  }
}

export async function GET() {
  try {
    const apiKey = process.env.SENDGRID_API_KEY;
    
    if (!apiKey) {
      return NextResponse.json({ error: 'SendGrid API key missing' }, { status: 500 });
    }

    const response = await fetch('https://api.sendgrid.com/v3/marketing/lists', {
      headers: getHeaders(apiKey)
    });

    if (!response.ok) {
      throw new Error(`SendGrid API error: ${response.status}`);
    }

    const data = await response.json();
    const contactLists = data.result?.map(transformList) || [];

    return NextResponse.json({ contactLists });
  } catch (error: unknown) {
    return NextResponse.json({ error: 'Failed to fetch contact lists' }, { status: 500 });
  }
}