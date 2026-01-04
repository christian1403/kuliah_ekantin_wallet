import { Head } from '@inertiajs/react';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem, type NavItem } from '@/types';
import { type ReactNode } from 'react';
import { Store } from 'lucide-react';

interface KasirLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    title?: string;
    navItems?: NavItem[];
}

export default function KasirLayout({ 
    children, 
    breadcrumbs = [],
    navItems = [],
    title = 'Kasir Portal',
    ...props 
}: KasirLayoutProps) {
    const kasirBreadcrumbs: BreadcrumbItem[] = [
        { title: 'Kasir', href: '/kasir/dashboard' },
        ...breadcrumbs
    ];

    return (
        <>
            {title && <Head title={title} />}
            <AppLayoutTemplate breadcrumbs={kasirBreadcrumbs} {...props}>
                {children}
            </AppLayoutTemplate>
        </>
    );
}