import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import KasirLayout from '@/layouts/kasir/kasir-layout';
import { 
  Store, 
  TrendingUp, 
  TrendingDown, 
  ArrowUpRight, 
  ArrowDownRight,
  CreditCard,
  Calendar,
  BarChart3,
  Eye,
  Clock,
  DollarSign,
  ShoppingCart,
  CheckCircle,
  AlertCircle,
  Users,
  Package
} from 'lucide-react';
import { formatCurrency } from '@/lib/utils';

interface WeeklyData {
  date: string;
  revenue: number;
  transactions: number;
}

interface RecentTransaction {
  id: string;
  invoice: string;
  amount: number;
  status_transaksi: 'pending' | 'completed' | 'failed';
  created_at: string;
  wallet: {
    mahasiswa: {
      nama: string;
      npm: string;
      user: {
        name: string;
        email: string;
      };
    };
  };
  detail_produk_transactions: {
    quantity: number;
    produk: {
      nama: string;
      harga: number;
    };
  }[];
}

interface Stats {
  total_transactions_today: number;
  total_revenue_today: number;
  pending_orders: number;
  completed_orders: number;
}

interface Kasir {
  id: string;
  nama: string;
  no_hp: string;
  user: {
    name: string;
    email: string;
  };
  merchant: {
    merchant_id: string;
    nama: string;
    alamat?: string;
    no_telepon?: string;
    email?: string;
  };
}

interface Props extends PageProps {
  kasir: Kasir;
  stats: Stats;
  recentTransactions: RecentTransaction[];
  weeklyData: WeeklyData[];
}

export default function KasirDashboard({ 
  kasir,
  stats,
  recentTransactions,
  weeklyData
}: Props) {

  const getInitials = (name: string) => {
    return name
      ?.split(' ')
      .map(n => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  const getTransactionIcon = (transaction: RecentTransaction) => {
    if(transaction.status_transaksi === 'pending') {
      return <Clock className="h-4 w-4 text-yellow-500" />;
    }
    if (transaction.status_transaksi === 'completed') {
      return <CheckCircle className="h-4 w-4 text-green-500" />;
    }
    return <AlertCircle className="h-4 w-4 text-red-500" />;
  };

  const getStatusBadge = (status: string) => {
    const variants: { [key: string]: "default" | "secondary" | "destructive" | "outline" } = {
      completed: "default",
      pending: "secondary", 
      failed: "destructive"
    };
    
    return <Badge variant={variants[status] || "outline"}>{status}</Badge>;
  };

  // Calculate weekly revenue trend
  const currentWeekRevenue = weeklyData.reduce((sum, day) => sum + day.revenue, 0);
  const lastWeekRevenue = currentWeekRevenue > 0 ? currentWeekRevenue * 0.85 : 0; // Mock calculation
  const revenueTrend = currentWeekRevenue - lastWeekRevenue;
  const revenueTrendPercentage = lastWeekRevenue > 0 ? ((revenueTrend / lastWeekRevenue) * 100) : 0;

  const maxWeeklyRevenue = Math.max(...weeklyData.map(d => d.revenue));

  return (
    <KasirLayout 
      title="Dashboard - Kasir Portal"
      breadcrumbs={[{ title: 'Dashboard', href: '/kasir/dashboard' }]}
    >
      <div className="flex-1 space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Kasir Dashboard</h1>
            <p className="text-muted-foreground">
              Welcome back, {kasir.nama}
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Link href="/kasir/transactions">
              <Button size="sm">
                <Eye className="h-4 w-4 mr-2" />
                View All Transactions
              </Button>
            </Link>
          </div>
        </div>

        {/* Kasir & Merchant Info Card */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-4">
                <Avatar className="h-16 w-16">
                  <AvatarFallback className="text-lg font-semibold">
                    {getInitials(kasir.nama)}
                  </AvatarFallback>
                </Avatar>
                <div>
                  <CardTitle className="text-xl">{kasir.nama}</CardTitle>
                  <CardDescription className="text-base">
                    {kasir.user.email}
                  </CardDescription>
                  <div className="flex items-center gap-2 mt-2">
                    <Badge variant="secondary">{kasir.no_hp}</Badge>
                  </div>
                </div>
              </div>
              <div className="text-right">
                <div className="flex items-center gap-2 mb-2">
                  <Store className="h-5 w-5 text-primary" />
                  <CardTitle className="text-lg">{kasir.merchant.nama}</CardTitle>
                </div>
                <CardDescription>
                  {kasir.merchant.alamat || 'No address provided'}
                </CardDescription>
                {kasir.merchant.no_telepon && (
                  <div className="text-sm text-muted-foreground mt-1">
                    📞 {kasir.merchant.no_telepon}
                  </div>
                )}
              </div>
            </div>
          </CardHeader>
        </Card>

        {/* Stats Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Today's Revenue</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {formatCurrency(stats.total_revenue_today)}
              </div>
              <p className="text-xs text-muted-foreground">
                Total earnings today
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Today's Transactions</CardTitle>
              <CreditCard className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {stats.total_transactions_today}
              </div>
              <p className="text-xs text-muted-foreground">
                Transactions processed
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending Orders</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-yellow-600">
                {stats.pending_orders}
              </div>
              <p className="text-xs text-muted-foreground">
                Awaiting confirmation
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Completed Orders</CardTitle>
              <CheckCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {stats.completed_orders}
              </div>
              <p className="text-xs text-muted-foreground">
                Successfully completed
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          {/* Weekly Revenue Chart */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <BarChart3 className="h-5 w-5" />
                Weekly Revenue (Last 7 Days)
              </CardTitle>
              <CardDescription>
                Revenue and transaction trends over the past week
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {weeklyData.map((day, index) => (
                  <div key={index} className="space-y-1">
                    <div className="flex items-center justify-between text-sm">
                      <div className="font-medium">{day.date}</div>
                      <div className="flex items-center gap-4">
                        <span className="text-muted-foreground">{day.transactions} txn</span>
                        <span className="font-medium">{formatCurrency(day.revenue)}</span>
                      </div>
                    </div>
                    <div className="w-full bg-muted rounded-full h-2">
                      <div 
                        className="bg-primary h-2 rounded-full" 
                        style={{ 
                          width: maxWeeklyRevenue > 0 ? `${(day.revenue / maxWeeklyRevenue) * 100}%` : '0%' 
                        }}
                      />
                    </div>
                  </div>
                ))}
              </div>
              <div className="flex items-center text-xs mt-4 pt-4 border-t">
                {revenueTrend >= 0 ? (
                  <TrendingUp className="h-3 w-3 text-green-500 mr-1" />
                ) : (
                  <TrendingDown className="h-3 w-3 text-red-500 mr-1" />
                )}
                <span className={revenueTrend >= 0 ? "text-green-500" : "text-red-500"}>
                  {Math.abs(revenueTrendPercentage).toFixed(1)}% from last week
                </span>
              </div>
            </CardContent>
          </Card>

          {/* Order Status Overview */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <ShoppingCart className="h-5 w-5" />
                Order Status Overview
              </CardTitle>
              <CardDescription>
                Current status of all orders
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="flex items-center justify-between p-3 border rounded-lg">
                  <div className="flex items-center gap-3">
                    <CheckCircle className="h-5 w-5 text-green-500" />
                    <div>
                      <div className="font-medium">Completed Orders</div>
                      <div className="text-sm text-muted-foreground">Successfully processed</div>
                    </div>
                  </div>
                  <div className="text-2xl font-bold text-green-600">
                    {stats.completed_orders}
                  </div>
                </div>

                <div className="flex items-center justify-between p-3 border rounded-lg">
                  <div className="flex items-center gap-3">
                    <Clock className="h-5 w-5 text-yellow-500" />
                    <div>
                      <div className="font-medium">Pending Orders</div>
                      <div className="text-sm text-muted-foreground">Awaiting action</div>
                    </div>
                  </div>
                  <div className="text-2xl font-bold text-yellow-600">
                    {stats.pending_orders}
                  </div>
                </div>

                <div className="flex items-center justify-between p-3 border rounded-lg bg-muted/50">
                  <div className="flex items-center gap-3">
                    <Users className="h-5 w-5 text-blue-500" />
                    <div>
                      <div className="font-medium">Total Today</div>
                      <div className="text-sm text-muted-foreground">All transactions</div>
                    </div>
                  </div>
                  <div className="text-2xl font-bold text-blue-600">
                    {stats.total_transactions_today}
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Recent Transactions */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle className="flex items-center gap-2">
                <Clock className="h-5 w-5" />
                Recent Transactions
              </CardTitle>
              <CardDescription>
                Latest customer transactions at your merchant
              </CardDescription>
            </div>
            <Link href="/kasir/transactions">
              <Button variant="outline" size="sm">
                <Eye className="h-4 w-4 mr-2" />
                View All
              </Button>
            </Link>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {recentTransactions.map((transaction) => (
                <div key={transaction.id} className="flex items-center justify-between py-3 border-b last:border-b-0">
                  <div className="flex items-center space-x-4">
                    <div className="p-2 bg-muted rounded-full">
                      {getTransactionIcon(transaction)}
                    </div>
                    <div>
                      <div className="font-medium">
                        {transaction.wallet.mahasiswa.nama}
                      </div>
                      <div className="text-sm text-muted-foreground">
                        {transaction.wallet.mahasiswa.npm} • {transaction.invoice}
                      </div>
                      <div className="text-xs text-muted-foreground">
                        {new Date(transaction.created_at).toLocaleDateString('en-US', {
                          month: 'short',
                          day: 'numeric',
                          hour: '2-digit',
                          minute: '2-digit'
                        })} • {transaction.detail_produk_transactions?.length || 0} items
                      </div>
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="font-medium text-green-600">
                      {formatCurrency(transaction.amount)}
                    </div>
                    <div className="flex justify-end mt-1">
                      {getStatusBadge(transaction.status_transaksi)}
                    </div>
                  </div>
                </div>
              ))}
              {recentTransactions.length === 0 && (
                <div className="text-center py-8">
                  <ShoppingCart className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground">No transactions yet</p>
                  <p className="text-sm text-muted-foreground">Customer transactions will appear here</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
            <CardDescription>
              Manage your merchant operations
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-2">
              <Link href="/kasir/transactions">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <CreditCard className="h-5 w-5" />
                  All Transactions
                </Button>
              </Link>
              <Link href="/kasir/products">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <Package className="h-5 w-5" />
                  Manage Products
                </Button>
              </Link>
              {/* <Link href="/kasir/reports">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <BarChart3 className="h-5 w-5" />
                  Reports
                </Button>
              </Link>
              <Link href="/kasir/profile">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <Users className="h-5 w-5" />
                  Profile
                </Button>
              </Link> */}
            </div>
          </CardContent>
        </Card>
      </div>
    </KasirLayout>
  );
}