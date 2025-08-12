import Sidebar from "@/components/core/Sidebar"

export default function DashboardLayout({
    children,
}: {
    children: React.ReactNode
}) {
    return (
        <div className="flex flex-col md:flex-row h-screen gap-2 md:gap-4 p-2 md:p-4">
            <Sidebar />
            <main className="flex-1 px-4 border border-neutral-300 overflow-x-hidden overflow-y-auto ">
                {children}
            </main>
        </div>
    )
}