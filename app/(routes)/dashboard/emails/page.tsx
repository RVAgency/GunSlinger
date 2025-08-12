'use client'

import React, { useState } from 'react'
import { toast } from 'sonner'
import { Send, FileText, Loader2, Contact } from 'lucide-react'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Input } from "@/components/ui/input"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"

import ExcelInput from '@/components/shared/blasting/ExcelInput'

import { useEmailCampaignStore } from '@/stores/emailCampaign'
import { useSenderEmailsStore } from '@/stores/senderEmails'
import EmailContentTabs from '@/components/shared/blasting/EmailContentTabsc'
import { useContactListsStore } from '@/stores/contactLists'

export default function Emails() {
  const { formData, updateFormData, selectedFile, resetForm } = useEmailCampaignStore()
  const { senderEmails, isLoading: isLoadingSenders, fetchSenderEmails } = useSenderEmailsStore()
  const { fetchContactLists, contactLists, isContinuousLoading } = useContactListsStore()

  const [isSubmitting, setIsSubmitting] = useState(false)
  const [isDialogOpen, setIsDialogOpen] = useState(false)

  // eslint-disable-next-line react-hooks/exhaustive-deps
  React.useEffect(() => { fetchSenderEmails(); fetchContactLists() }, [])

  const storeCampaignData = async () => {
    try {
      console.log(formData.contactListName)
      await fetch('/api/campaigns/store', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          campaignName: formData.campaignName,
          subject: formData.subject,
          senderId: formData.senderId,
          body: formData.body,
          templateId: formData.templateName,
          contactListId: formData.contactListName,
          excelFileName: selectedFile?.file.name
        })
      })
    } catch (error) {
      console.error('Failed to store campaign data:', error)
    }
  }

  const handleSend = async () => {
    if (!validateForm(formData)) {
      return toast.error('Please fill all required fields');
    }
    setIsSubmitting(true)
    try {
      const campaignResponse = await fetch('/api/campaigns', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: formData.campaignName || 'Email Campaign',
          subject: formData.subject,
          senderEmail: formData.senderId,
          listIds: [formData.contactListId],
          templateId: formData?.templateId,
          body: formData?.body
        })
      })
      if (!campaignResponse.ok) {
        const error = await campaignResponse.json()
        throw new Error(error.error)
      }

      // Store campaign data in Airtable
      await storeCampaignData()

      resetForm()
      toast.success(`Campaign sent successfully to recipients!`)
      setIsDialogOpen(false)
    } catch (error) {
      toast.error(`Failed to send campaign: ${error instanceof Error ? error.message : 'Unknown error occurred'}`)
    } finally {
      setIsSubmitting(false)
    }
  }

  const getSelectedSenderEmail = () => {
    return senderEmails?.find(sender => sender.email === formData.senderId)?.email || formData.senderId
  }

  const getSelectedContactListName = () => {
    return contactLists?.find(contact => contact.id === formData.contactListId)?.name || '-'
  }
  const getSelectedContactsNoOfContacts = () => {
    return contactLists?.find(contact => contact.id === formData.contactListId)?.contact_count || '-'
  }
  const getSelectedContactsSourceFile = () => {
    return contactLists?.find(contact => contact.id === formData.contactListId)?.source_file_name || '-'
  }

  // React.useEffect(() => { console.log(formData) }, [formData])

  return (
    <div className="max-w-4xl relative pb-3">
      <div className="text-2xl pt-6 pb-2 font-semibold md:sticky top-0 bg-white dark:bg-black mb-8">Email Campaign</div>

      <div className="flex-1 mt-4 flex flex-col w-full  gap-6 mb-6">
        {/* campaign name from  */}
        <div className="space-y-0.5 flex-1  ">
          <div className="flex items-center gap-2">
            <FileText className="h-4 w-4 text-orange-500" />
            <h2 className="font-medium  text-base">Campaign Name</h2>
          </div>
          <Input
            type="text"
            className=''
            value={formData.campaignName}
            onChange={(e) => updateFormData('campaignName', e.target.value)}
            placeholder="Enter email subject..."
          />
        </div>

        <section className="flex-1 gap-3  flex md:flex-row flex-col">
          {/* recipient/ contact group  */}
          <div className="space-y-0.5 flex-1 ">
            <div className="flex items-center justify-between">
              <p className='flex items-center  gap-2'><Contact className="h-4 w-4 text-blue-500" />
                <span className="font-medium  text-base">Recipient group</span>
              </p>
              <ExcelInput />

            </div>
            <Select value={formData.contactListId} onValueChange={(value) => {
              const selectedContact = contactLists?.find(contact => contact.id === value);
              updateFormData('contactListId', value);
              if (selectedContact) {
                updateFormData('contactListName', selectedContact.name);
              }
            }} disabled={isLoadingSenders}>
              <SelectTrigger className='w-full'>
                <SelectValue placeholder={"Select recipient group..."} />
              </SelectTrigger>
              <SelectContent>
                {contactLists?.map(contact => (
                  <SelectItem key={contact.id} value={contact.id}>{contact.name}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          {/* send from  */}
          <div className="space-y-0.5 flex-1 ">
            <div className="flex items-center gap-2">
              <Send className="h-4 w-4 text-red-500" />
              <h2 className="font-medium  text-base">Send From</h2>
            </div>
            <Select value={formData.senderId} onValueChange={(value) => updateFormData('senderId', value)} disabled={isLoadingSenders}>
              <SelectTrigger className='w-full'>
                <SelectValue placeholder={isLoadingSenders ? "Loading..." : "Select sender email..."}>
                  {formData.senderId}
                </SelectValue>
              </SelectTrigger>
              <SelectContent>
                {senderEmails?.map(sender => (
                  <SelectItem key={sender.id} value={sender.email}>
                    <div className='flex flex-col'>
                      <span className='font-semibold '>{sender.name}</span>
                      <span className='text-xs'>{sender.email}</span>
                    </div>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </section>

      </div>

      <EmailContentTabs />

      <div className="flex-1 flex justify-end mt-6">
        <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
          <DialogTrigger asChild>
            <button
              disabled={!validateForm(formData)}
              className="flex items-center gap-2 px-6 py-3 bg-pry   text-white  hover:bg-pry/80 disabled:bg-neutral-400 disabled:cursor-not-allowed transition-colors"
            >
              <Send className="h-4 w-4" />
              Send Campaign
            </button>
          </DialogTrigger>
          <DialogContent className="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle>Confirm Campaign Send</DialogTitle>
              <DialogDescription>
                Please review the campaign details before sending.
              </DialogDescription>
            </DialogHeader>
            <div className="grid gap-2 py-4">
              <div className="flex items-start md:items-center md:flex-row flex-col gap-1 md:gap-4">
                <label className="text-right text-sm font-medium">
                  Sender Email:
                </label>
                <div className="col-span-3 text-sm text-neutral-600 dark:text-neutral-300">
                  <span className='bg-pry dark:bg-neutral-800 py-px px-2  text-white'>{getSelectedSenderEmail()}</span>
                </div>
              </div>
              <div className="flex items-start md:items-center md:flex-row flex-col gap-1 md:gap-4">
                <label className="text-right text-sm font-medium">
                  Recipient Group:
                </label>
                <div className="col-span-3 text-sm flex items-center gap-1 text-neutral-600 dark:text-neutral-300">
                  <span className='bg-pry dark:bg-neutral-800 py-px px-2  text-white'>{getSelectedContactListName()} </span>
                </div>

              </div>


              <div className="flex items-start md:items-center md:flex-row flex-col gap-1 md:gap-4">
                <label className="text-right text-sm font-medium">
                  Source Filename:
                </label>
                <div className="col-span-3 text-sm flex items-center gap-1 text-neutral-600 dark:text-neutral-300">
                  <span className='bg-pry dark:bg-neutral-800 py-px px-2  text-white'>{getSelectedContactsSourceFile()} </span>
                </div>
              </div>



              <div className="flex items-start md:items-center md:flex-row flex-col gap-1 md:gap-4">
                <label className="text-right text-sm font-medium">
                  Recipient List Count:
                </label>
                <div className="col-span-3 text-sm flex items-center gap-1 text-neutral-600 dark:text-neutral-300">
                  <span className='bg-pry dark:bg-neutral-800 py-px px-2  text-white'>

                    {
                      isContinuousLoading ? (
                        <Loader2 className='h-4 w-4 text-green-500 animate-spin' />
                      ) : (
                        null
                      )
                    }{getSelectedContactsNoOfContacts()} </span>
                </div>
              </div>
            </div>
            <DialogFooter className='flex gap-2'>
              <button
                type="button"
                onClick={() => setIsDialogOpen(false)}
                className="px-4 py-3 text-sm font-medium text-neutral-700  border border-neutral-300  hover:bg-neutral-200  dark:bg-neutral-900 dark:text-neutral-300 dark:border-neutral-600 dark:hover:bg-neutral-800 flex-1"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={handleSend}
                disabled={isSubmitting}
                className="flex items-center justify-center  gap-2 px-4 py-3 text-sm font-medium text-white dark:bg-pry bg-pry border border-transparent  hover:bg-pry/80  disabled:cursor-not-allowed! flex-1"
              >

                <div className='flex items-center gap-2'>
                  {isSubmitting ? (
                    <>
                      <Loader2 className="h-4 w-4 animate-spin" />
                      Sending...
                    </>
                  ) : (<>
                    <Send className="h-4 w-4" />
                    Send Campaign
                  </>
                  )}

                </div>

              </button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </div>
  )
}

function validateForm(formData: any) {
  const required = ['senderId', 'contactListId', 'campaignName'];
  if (!formData?.templateId) required.push('subject', 'body');

  return required.every(field => {
    const value = formData?.[field];
    const isValid = typeof value === 'string' ? value.trim() !== '' : Boolean(value);
    return isValid;
  });
}