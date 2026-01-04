import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { 
    BookOpen, 
    Folder, 
    LayoutGrid, 
    Users, 
    Store, 
    CreditCard, 
    BarChart3, 
    Settings, 
    ShoppingCart, 
    Package, 
    FileText, 
    User, 
    Wallet, 
    ArrowRightLeft, 
    ShoppingBag 
} from 'lucide-react';
import AppLogo from './app-logo';

// Navigation items for Admin role
const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: LayoutGrid,
    },
    // {
    //     title: 'Users Management',
    //     href: '/admin/users',
    //     icon: Users,
    // },
    // {
    //     title: 'Merchants',
    //     href: '/admin/merchants',
    //     icon: Store,
    // },
    {
        title: 'Transactions',
        href: '/admin/transactions',
        icon: CreditCard,
    },
    // {
    //     title: 'Reports',
    //     href: '/admin/reports',
    //     icon: BarChart3,
    // },
    // {
    //     title: 'Settings',
    //     href: '/admin/settings',
    //     icon: Settings,
    // },
];

// Navigation items for Kasir (Cashier) role
const kasirNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/kasir/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Transactions',
        href: '/kasir/transactions',
        icon: ShoppingCart,
    },
    {
        title: 'Products',
        href: '/kasir/products',
        icon: Package,
    },
    // {
    //     title: 'Sales Reports',
    //     href: '/kasir/reports',
    //     icon: FileText,
    // },
    // {
    //     title: 'Profile',
    //     href: '/kasir/profile',
    //     icon: User,
    // },
];

// Navigation items for Mahasiswa (Student) role
const mahasiswaNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/mahasiswa/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Top Up Wallet',
        href: '/mahasiswa/wallet/top-up',
        icon: Wallet,
    },
    {
        title: 'Transactions',
        href: '/mahasiswa/transactions',
        icon: ArrowRightLeft,
    },
    {
        title: 'Merchants',
        href: '/mahasiswa/merchants',
        icon: ShoppingBag,
    },
    // {
    //     title: 'Profile',
    //     href: '/mahasiswa/profile',
    //     icon: User,
    // },
];

// Function to get navigation items based on user role
const getNavItemsByRole = (userRole: string): NavItem[] => {
    switch (userRole) {
        case 'admin':
            return adminNavItems;
        case 'kasir':
            return kasirNavItems;
        case 'mahasiswa':
            return mahasiswaNavItems;
        default:
            return mahasiswaNavItems; // Default to mahasiswa if role is not found
    }
};

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Repository',
    //     href: 'https://github.com/laravel/react-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#react',
    //     icon: BookOpen,
    // },
];

export function AppSidebar() {
    const { auth } = usePage().props as any;
    const userRole = auth?.user?.roles?.[0]?.name || 'mahasiswa';
    const navItems = getNavItemsByRole(userRole);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
