import { MailCheck, Menu } from 'lucide-react'
import ThemeToggle from './ThemeToggle'
import SignOutButton from '../auth/SignOutButton'
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet"
import Image from 'next/image'

const SidebarContent = () => (
  <>
    <div className="h-24 bg-amber-100 dark:bg-neutral-700 flex items-center relative justify-center ">
      <Image src={'/gunslinger.jpg'}  alt='Gunslinger' fill className=''/>
    </div>

    <nav className="flex flex-col flex-1 justify-between">
      <ul className='space-y-2'>
        <li className='text-white flex items-center gap-2 font-medium px-3 py-2 hover:bg-white/10 transition-colors duration-300 cursor-pointer '>
          <MailCheck className='h-5 w-5' />
          Blast Emails
        </li>
        <SignOutButton />
      </ul>
      <ThemeToggle />
    </nav>
  </>
)

export default function Sidebar() {
  return (
    <>
      {/* Desktop Sidebar */}
      <aside className="hidden md:flex w-64 bg-pry dark:bg-neutral-800  p-4 flex-col gap-2">
        <SidebarContent />
      </aside>

      {/* Mobile Sheet */}
      <div className='md:hidden flex gap-3 sticky top-0 items-center'>
      <Sheet >
        <SheetTrigger className=" w-fit  top-4 left-4 z-50 p-2 bg-pry dark:bg-neutral-800 ">
          <Menu className="h-6 w-6 text-white" />
        </SheetTrigger>
        <SheetContent side="left" className='h-full w-80 p-2 bg-transparent shadow-none' >
          <SheetTitle className='sr-only'>Menu</SheetTitle>
          <div className=' bg-pry  pt-12 pb-4 h-full dark:bg-neutral-800 px-4 flex flex-col gap-2'>
          
          <SidebarContent />
          </div>
          
        </SheetContent>
      </Sheet>
      <h4 className='font-semibold text-2xl'>Gunslinger</h4>
      </div>
      
      
    </>
  )
}