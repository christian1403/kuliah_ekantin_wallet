import { Head, Link, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import AdminLayout from '@/layouts/admin/admin-layout';
import { 
  Search,
  Download,
  Filter,
  Calendar,
  ArrowUpRight,
  ArrowDownLeft,
  Clock,
  CheckCircle,
  XCircle,
  Eye,
  Users,
  DollarSign,
  TrendingUp,
  Activity,
  Check,
  Loader2
} from 'lucide-react';
import { formatCurrency } from '@/lib/utils';

interface Transaction {
  transaction_id: string;
  amount: number;
    invoice: string;
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
  detail_pemasukan?: {
    metode_bayar?: {
      nama: string;
    };
  };
  detail_pengeluaran?: any;
}

interface Stats {
  total_transactions: number;
  pending_transactions: number;
  completed_transactions: number;
  failed_transactions: number;
  total_amount_today: number;
  top_up_count: number;
  payment_count: number;
}

interface Filters {
  search?: string;
  status?: string;
  type?: string;
  date_from?: string;
  date_to?: string;
  per_page: number;
}

interface Props extends PageProps {
  transactions: {
    data: Transaction[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
  };
  stats: Stats;
  filters: Filters;
}

export default function TransactionsIndex({ transactions, stats, filters }: Props) {
  const [searchTerm, setSearchTerm] = useState(filters.search || '');
  const [selectedStatus, setSelectedStatus] = useState(filters.status || '');
  const [selectedType, setSelectedType] = useState(filters.type || '');
  const [dateFrom, setDateFrom] = useState(filters.date_from || '');
  const [dateTo, setDateTo] = useState(filters.date_to || '');
  const [perPage, setPerPage] = useState(filters.per_page.toString());
  const [confirmingTransactions, setConfirmingTransactions] = useState<Set<string>>(new Set());

  const { post } = useForm();

  const handleConfirmTransaction = (transactionId: string) => {
    if (confirmingTransactions.has(transactionId)) return;

    setConfirmingTransactions(prev => new Set(prev).add(transactionId));
    
    post(`/admin/transactions/${transactionId}/confirm`, {
      onSuccess: () => {
        setConfirmingTransactions(prev => {
          const newSet = new Set(prev);
          newSet.delete(transactionId);
          return newSet;
        });
      },
      onError: () => {
        setConfirmingTransactions(prev => {
          const newSet = new Set(prev);
          newSet.delete(transactionId);
          return newSet;
        });
      },
    });
  };

  const getTransactionIcon = (transaction: Transaction) => {
    if (transaction.detail_pemasukan) {
      return <ArrowDownLeft className="h-4 w-4 text-green-500" />;
    }
    return <ArrowUpRight className="h-4 w-4 text-red-500" />;
  };

  const getStatusIcon = (status: string) => {
    const icons = {
      completed: <CheckCircle className="h-4 w-4 text-green-500" />,
      pending: <Clock className="h-4 w-4 text-yellow-500" />,
      failed: <XCircle className="h-4 w-4 text-red-500" />
    };
    return icons[status as keyof typeof icons] || <Clock className="h-4 w-4" />;
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

  const handleFilter = () => {
    const params = new URLSearchParams();
    if (searchTerm) params.set('search', searchTerm);
    if (selectedStatus) params.set('status', selectedStatus);
    if (selectedType) params.set('type', selectedType);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);
    if (perPage !== '10') params.set('per_page', perPage);
    
    window.location.href = `/admin/transactions?${params.toString()}`;
  };

  const handleExport = () => {
    const params = new URLSearchParams();
    if (searchTerm) params.set('search', searchTerm);
    if (selectedStatus) params.set('status', selectedStatus);
    if (selectedType) params.set('type', selectedType);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);
    
    window.open(`/admin/transactions/export?${params.toString()}`, '_blank');
  };

  const clearFilters = () => {
    setSearchTerm('');
    setSelectedStatus('');
    setSelectedType('');
    setDateFrom('');
    setDateTo('');
    setPerPage('10');
    window.location.href = '/admin/transactions';
  };

  return (
    <AdminLayout 
      title="Transaction Management - Admin Portal"
      breadcrumbs={[
        { title: 'Transactions', href: '/admin/transactions' }
      ]}
    >
      <div className="flex-1 space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Transaction Management</h1>
            <p className="text-muted-foreground">
              Monitor and manage all wallet transactions
            </p>
          </div>
          <div className="flex items-center gap-2">
            {/* <Button onClick={handleExport} variant="outline" size="sm">
              <Download className="h-4 w-4 mr-2" />
              Export
            </Button> */}
            <Link href="/admin/dashboard">
              <Button variant="outline" size="sm">
                Back to Dashboard
              </Button>
            </Link>
          </div>
        </div>

        {/* Statistics Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Transactions</CardTitle>
              <Activity className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.total_transactions.toLocaleString()}</div>
              <p className="text-xs text-muted-foreground">All time</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Completed</CardTitle>
              <CheckCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {stats.completed_transactions.toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">Successful transactions</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-yellow-600">
                {stats.pending_transactions.toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">Awaiting processing</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Today's Volume</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">
                {formatCurrency(stats.total_amount_today)}
              </div>
              <p className="text-xs text-muted-foreground">Completed today</p>
            </CardContent>
          </Card>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Filter className="h-5 w-5" />
              Filters
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-6">
              <div className="md:col-span-2">
                <Input
                  placeholder="Search transactions, users..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="w-full"
                />
              </div>
              
              <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                <SelectTrigger>
                  <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="failed">Failed</SelectItem>
                </SelectContent>
              </Select>

              <Select value={selectedType} onValueChange={setSelectedType}>
                <SelectTrigger>
                  <SelectValue placeholder="Type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Types</SelectItem>
                  <SelectItem value="top_up">Top Up</SelectItem>
                  <SelectItem value="payment">Payment</SelectItem>
                </SelectContent>
              </Select>

              <Input
                type="date"
                value={dateFrom}
                onChange={(e) => setDateFrom(e.target.value)}
                placeholder="From Date"
              />

              <Input
                type="date"
                value={dateTo}
                onChange={(e) => setDateTo(e.target.value)}
                placeholder="To Date"
              />
            </div>

            <div className="flex gap-2 mt-4">
              <Button onClick={handleFilter}>
                <Search className="h-4 w-4 mr-2" />
                Apply Filters
              </Button>
              <Button variant="outline" onClick={clearFilters}>
                Clear Filters
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Transactions Table */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle>Transactions</CardTitle>
                <CardDescription>
                  Showing {transactions.from} to {transactions.to} of {transactions.total} transactions
                </CardDescription>
              </div>
              <Select value={perPage} onValueChange={setPerPage}>
                <SelectTrigger className="w-20">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="10">10</SelectItem>
                  <SelectItem value="25">25</SelectItem>
                  <SelectItem value="50">50</SelectItem>
                  <SelectItem value="100">100</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Transaction</TableHead>
                  <TableHead>User</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Amount</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Date</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {transactions.data.map((transaction) => (
                  <TableRow key={transaction.transaction_id}>
                    <TableCell>
                      <div className="flex items-center gap-3">
                        <div className="p-2 bg-muted rounded-full">
                          {getTransactionIcon(transaction)}
                        </div>
                        <div>
                          <div className="font-medium">{transaction.invoice}</div>
                          <div className="text-sm text-muted-foreground">
                            {transaction.detail_pemasukan?.metode_bayar?.nama || 'Direct Payment'}
                          </div>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-3">
                        <Avatar className="h-8 w-8">
                          <AvatarFallback>
                            {transaction.wallet?.mahasiswa?.user?.name?.charAt(0) || 'U'}
                          </AvatarFallback>
                        </Avatar>
                        <div>
                          <div className="font-medium">
                            {transaction.wallet?.mahasiswa?.user?.name || 'Unknown User'}
                          </div>
                          <div className="text-sm text-muted-foreground">
                            {transaction.wallet?.mahasiswa?.user?.email || 'No email'}
                          </div>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant="outline" className={transaction.detail_pemasukan ? 'text-green-600' : 'text-red-600'}>
                        {transaction.detail_pemasukan ? 'Top Up' : 'Payment'}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div className={`font-medium ${transaction.detail_pemasukan ? 'text-green-600' : 'text-red-600'}`}>
                        {transaction.detail_pemasukan ? '+' : '-'}{formatCurrency(transaction.amount)}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        {getStatusIcon(transaction.status_transaksi)}
                        {getStatusBadge(transaction.status_transaksi)}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="text-sm">
                        {new Date(transaction.created_at).toLocaleDateString('en-US', {
                          month: 'short',
                          day: 'numeric',
                          year: 'numeric'
                        })}
                      </div>
                      <div className="text-xs text-muted-foreground">
                        {new Date(transaction.created_at).toLocaleTimeString('en-US', {
                          hour: '2-digit',
                          minute: '2-digit'
                        })}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        {/* <Link href={`/admin/transactions/${transaction.transaction_id}`}>
                          <Button variant="ghost" size="sm">
                            <Eye className="h-4 w-4" />
                          </Button>
                        </Link> */}
                        {transaction.status_transaksi === 'pending' && !transaction?.detail_pengeluaran && (
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleConfirmTransaction(transaction.transaction_id)}
                            disabled={confirmingTransactions.has(transaction.transaction_id)}
                            className="text-green-600 hover:text-green-700 hover:bg-green-50"
                          >
                            {confirmingTransactions.has(transaction.transaction_id) ? (
                              <Loader2 className="h-4 w-4 animate-spin" />
                            ) : (
                              <Check className="h-4 w-4" />
                            )}
                          </Button>
                        )}
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>

            {transactions.data.length === 0 && (
              <div className="text-center py-8">
                <Activity className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <p className="text-muted-foreground">No transactions found</p>
                <p className="text-sm text-muted-foreground">Try adjusting your filters</p>
              </div>
            )}

            {/* Pagination */}
            {transactions.last_page > 1 && (
              <div className="flex items-center justify-between mt-6">
                <div className="text-sm text-muted-foreground">
                  Showing {transactions.from} to {transactions.to} of {transactions.total} results
                </div>
                <div className="flex items-center gap-2">
                  {transactions.current_page > 1 && (
                    <Link 
                      href={`/admin/transactions?page=${transactions.current_page - 1}&${new URLSearchParams(filters as any).toString()}`}
                    >
                      <Button variant="outline" size="sm">Previous</Button>
                    </Link>
                  )}
                  
                  <span className="text-sm">
                    Page {transactions.current_page} of {transactions.last_page}
                  </span>
                  
                  {transactions.current_page < transactions.last_page && (
                    <Link 
                      href={`/admin/transactions?page=${transactions.current_page + 1}&${new URLSearchParams(filters as any).toString()}`}
                    >
                      <Button variant="outline" size="sm">Next</Button>
                    </Link>
                  )}
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}