import { NextRequest, NextResponse } from 'next/server'

interface CampaignData {
    campaignName: string
    subject: string
    senderId: string
    body: string
    templateId: string
    contactListId?: string
    excelFileName?: string
}

// Common headers for Airtable API
const getAirtableHeaders = (token: string) => ({
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
});

// Transform campaign data to Airtable format
const transformToAirtableData = (campaignData: CampaignData) => ({
    fields: {
        'Campaign Name': campaignData.campaignName,
        'Sender': campaignData.senderId,
        'Recipient Group': campaignData.contactListId || '',
        'Template ID': campaignData.templateId,
        'Subject': campaignData.subject,
        'Body': campaignData.body,
        'Created At': new Date().toISOString(),
        'Status': 'Sent'
    }
});

export async function POST(request: NextRequest) {
    try {
        const campaignData: CampaignData = await request.json();
        
        // Validate required environment variables
        const { AIRTABLE_BASE_ID, AIRTABLE_PERSONAL_ACCESS_TOKEN } = process.env;
        if (!AIRTABLE_BASE_ID || !AIRTABLE_PERSONAL_ACCESS_TOKEN) {
            return NextResponse.json(
                { success: false, error: 'Airtable configuration missing' },
                { status: 500 }
            );
        }

        const airtableData = transformToAirtableData(campaignData);
        const headers = getAirtableHeaders(AIRTABLE_PERSONAL_ACCESS_TOKEN);

        const response = await fetch(`https://api.airtable.com/v0/${AIRTABLE_BASE_ID}/campaigns`, {
            method: 'POST',
            headers,
            body: JSON.stringify(airtableData)
        })

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`Airtable API error: ${errorData.error?.message || 'Unknown error'}`);
        }

        const result = await response.json();

        return NextResponse.json({
            success: true,
            campaignId: result.id,
            message: 'Campaign stored successfully'
        });

    } catch (error) {
        console.error('Error storing campaign:', error);
        return NextResponse.json(
            {
                success: false,
                error: error instanceof Error ? error.message : 'Failed to store campaign'
            },
            { status: 500 }
        );
    }
}