import { Head } from '@inertiajs/react';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem, type NavItem } from '@/types';
import { type ReactNode } from 'react';
import {LayoutDashboard} from 'lucide-react';

interface MahasiswaLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    title?: string;
    navItems?: NavItem[];
}

export default function MahasiswaLayout({ 
    children, 
    breadcrumbs = [],
    navItems = [],
    title = 'Student Portal',
    ...props 
}: MahasiswaLayoutProps) {
    const mahasiswaBreadcrumbs: BreadcrumbItem[] = [
        { title: 'Mahasiswa', href: '/mahasiswa/dashboard' },
        ...breadcrumbs
    ];

    return (
        <>
            {title && <Head title={title} />}
            <AppLayoutTemplate breadcrumbs={mahasiswaBreadcrumbs} {...props}>
                {children}
            </AppLayoutTemplate>
        </>
    );
}