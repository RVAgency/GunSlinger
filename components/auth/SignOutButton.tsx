'use client'

import { LogOut } from 'lucide-react'
import { toast } from 'sonner'

export default function SignOutButton() {
  const handleSignOut = async () => {
    try {
      const response = await fetch('/api/auth/signout', {
        method: 'POST'
      })

      if (response.ok) {
        toast.success('Signed out successfully')
        window.location.href = '/signin'
      } else {
        throw new Error('Failed to sign out')
      }
    } catch (error) {
      toast.error('Failed to sign out')
    }
  }

  return (
    <button
      onClick={handleSignOut}
      className="flex items-center gap-2 px-3 py-2  text-white hover:text-white hover:bg-white/10 dark:hover:bg-red-900/20  transition-colors w-full"
    >
      <LogOut className="h-5 w-5" />
      Sign Out
    </button>
  )
}