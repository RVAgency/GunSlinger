import { create } from 'zustand'

interface Template {
    id: string
    name: string
    subject: string
}

interface TemplatesState {
    templates: Template[]
    isLoading: boolean
    error: string | null
    fetchTemplates: () => Promise<void>
}

export const useTemplatesStore = create<TemplatesState>((set, get) => ({
    templates: [],
    isLoading: false,
    error: null,

    fetchTemplates: async () => {
        if (get().isLoading || get().templates.length > 0) return;

        set({ isLoading: true, error: null });
        
        try {
            const response = await fetch('/api/templates');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.templates && Array.isArray(data.templates)) {
                set({ 
                    templates: data.templates,
                    isLoading: false,
                    error: null 
                });
            } else {
                throw new Error('Invalid response format');
            }
        } catch (error: unknown) {
            console.error('Error fetching templates:', error);
            set({ 
                templates: [],
                isLoading: false,
                error: error instanceof Error ? error.message : 'Unknown error'
            });
        }
    }
}));