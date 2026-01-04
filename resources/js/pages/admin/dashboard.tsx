import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import AdminLayout from '@/layouts/admin/admin-layout';
import { 
  Users, 
  Store,
  UserCheck,
  GraduationCap,
  CreditCard,
  TrendingUp, 
  TrendingDown, 
  ArrowUpRight, 
  ArrowDownRight,
  ArrowDownLeft,
  Calendar,
  PieChart,
  BarChart3,
  Clock,
  DollarSign,
  Activity,
  AlertTriangle,
  CheckCircle,
  Building,
  Settings
} from 'lucide-react';
import { formatCurrency } from '@/lib/utils';

interface Stats {
  total_users: number;
  total_merchants: number;
  total_kasir: number;
  total_mahasiswa: number;
}

interface TransactionStats {
  total_transactions_today: number;
  pending_transactions: number;
  completed_transactions: number;
}

interface RecentTransaction {
  transaction_id: string;
  amount: number;
  status_transaksi: 'pending' | 'completed' | 'failed';
  created_at: string;
  wallet?: {
    mahasiswa?: {
      user?: {
        name: string;
        email: string;
      };
    };
  };
  detail_pengeluaran?: any;
  detail_pemasukan?: any;
}

interface MonthlyData {
  month: string;
  transactions: number;
}

interface Props extends PageProps {
  stats: Stats;
  transactionStats: TransactionStats;
  recentTransactions: RecentTransaction[];
  monthlyData: MonthlyData[];
}

export default function AdminDashboard({ 
  stats, 
  transactionStats, 
  recentTransactions, 
  monthlyData 
}: Props) {
  const getTransactionIcon = (transaction: RecentTransaction) => {
    if (transaction.detail_pemasukan) {
      return <ArrowDownLeft className="h-4 w-4 text-green-500" />;
    }
    return <ArrowUpRight className="h-4 w-4 text-red-500" />;
  };

  const getTransactionColor = (transaction: RecentTransaction) => {
    if (transaction.detail_pemasukan) {
      return 'text-green-600';
    }
    return 'text-red-600';
  };

  const getStatusBadge = (status: string) => {
    const variants: { [key: string]: "default" | "secondary" | "destructive" | "outline" } = {
      completed: "default",
      pending: "secondary", 
      failed: "destructive"
    };
    
    const colors: { [key: string]: string } = {
      completed: "bg-green-100 text-green-800",
      pending: "bg-yellow-100 text-yellow-800",
      failed: "bg-red-100 text-red-800"
    };
    
    return (
      <Badge variant={variants[status] || "outline"} className={colors[status]}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  // Calculate completion rate
  const completionRate = transactionStats.total_transactions_today > 0 
    ? (transactionStats.completed_transactions / transactionStats.total_transactions_today) * 100 
    : 0;

  // Get maximum transactions for chart scaling
  const maxTransactions = Math.max(...monthlyData.map(d => d.transactions));

  return (
    <AdminLayout 
      title="Admin Dashboard - Admin Portal"
      breadcrumbs={[{ title: 'Dashboard', href: '/admin/dashboard' }]}
    >
      <div className="flex-1 space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
            <p className="text-muted-foreground">
              Monitor and manage your wallet application
            </p>
          </div>
          <div className="flex items-center gap-2">
            {/* <Link href="/admin/settings">
              <Button variant="outline" size="sm">
                <Settings className="h-4 w-4 mr-2" />
                Settings
              </Button>
            </Link>
            <Link href="/admin/reports">
              <Button size="sm">
                <BarChart3 className="h-4 w-4 mr-2" />
                Reports
              </Button>
            </Link> */}
          </div>
        </div>

        {/* User Statistics Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Users</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">
                {stats.total_users.toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">
                Registered users
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Mahasiswa</CardTitle>
              <GraduationCap className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {stats.total_mahasiswa.toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">
                Active mahasiswa
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Merchants</CardTitle>
              <Store className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-purple-600">
                {stats.total_merchants.toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">
                Active merchants
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Cashiers</CardTitle>
              <UserCheck className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-orange-600">
                {stats.total_kasir.toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">
                Active cashiers
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Transaction Statistics Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Today's Transactions</CardTitle>
              <Activity className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {transactionStats.total_transactions_today.toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">
                Transactions today
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Completed</CardTitle>
              <CheckCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {transactionStats.completed_transactions.toLocaleString()}
              </div>
              <div className="flex items-center text-xs">
                <TrendingUp className="h-3 w-3 text-green-500 mr-1" />
                <span className="text-green-500">
                  {completionRate.toFixed(1)}% completion rate
                </span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-yellow-600">
                {transactionStats.pending_transactions.toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">
                Awaiting processing
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Failed Transactions</CardTitle>
              <AlertTriangle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">
                {(transactionStats.total_transactions_today - 
                  transactionStats.completed_transactions - 
                  transactionStats.pending_transactions).toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">
                Need attention
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          {/* Monthly Transaction Chart */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <BarChart3 className="h-5 w-5" />
                Transaction Volume (Last 12 Months)
              </CardTitle>
              <CardDescription>
                Monthly transaction trends across the platform
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {monthlyData.map((month, index) => (
                  <div key={index} className="flex items-center justify-between">
                    <div className="text-sm font-medium">{month.month}</div>
                    <div className="flex items-center gap-3">
                      <div className="w-32 bg-muted rounded-full h-2">
                        <div 
                          className="bg-primary h-2 rounded-full" 
                          style={{ 
                            width: maxTransactions > 0 ? `${(month.transactions / maxTransactions) * 100}%` : '0%' 
                          }}
                        />
                      </div>
                      <div className="text-sm text-muted-foreground w-16 text-right">
                        {month.transactions.toLocaleString()}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* System Health */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Activity className="h-5 w-5" />
                System Health
              </CardTitle>
              <CardDescription>
                Current system status and metrics
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <div className="text-sm font-medium">Transaction Success Rate</div>
                  <div className="flex items-center gap-2">
                    <div className="w-20 bg-muted rounded-full h-2">
                      <div 
                        className="bg-green-500 h-2 rounded-full" 
                        style={{ width: `${completionRate}%` }}
                      />
                    </div>
                    <Badge variant="secondary" className="text-green-600">
                      {completionRate.toFixed(1)}%
                    </Badge>
                  </div>
                </div>
                
                <div className="flex items-center justify-between">
                  <div className="text-sm font-medium">User Activity</div>
                  <Badge variant="default" className="bg-blue-100 text-blue-600">
                    High
                  </Badge>
                </div>
                
                <div className="flex items-center justify-between">
                  <div className="text-sm font-medium">System Status</div>
                  <Badge variant="default" className="bg-green-100 text-green-600">
                    Operational
                  </Badge>
                </div>

                <div className="flex items-center justify-between">
                  <div className="text-sm font-medium">Pending Reviews</div>
                  <Badge variant="secondary" className="bg-yellow-100 text-yellow-600">
                    {transactionStats.pending_transactions}
                  </Badge>
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
                <CreditCard className="h-5 w-5" />
                Recent Transactions
              </CardTitle>
              <CardDescription>
                Latest wallet transactions across the platform
              </CardDescription>
            </div>
            <Link href="/admin/transactions">
              <Button variant="outline" size="sm">
                <BarChart3 className="h-4 w-4 mr-2" />
                View All
              </Button>
            </Link>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {recentTransactions.map((transaction) => (
                <div key={transaction.transaction_id} className="flex items-center justify-between py-3 border-b border-border last:border-b-0">
                  <div className="flex items-center space-x-4">
                    <div className="p-2 bg-muted rounded-full">
                      {getTransactionIcon(transaction)}
                    </div>
                    <div>
                      <div className="text-sm font-medium">
                        {transaction.wallet?.mahasiswa?.user?.name || 'Unknown User'}
                      </div>
                      <div className="text-xs text-muted-foreground">
                        {transaction.wallet?.mahasiswa?.user?.email || 'No email'}
                      </div>
                      <div className="text-xs text-muted-foreground">
                        {new Date(transaction.created_at).toLocaleDateString('en-US', {
                          month: 'short',
                          day: 'numeric',
                          hour: '2-digit',
                          minute: '2-digit'
                        })}
                      </div>
                    </div>
                  </div>
                  <div className="text-right">
                    <div className={`text-sm font-medium ${getTransactionColor(transaction)}`}>
                      {transaction.detail_pemasukan ? '+' : '-'}{formatCurrency(transaction.amount)}
                    </div>
                    <div className="flex justify-end mt-1">
                      {getStatusBadge(transaction.status_transaksi)}
                    </div>
                  </div>
                </div>
              ))}
              {recentTransactions.length === 0 && (
                <div className="text-center py-8">
                  <CreditCard className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground">No transactions yet</p>
                  <p className="text-sm text-muted-foreground">Transactions will appear here as users make them</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle>Admin Actions</CardTitle>
            <CardDescription>
              Quick access to common administrative tasks
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-1">
              {/* <Link href="/admin/users">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <Users className="h-5 w-5" />
                  Manage Users
                </Button>
              </Link> */}
              {/* <Link href="/admin/merchants">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <Building className="h-5 w-5" />
                  Manage Merchants
                </Button>
              </Link> */}
              <Link href="/admin/transactions">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <CreditCard className="h-5 w-5" />
                  View Transactions
                </Button>
              </Link>
              {/* <Link href="/admin/reports">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <BarChart3 className="h-5 w-5" />
                  Generate Reports
                </Button>
              </Link> */}
            </div>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}