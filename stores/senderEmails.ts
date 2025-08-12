import { create } from 'zustand'

interface SenderEmail {
    email: string
    name: string
    id: string
}

interface SenderEmailsState {
    senderEmails: SenderEmail[]
    isLoading: boolean
    error: string | null
    fetchSenderEmails: () => Promise<void>
    refreshSenderEmails: () => Promise<void>
}

export const useSenderEmailsStore = create<SenderEmailsState>((set, get) => ({
    senderEmails: [],
    isLoading: false,
    error: null,

    fetchSenderEmails: async () => {
        // Don't fetch if already loading or if we already have data
        if (get().isLoading || get().senderEmails.length > 0) return;

        set({ isLoading: true, error: null });
        
        try {
            const response = await fetch('/api/sender-emails');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.senderEmails && Array.isArray(data.senderEmails)) {
                set({ 
                    senderEmails: data.senderEmails,
                    isLoading: false,
                    error: null 
                });
            } else {
                throw new Error('Invalid response format');
            }
        } catch (error: any) {
            console.error('Error fetching sender emails:', error);
            set({ 
                senderEmails: [
                    { email: '414irfann@gmail.com', name: 'Default Sender', id: 'default' }
                ],
                isLoading: false,
                error: error.message 
            });
        }
    },

    refreshSenderEmails: async () => {
        // Force refresh - clear existing data first
        set({ senderEmails: [], isLoading: true, error: null });
        
        try {
            const response = await fetch('/api/sender-emails');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.senderEmails && Array.isArray(data.senderEmails)) {
                set({ 
                    senderEmails: data.senderEmails,
                    isLoading: false,
                    error: null 
                });
            } else {
                throw new Error('Invalid response format');
            }
        } catch (error: any) {
            console.error('Error refreshing sender emails:', error);
            set({ 
                senderEmails: [
                    { email: '414irfann@gmail.com', name: 'Default Sender', id: 'default' }
                ],
                isLoading: false,
                error: error.message 
            });
        }
    }
}));