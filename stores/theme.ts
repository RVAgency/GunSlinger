import { create } from 'zustand'
import { persist } from 'zustand/middleware'

type Theme = 'light' | 'dark' | 'system'

interface ThemeStore {
  theme: Theme
  resolvedTheme: 'light' | 'dark'
  setTheme: (theme: Theme) => void
  toggleTheme: () => void
  initTheme: () => void
}

export const useThemeStore = create<ThemeStore>()(
  persist(
    (set, get) => ({
      theme: 'light',
      resolvedTheme: 'light',
      setTheme: (theme) => {
        set({ theme })
        get().initTheme()
      },
      toggleTheme: () => {
        const { theme } = get()
        const themes = ['light', 'dark', 'system'] as const
        const currentIndex = themes.indexOf(theme)
        const nextTheme = themes[(currentIndex + 1) % themes.length]
        get().setTheme(nextTheme)
      },
      initTheme: () => {
        const { theme } = get()
        const applyTheme = () => {
          if (theme === 'system') {
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
            set({ resolvedTheme: systemTheme })
            document.documentElement.classList.toggle('dark', systemTheme === 'dark')
          } else {
            set({ resolvedTheme: theme })
            document.documentElement.classList.toggle('dark', theme === 'dark')
          }
        }
        applyTheme()

        if (theme === 'system') {
          const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
          mediaQuery.addEventListener('change', applyTheme)
        }
      }
    }),
    {
      name: 'theme-storage',
    }
  )
)