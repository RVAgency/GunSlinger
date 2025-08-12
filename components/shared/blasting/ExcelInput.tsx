import { Loader2, Upload } from 'lucide-react'
import React, { useCallback, useRef } from 'react'
import { extractEmailsFromExcel, parseExcelToJson } from '@/lib/utils/excelParser'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { useEmailCampaignStore } from '@/stores/emailCampaign'
import { useContactListsStore } from '@/stores/contactLists'
import { Input } from '@/components/ui/input'
import { toast } from 'sonner'


export default function ExcelInput() {
    const [contactName, setContactName] = React.useState<string>('')
    const [selectedFileData, setSelectedFileData] = React.useState<File | null>(null)
    const { setSelectedFile, updateFormData } = useEmailCampaignStore()
    const { fetchContactLists, continuousFetch, isContinuousLoading } = useContactListsStore()

    const [isUploading, setIsUploading] = React.useState(false);
    const [uploadProgress, setUploadProgress] = React.useState(0);
    const [currentStatus, setCurrentStatus] = React.useState('');
    const [dialogOpen, setDialogOpen] = React.useState(false);

    const handleFileSelect = useCallback((event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            setSelectedFileData(file);
        }
    }, []);

    const completeUpload = useCallback(async (file: File, contactListId: string, contactName: string, contactCount: number) => {
        const statuses = ['Processing file...', 'Getting contacts ready...', 'Uploading contacts...', 'Finalizing things...'];
        setSelectedFile({ file, contactListId });

        for (let i = 0; i < statuses.length; i++) {
            setCurrentStatus(statuses[i]);
            setUploadProgress((i + 1) * 25);
            await new Promise(resolve => setTimeout(resolve, 8500));
        }
        await fetchContactLists(true);

        updateFormData('contactListId', contactListId);
        updateFormData('contactListName', contactName);

        continuousFetch(contactName, contactCount);

        setDialogOpen(false);
        setSelectedFileData(null);
        setContactName('');
        setIsUploading(false);
        setCurrentStatus('');
        setUploadProgress(0);
    }, [setSelectedFile, updateFormData, continuousFetch]);

    const handleUpload = useCallback(async () => {
        if (!selectedFileData || !contactName.trim()) return;

        setIsUploading(true);
        setUploadProgress(0);
        setCurrentStatus('Uploading file...');

        try {
            const parsedData = await parseExcelToJson(selectedFileData);
            const extractedData = extractEmailsFromExcel(parsedData.data);
            console.log(selectedFileData, parsedData, extractedData)
            const contacts = extractedData.map(row => ({
                email: row.email,
                firstName: row.firstName || '',
                lastName: row.lastName || '',
                phone: row.phone || '',
                code: row?.code || '',
                filename: selectedFileData?.name
            }));
            console.log(selectedFileData.name)
            const listResponse = await fetch('/api/contact-lists', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: contactName,
                    contacts,
                    sourceFileName: selectedFileData.name
                })
            });

            if (!listResponse.ok) throw new Error('Failed to create contact list');
            const { contactList } = await listResponse.json();

            completeUpload(selectedFileData, contactList?.id, contactName, contacts.length);


        } catch (error) {
            setIsUploading(false);
            setCurrentStatus('');
            setUploadProgress(0);
            toast.warning(`Error parsing Excel file. Please check the format.${error}`);
        }
    }, [selectedFileData, contactName, completeUpload]);

    const handleCancel = useCallback(() => {
        setDialogOpen(false);
        setSelectedFileData(null);
        setContactName('');
        setCurrentStatus('');
        setUploadProgress(0);
    }, []);

    return (
        <>
            <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                <DialogTrigger asChild >
                    <div className='flex items-center gap-2'>
                        {
                            isContinuousLoading ? (
                                <Loader2 className='h-4 w-4 text-green-500 animate-spin' />
                            ) : (
                                null
                            )
                        }
                        <span className="text-pry text-sm  cursor-pointer hover:underline mr-4"> Upload Excel</span>
                    </div>
                </DialogTrigger>

                <DialogContent>
                    <DialogHeader>
                        <div className="flex items-center gap-2 ">
                            <Upload className="h-4 w-4 text-blue-500" />
                            <DialogTitle>Upload Excel File</DialogTitle>
                        </div>
                        <DialogDescription className='text-xs'>
                            Enter the name and upload excel file to create a new recipient group.
                        </DialogDescription>
                    </DialogHeader>

                    <Input
                        type="text"
                        className=''
                        value={contactName}
                        onChange={(e) => setContactName(e.target.value)}
                        placeholder="Enter group name..."
                    />



                    <div className="border-2 h-32 border-dashed  border-gray-300 dark:border-gray-600 p-4 text-center hover:border-pry transition-colors flex flex-col items-center justify-center cursor-pointer"
                        onClick={() => !isUploading && document.getElementById('file-upload')?.click()}>
                        <input
                            type="file"
                            accept=".xlsx,.xls"
                            onChange={handleFileSelect}
                            className="hidden"
                            id="file-upload"
                            disabled={isUploading}
                        />

                        {isUploading ? (
                            <div className="w-full flex flex-col items-center justify-center">
                                <div className='flex items-center gap-3 mb-3'>
                                    <Loader2 className='animate-spin h-6 w-6 text-blue-500' />
                                </div>
                                <div className="w-full bg-gray-200 dark:bg-gray-700  h-2 mb-3 max-w-sm">
                                    <div
                                        className="bg-blue-500 h-2  transition-all duration-500 ease-out"
                                        style={{ width: `${uploadProgress}%` }}
                                    ></div>
                                </div>
                                <div className="text-center min-h-[20px]">
                                    <p className="text-sm text-blue-500 animate-pulse transition-all duration-300 transform">
                                        {currentStatus || 'Loading...'}
                                    </p>
                                </div>
                            </div>
                        ) : selectedFileData ? (
                            <div className="flex flex-col h-full items-center justify-center">
                                <Upload className="h-8 w-8 mx-auto mb-3 text-green-500" />
                                <p className="text-sm font-medium text-green-600">{selectedFileData.name}</p>
                                <p className="text-gray-500 text-xs">File selected</p>
                            </div>
                        ) : (
                            <div className="flex flex-col h-full items-center justify-center">
                                <Upload className="h-8 w-8 mx-auto mb-3 text-gray-400" />
                                <p className="text-sm font-medium">Click to select Excel file</p>
                                <p className="text-gray-500 text-xs">Supports .xlsx, .xls files</p>
                            </div>
                        )}
                    </div>

                    <div className='flex gap-2'>
                        <button
                            onClick={handleCancel}
                            disabled={isUploading}
                            className="w-full flex-1 py-2 border dark:border-neutral-600 dark:hover:bg-neutral-800 "
                        >
                            Cancel
                        </button>
                        <button
                            onClick={handleUpload}
                            disabled={!selectedFileData || !contactName.trim() || isUploading}
                            className="w-full py-2 flex-1  bg-pry dark:bg-pry text-white min-w-[120px] flex items-center justify-center disabled:cursor-not-allowed!"
                        >
                            {isUploading ? (
                                <div className='flex items-center gap-2'>
                                    <Loader2 className="animate-spin h-4 w-4 mr-2" />
                                    Uploading...
                                </div>
                            ) : (
                                'Continue'
                            )}
                        </button>
                    </div>

                </DialogContent>
            </Dialog>

            {/* {selectedFile && (
                <div className="text-sm text-gray-600">
                    <p>{selectedFile.file.name} Uploaded</p>
                </div>
            )} */}
        </>
    )
}