import { NextResponse } from 'next/server';

interface SenderEmail {
  email: string;
  name: string;
  id: string;
}

interface AirtableRecord {
  fields: {
    Send_From_Email: string;
    Location_Name: string;
  };
  id: string;
}

// Common headers for Airtable API
const getAirtableHeaders = (token: string) => ({
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json',
});

// Transform Airtable record to sender email format
const transformRecord = (record: AirtableRecord): SenderEmail => ({
  email: record.fields.Send_From_Email,
  name: record.fields.Location_Name || record.fields.Send_From_Email,
  id: record.id
});

// Remove duplicates based on email and name combination
const deduplicateEmails = (emails: SenderEmail[]): SenderEmail[] => {
  return emails.filter((email, index, self) =>
    index === self.findIndex(e => e.email === email.email && e.name === email.name)
  );
};

// Fetch all records with pagination
const fetchAllRecords = async (baseId: string, tableName: string, headers: Record<string, string>) => {
  let allRecords: any[] = [];
  let offset: string | undefined;

  do {
    const url = new URL(`https://api.airtable.com/v0/${baseId}/${encodeURIComponent(tableName)}`);
    if (offset) url.searchParams.set('offset', offset);

    const response = await fetch(url.toString(), { headers });

    if (!response.ok) {
      throw new Error(`Airtable API error: ${response.status}`);
    }

    const data = await response.json();
    allRecords = allRecords.concat(data.records);
    offset = data.offset;
  } while (offset);

  return allRecords;
};

export async function GET() {
  try {
    const { AIRTABLE_PERSONAL_ACCESS_TOKEN, AIRTABLE_BASE_ID, AIRTABLE_TABLE_NAME } = process.env;
    const tableName = AIRTABLE_TABLE_NAME || 'Sender Emails';

    if (!AIRTABLE_PERSONAL_ACCESS_TOKEN || !AIRTABLE_BASE_ID) {
      return NextResponse.json({ error: 'Airtable configuration missing' }, { status: 500 });
    }

    const headers = getAirtableHeaders(AIRTABLE_PERSONAL_ACCESS_TOKEN);
    const allRecords = await fetchAllRecords(AIRTABLE_BASE_ID, tableName, headers);

    console.log('Airtable response:', {
      recordCount: allRecords.length,
      sampleRecord: allRecords[0]
    });

    // Process records
    const allEmails = (allRecords as AirtableRecord[])
      .filter(record => record.fields.Send_From_Email)
      .map(transformRecord);

    console.log('All emails before deduplication:', allEmails.length);

    const senderEmails = deduplicateEmails(allEmails);
    console.log('Final sender emails after deduplication:', senderEmails.length);

    // Sort by name
    const sortedSenderEmails = senderEmails.sort((a, b) => a.name.localeCompare(b.name));

    return NextResponse.json({ senderEmails: sortedSenderEmails });
  } catch (error: unknown) {
    console.error('Error fetching sender emails:', error);
    return NextResponse.json(
      {
        error: 'Failed to fetch sender emails',
        details: error instanceof Error ? error.message : 'Unknown error'
      },
      { status: 500 }
    );
  }
}