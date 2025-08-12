import { NextResponse } from 'next/server';

// Common headers for SendGrid API
const getHeaders = (apiKey: string) => ({
    'Authorization': `Bearer ${apiKey}`,
    'Content-Type': 'application/json',
});

export async function POST(request: Request) {
    try {
        const { name, subject, senderEmail, listIds, templateId, body } = await request.json();
        const apiKey = process.env.SENDGRID_API_KEY;
        const suppressionId = process.env.SENDGRID_SUPPRESSION_ID;

        if (!apiKey) {
            return NextResponse.json({ error: 'SendGrid API key missing' }, { status: 500 });
        }

        // Validate required fields
        if (!name || !senderEmail || !listIds || listIds.length === 0) {
            return NextResponse.json({ error: 'Missing required fields: name, senderEmail, or listIds' }, { status: 400 });
        }



        const headers = getHeaders(apiKey);

        // Fetch sender info
        const sendersResponse = await fetch('https://api.sendgrid.com/v3/marketing/senders', { headers });

        if (!sendersResponse.ok) {
            throw new Error('Failed to fetch senders');
        }

        const sendersData = await sendersResponse.json();
        const sender = sendersData.find((s: any) => s.from?.email === senderEmail);

        if (!sender) {
            throw new Error(`Sender email ${senderEmail} not found or not verified`);
        }

        // Validate IDs
        if (!sender.id || typeof sender.id !== 'number') {
            throw new Error(`Invalid sender ID: ${sender.id}`);
        }

        if (templateId && (!templateId || typeof templateId !== 'string')) {
            throw new Error(`Invalid template ID: ${templateId}`);
        }

        // Validate list IDs
        const validListIds = listIds.filter((id: any) => id && typeof id === 'string');
        if (validListIds.length === 0) {
            throw new Error(`Invalid list IDs: ${JSON.stringify(listIds)}`);
        }

        // Validate suppression group ID
        if (!suppressionId) {
            return NextResponse.json({ error: 'SendGrid suppression group ID missing' }, { status: 500 });
        }

        const suppressionGroupId = parseInt(suppressionId);
        if (isNaN(suppressionGroupId)) {
            return NextResponse.json({ error: 'Invalid suppression group ID format' }, { status: 500 });
        }

        // Build email config based on template vs custom content
        const emailConfig: any = {
            sender_id: sender.id,
            generate_plain_content: true,
            suppression_group_id: suppressionGroupId
        };

        if (templateId) {
            emailConfig.design_id = templateId;
        } else {
            emailConfig.subject = subject;
            const content = body || 'Default content';
            emailConfig.html_content = content.includes('<html>')
                ? content
                : `<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body>${content.replace(/\n/g, '<br>')}</body></html>`;
        }

        const campaignPayload = {
            name,
            send_to: {
                list_ids: validListIds
            },
            email_config: emailConfig
        };



        // Create campaign
        const response = await fetch('https://api.sendgrid.com/v3/marketing/singlesends', {
            method: 'POST',
            headers,
            body: JSON.stringify(campaignPayload)
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.errors?.[0]?.message || 'Failed to create campaign');
        }

        const campaign = await response.json();

        // Prepare parallel operations
        const operations = [];

        // Add subject update if needed
        if (templateId && subject?.trim()) {
            operations.push(
                fetch(`https://api.sendgrid.com/v3/marketing/singlesends/${campaign.id}`, {
                    method: 'PATCH',
                    headers,
                    body: JSON.stringify({ email_config: { subject } })
                }).then(res => {
                    if (!res.ok) throw new Error('Subject update failed');
                    return res;
                })
            );
        }

        // Execute subject update if needed
        if (operations.length > 0) {
            await Promise.all(operations);
        }

        // Schedule campaign
        const sendResponse = await fetch(`https://api.sendgrid.com/v3/marketing/singlesends/${campaign.id}/schedule`, {
            method: 'PUT',
            headers,
            body: JSON.stringify({ send_at: 'now' })
        });

        if (!sendResponse.ok) {
            const error = await sendResponse.json();
            throw new Error(`Schedule failed: ${JSON.stringify(error)}`);
        }

        return NextResponse.json({ campaign });
    } catch (error: unknown) {
        return NextResponse.json(
            { error: error instanceof Error ? error.message : 'Failed to create campaign' },
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

        const response = await fetch('https://api.sendgrid.com/v3/marketing/singlesends', {
            headers: getHeaders(apiKey)
        });

        if (!response.ok) {
            throw new Error(`SendGrid API error: ${response.status}`);
        }

        const data = await response.json();
        return NextResponse.json({ campaigns: data.result });
    } catch (error: unknown) {
        return NextResponse.json(
            { error: 'Failed to fetch campaigns' },
            { status: 500 }
        );
    }
}