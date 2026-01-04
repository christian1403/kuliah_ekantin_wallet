import { Head } from '@inertiajs/react';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem, type NavItem } from '@/types';
import { type ReactNode } from 'react';

interface AdminLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    title?: string;
    navItems?: NavItem[];
}

export default function AdminLayout({ 
    children, 
    breadcrumbs = [],
    navItems = [],
    title = 'Admin Portal',
    ...props 
}: AdminLayoutProps) {
    const adminBreadcrumbs: BreadcrumbItem[] = [
        { title: 'Admin', href: '/admin/dashboard' },
        ...breadcrumbs
    ];

    return (
        <>
            {title && <Head title={title} />}
            <AppLayoutTemplate breadcrumbs={adminBreadcrumbs} {...props}>
                {children}
            </AppLayoutTemplate>
        </>
    );
}