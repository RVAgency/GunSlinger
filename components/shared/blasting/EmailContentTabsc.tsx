
'use client'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { useEmailCampaignStore } from '@/stores/emailCampaign'
import { useTemplatesStore } from '@/stores/templates'
import { Brush, ChartNoAxesGantt, FileText, Mail, Send } from 'lucide-react'
import React from 'react'

export default function EmailContentTabs() {
    const { formData, updateFormData } = useEmailCampaignStore()
    const { fetchTemplates, templates } = useTemplatesStore()
    const [tab, setTab] = React.useState<'template' | 'body'>('template')
    // eslint-disable-next-line react-hooks/exhaustive-deps
    React.useEffect(() => { fetchTemplates() }, [])

    const toggleTab = () => {
        if (tab === 'template') {
            updateFormData('templateId', '')
        } else {
            updateFormData('body', '')
            updateFormData('subject', '')
        }
        setTab(tab === 'template' ? 'body' : 'template')
    }

    return (
        <>
            <section className="flex-1  mt-8 ">
                <div className="flex items-center justify-between py-2 px-2">
                    <div className='flex items-center gap-2'>
                        <ChartNoAxesGantt className="h-6 w-6 text-orange-500" />
                        <h2 className="font-medium  text-2xl">Mail Content</h2>
                    </div>


                </div>
                <div className='flex items-end justify-end'>
                    <button
                        onClick={toggleTab}
                        className={`text-sm bg-pry py-2 px-4 w-fit    cursor-pointer font-medium transition-colors hover:text-white text-white
                                }`}
                    >
                        {tab === 'template' ? 'Use PlaneText' : 'Use Template'}
                    </button>
                </div>

                {
                    tab === 'template' ? (
                        <div className='space-y-4'>
                            <div className="space-y-0.5">
                                <div className="flex items-center gap-2">
                                    <FileText className="h-4 w-4 text-orange-500" />
                                    <h2 className="font-medium  text-base">Design Subject</h2>
                                </div>
                                <Input
                                    type="text"
                                    value={formData.subject}
                                    onChange={(e) => updateFormData('subject', e.target.value)}
                                    placeholder="Enter email subject..."
                                />
                            </div>
                            <div className="space-y-0.5 flex-1 ">
                                <div className="flex items-center gap-2">
                                    <Brush className="h-4 w-4 text-violet-500" />
                                    <span className="font-medium  text-base">Design Template</span>

                                </div>
                                <Select value={formData.templateId} onValueChange={(value) => {
                                    updateFormData('templateId', value)
                                    const selectedTemplate = templates.find(template => template.id === value)
                                    updateFormData('templateName', selectedTemplate?.name || '')
                                }} disabled={false}>
                                    <SelectTrigger className='w-full'>
                                        <SelectValue placeholder={false ? "Loading..." : "Select design template..."} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {templates.map(template => (
                                            <SelectItem key={template.id} value={template.id}>{template.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    ) : (
                        <div className='space-y-4'>
                            <div className="space-y-0.5">
                                <div className="flex items-center gap-2">
                                    <FileText className="h-4 w-4 text-orange-500" />
                                    <h2 className="font-medium  text-base">Email Subject</h2>
                                </div>
                                <Input
                                    type="text"
                                    value={formData.subject}
                                    onChange={(e) => updateFormData('subject', e.target.value)}
                                    placeholder="Enter email subject..."
                                />
                            </div>

                            <div className="space-y-0.5">
                                <div className="flex items-center gap-2">
                                    <Mail className="h-4 w-4 text-green-500" />
                                    <h2 className="font-medium  text-base">Email Body</h2>
                                </div>
                                <Textarea
                                    className="min-h-[160px]"
                                    value={formData.body}
                                    onChange={(e) => updateFormData('body', e.target.value)}
                                    placeholder="Enter email body..."
                                    rows={10}
                                />
                            </div>
                        </div>
                    )

                }
            </section>

        </>
    )
}
