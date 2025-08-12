'use client'

import { useEffect } from 'react'
import { useThemeStore } from '@/stores/theme'
import { Moon, Sun, Monitor } from 'lucide-react'

export default function ThemeToggle() {
    const { theme, setTheme, initTheme } = useThemeStore()

    useEffect(() => {
        initTheme()
    }, [initTheme])

    return (
        <div className="flex items-center gap-2">
            {/* Dark/Light Switch */}
            <div className="flex items-center gap-2 bg-neutral-100 dark:bg-neutral-900  p-1">
                <button
                    onClick={() => setTheme('light')}
                    className={`p-1.5  cursor-pointer transition-colors ${theme === 'light'
                            ? 'bg-white dark:bg-neutral-700 shadow-sm'
                            : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'
                        }`}
                    title="Light mode"
                >
                    <Sun className="h-4 w-4" />
                </button>
                <button
                    onClick={() => setTheme('dark')}
                    className={`p-1.5  cursor-pointer transition-colors ${theme === 'dark'
                            ? 'bg-white dark:bg-neutral-700 shadow-sm'
                            : 'hover:bg-neutral-200 dark:hover:bg-neutral-700'
                        }`}
                    title="Dark mode"
                >
                    <Moon className="h-4 w-4" />
                </button>
            </div>

            {/* System Button */}
            <button
                onClick={() => setTheme('system')}
                className={`p-2  text-white dark:hover:text-white cursor-pointer hover:text-black transition-colors ${theme === 'system'
                        ? 'bg-blue-100 dark:bg-neutral-700 text-black dark:text-white'
                        : 'hover:bg-neutral-100 dark:hover:bg-neutral-800'
                    }`}
                title="System theme"
            >
                <Monitor className="h-4 w-4" />
            </button>
        </div>
    )
}