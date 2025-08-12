import { create } from 'zustand'

interface ContactList {
    id: string
    name: string
    contact_count: number
    source_file_name: string
}

interface ContactListsState {
    contactLists: ContactList[]
    isLoading: boolean
    error: string | null
    isContinuousLoading: boolean
    fetchContactLists: (force?: boolean) => Promise<void>
    continuousFetch: (listName: string, expectedCount: number) => void
}

export const useContactListsStore = create<ContactListsState>((set, get) => ({
    contactLists: [],
    isLoading: false,
    error: null,
    isContinuousLoading: false,

    fetchContactLists: async (force = false) => {
        console.log('execed')
        if (!force && (get().isLoading || get().contactLists.length > 0)) return;

        set({ isLoading: true, error: null });

        try {
            const response = await fetch('/api/contact-lists');

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.contactLists && Array.isArray(data.contactLists)) {
                set({
                    contactLists: data.contactLists,
                    isLoading: false,
                    error: null
                });
            } else {
                throw new Error('Invalid response format');
            }
        } catch (error: unknown) {
            console.error('Error fetching contact lists:', error);
            set({
                contactLists: [],
                isLoading: false,
                error: error instanceof Error ? error.message : 'Unknown error'
            });
        }
    },

    continuousFetch: (listName: string, expectedCount: number) => {
        set({ isContinuousLoading: true });

        const startTime = Date.now();
        const maxDuration = 80000; // 80 seconds

        const fetchInterval = setInterval(async () => {
            try {
                const response = await fetch('/api/contact-lists');
                if (response.ok) {
                    const data = await response.json();
                    if (data.contactLists && Array.isArray(data.contactLists)) {
                        // Find the uploaded list and check count
                        const uploadedList = data.contactLists.find((list: ContactList) =>
                            list.name === listName
                        );

                        if (uploadedList && uploadedList.contact_count === expectedCount) {
                            // Count matches, update store and stop fetching
                            set({ contactLists: data.contactLists, isContinuousLoading: false });
                            clearInterval(fetchInterval);
                            return;
                        }

                        // Only update if count changed
                        const currentList = get().contactLists.find(list => list.name === listName);
                        if (!currentList || currentList.contact_count !== uploadedList?.contact_count) {
                            set({ contactLists: data.contactLists });
                        }
                    }
                }

                if (Date.now() - startTime >= maxDuration) {
                    clearInterval(fetchInterval);
                    set({ isContinuousLoading: false });
                }
            } catch (error) {
                console.error('Continuous fetch error:', error);
                clearInterval(fetchInterval);
                set({ isContinuousLoading: false });
            }
        }, 4000);
    }
}));