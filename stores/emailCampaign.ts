import { create } from 'zustand'

interface SelectedFileData {
    file: File
    contactListId?: string
}

export interface EmailCampaignState {
    selectedFile: SelectedFileData | null
    formData: {
        campaignName: string //-
        subject: string    //-
        senderId: string    // -
        body: string     //-
        templateId: string //-
        templateName: string //-
        contactListId?: string
        contactListName?: string
    }
    setSelectedFile: (fileData: SelectedFileData | null) => void
    updateFormData: (field: string, value: string) => void
    resetForm: () => void
}

const initialFormData = {
    campaignName: '',
    senderId: '',
    templateId: '',
    templateName: '',
    subject: '',
    body: '',
    contactListId: '',
    contactListName: ''
}

export const useEmailCampaignStore = create<EmailCampaignState>((set) => ({
    selectedFile: null,
    formData: initialFormData,

    setSelectedFile: (fileData) => set({ selectedFile: fileData }),

    updateFormData: (field, value) =>
        set((state) => ({
            formData: { ...state.formData, [field]: value }
        })),

    resetForm: () =>
        set({
            selectedFile: null,
            formData: initialFormData
        })
}))