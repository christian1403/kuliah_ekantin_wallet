import { Head, Link, router, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Alert, AlertDescription } from '@/components/ui/alert';
import MahasiswaLayout from '@/layouts/mahasiswa/mahasiswa-layout';
import { index as transactionsIndex, exportMethod as transactionsExport } from '@/routes/mahasiswa/transactions';
import { 
  ArrowUpRight, 
  ArrowDownLeft, 
  Filter,
  Download,
  Search,
  Calendar,
  DollarSign,
  TrendingUp,
  TrendingDown,
  Clock,
  ChevronLeft,
  ChevronRight,
  Eye,
  Wallet,
  CreditCard,
  Building,
  BarChart3
} from 'lucide-react';
import { formatCurrency } from '@/lib/utils';
import { useState } from 'react';

interface WalletTransaction {
  transaction_id: string;
  wallet_id: string;
  invoice: string;
  amount: number;
  tipe_transaksi: 'credit' | 'debit';
  waktu_transaksi: string;
  deskripsi: string;
  curr_balance: number;
  after_balance: number;
  status_transaksi: 'pending' | 'completed' | 'failed';
  created_at: string;
  detailPengeluaran?: {
    kasir: {
      merchant: {
        nama_merchant: string;
      };
    };
    metodeBayar?: {
      nama: string;
    };
  };
  detailPemasukan?: {
    metodeBayar?: {
      nama: string;
    };
  };
}

interface Summary {
  total_credit: number;
  total_debit: number;
  total_transactions: number;
  current_balance: number;
}

interface Filters {
  type?: string;
  status?: string;
  date_from?: string;
  date_to?: string;
  amount_min?: string;
  amount_max?: string;
  search?: string;
}

interface Props extends PageProps {
  transactions: {
    data: WalletTransaction[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  summary: Summary;
  filters: Filters;
}

export default function TransactionHistoryIndex({ transactions, summary, filters }: Props) {
  const [showFilters, setShowFilters] = useState(false);

  const { data, setData, get, processing } = useForm({
    type: filters.type || '',
    status: filters.status || '',
    date_from: filters.date_from || '',
    date_to: filters.date_to || '',
    amount_min: filters.amount_min || '',
    amount_max: filters.amount_max || '',
    search: filters.search || '',
  });

  const handleFilter = () => {
    get(transactionsIndex.url(), {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleReset = () => {
    router.get(transactionsIndex.url());
  };

  const handleExport = () => {
    window.open(transactionsExport.url());
  };

  const getTransactionIcon = (transaction: WalletTransaction) => {
    return transaction.tipe_transaksi === 'credit' ? (
      <ArrowDownLeft className="h-4 w-4 text-green-500" />
    ) : (
      <ArrowUpRight className="h-4 w-4 text-red-500" />
    );
  };

  const getTransactionColor = (transaction: WalletTransaction) => {
    return transaction.tipe_transaksi === 'credit' ? 'text-green-600' : 'text-red-600';
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

  const getPaymentMethodIcon = (transaction: WalletTransaction) => {
    const methodName = transaction.detailPemasukan?.metodeBayar?.nama || 
                      transaction.detailPengeluaran?.metodeBayar?.nama || '';
    
    if (methodName.toLowerCase().includes('bank')) {
      return <Building className="h-4 w-4" />;
    } else if (methodName.toLowerCase().includes('wallet')) {
      return <Wallet className="h-4 w-4" />;
    } else {
      return <CreditCard className="h-4 w-4" />;
    }
  };

  return (
    <MahasiswaLayout 
      title="Transaction History - Student Portal"
      breadcrumbs={[
        { title: 'Transactions', href: '/mahasiswa/transactions' }
      ]}
    >
      <div className="flex-1 space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Transaction History</h1>
            <p className="text-muted-foreground">
              View and manage your wallet transaction history
            </p>
          </div>
          <div className="flex items-center gap-2">
            {/* <Link href="/mahasiswa/analytics">
              <Button variant="outline" size="sm">
                <BarChart3 className="h-4 w-4 mr-2" />
                Analytics
              </Button>
            </Link> */}
            {/* <Button variant="outline" size="sm" onClick={handleExport}>
              <Download className="h-4 w-4 mr-2" />
              Export
            </Button> */}
          </div>
        </div>

        {/* Summary Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Current Balance</CardTitle>
              <Wallet className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {formatCurrency(summary.current_balance)}
              </div>
              <p className="text-xs text-muted-foreground">
                Available in wallet
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Income</CardTitle>
              <TrendingUp className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {formatCurrency(summary.total_credit)}
              </div>
              <p className="text-xs text-muted-foreground">
                All time earnings
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Spending</CardTitle>
              <TrendingDown className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">
                {formatCurrency(summary.total_debit)}
              </div>
              <p className="text-xs text-muted-foreground">
                All time expenses
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Transactions</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {summary.total_transactions.toLocaleString()}
              </div>
              <p className="text-xs text-muted-foreground">
                All transactions
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <CardTitle className="flex items-center gap-2">
                <Filter className="h-5 w-5" />
                Filters
              </CardTitle>
              <Button
                variant="outline"
                size="sm"
                onClick={() => setShowFilters(!showFilters)}
              >
                {showFilters ? 'Hide Filters' : 'Show Filters'}
              </Button>
            </div>
          </CardHeader>
          {showFilters && (
            <CardContent>
              <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                {/* Search */}
                <div className="space-y-2">
                  <Label htmlFor="search">Search</Label>
                  <div className="relative">
                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="search"
                      placeholder="Search description..."
                      className="pl-8"
                      value={data.search}
                      onChange={(e) => setData('search', e.target.value)}
                    />
                  </div>
                </div>

                {/* Transaction Type */}
                <div className="space-y-2">
                  <Label>Transaction Type</Label>
                  <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                    <SelectTrigger>
                      <SelectValue placeholder="All types" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All types</SelectItem>
                      <SelectItem value="credit">Income</SelectItem>
                      <SelectItem value="debit">Expense</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                {/* Status */}
                <div className="space-y-2">
                  <Label>Status</Label>
                  <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                    <SelectTrigger>
                      <SelectValue placeholder="All statuses" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All statuses</SelectItem>
                      <SelectItem value="completed">Completed</SelectItem>
                      <SelectItem value="pending">Pending</SelectItem>
                      <SelectItem value="failed">Failed</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                {/* Date From */}
                <div className="space-y-2">
                  <Label htmlFor="date_from">Date From</Label>
                  <Input
                    id="date_from"
                    type="date"
                    value={data.date_from}
                    onChange={(e) => setData('date_from', e.target.value)}
                  />
                </div>

                {/* Date To */}
                <div className="space-y-2">
                  <Label htmlFor="date_to">Date To</Label>
                  <Input
                    id="date_to"
                    type="date"
                    value={data.date_to}
                    onChange={(e) => setData('date_to', e.target.value)}
                  />
                </div>

                {/* Amount Min */}
                <div className="space-y-2">
                  <Label htmlFor="amount_min">Min Amount</Label>
                  <div className="relative">
                    <span className="absolute left-3 top-2.5 text-sm text-muted-foreground">Rp</span>
                    <Input
                      id="amount_min"
                      type="number"
                      placeholder="0"
                      className="pl-8"
                      value={data.amount_min}
                      onChange={(e) => setData('amount_min', e.target.value)}
                    />
                  </div>
                </div>

                {/* Amount Max */}
                <div className="space-y-2">
                  <Label htmlFor="amount_max">Max Amount</Label>
                  <div className="relative">
                    <span className="absolute left-3 top-2.5 text-sm text-muted-foreground">Rp</span>
                    <Input
                      id="amount_max"
                      type="number"
                      placeholder="0"
                      className="pl-8"
                      value={data.amount_max}
                      onChange={(e) => setData('amount_max', e.target.value)}
                    />
                  </div>
                </div>
              </div>

              <div className="flex items-center gap-2 mt-4">
                <Button onClick={handleFilter} disabled={processing}>
                  <Filter className="h-4 w-4 mr-2" />
                  Apply Filters
                </Button>
                <Button variant="outline" onClick={handleReset}>
                  Reset
                </Button>
              </div>
            </CardContent>
          )}
        </Card>

        {/* Transactions Table */}
        <Card>
          <CardHeader>
            <CardTitle>Transaction History</CardTitle>
            <CardDescription>
              {transactions.total} transactions found
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Transaction</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Date</TableHead>
                    {/* <TableHead>Actions</TableHead> */}
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {transactions.data.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center py-8">
                        <div className="flex flex-col items-center gap-2">
                          <Clock className="h-8 w-8 text-muted-foreground" />
                          <p className="text-muted-foreground">No transactions found</p>
                        </div>
                      </TableCell>
                    </TableRow>
                  ) : (
                    transactions.data.map((transaction) => (
                      <TableRow key={transaction.transaction_id}>
                        <TableCell>
                          <div className="flex items-center gap-3">
                            <div className="p-2 bg-muted rounded-full">
                              {getTransactionIcon(transaction)}
                            </div>
                            <div>
                              <div className="font-medium line-clamp-1">
                                {transaction.invoice}
                              </div>
                              <div className="text-sm text-muted-foreground line-clamp-1">
                                {transaction.detailPengeluaran ? 
                                  transaction.detailPengeluaran.kasir.merchant.nama_merchant :
                                  transaction.deskripsi
                                }
                              </div>
                            </div>
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            {getPaymentMethodIcon(transaction)}
                            <span className="capitalize">
                              {transaction.tipe_transaksi === 'credit' ? 'Income' : 'Expense'}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className={`font-medium ${getTransactionColor(transaction)}`}>
                            {transaction.tipe_transaksi === 'credit' ? '+' : '-'}
                            {formatCurrency(transaction.amount)}
                          </div>
                        </TableCell>
                        <TableCell>
                          {getStatusBadge(transaction.status_transaksi)}
                        </TableCell>
                        <TableCell>
                          <div className="text-sm">
                            {new Date(transaction.created_at).toLocaleDateString('en-US', {
                              year: 'numeric',
                              month: 'short',
                              day: 'numeric'
                            })}
                          </div>
                          <div className="text-xs text-muted-foreground">
                            {new Date(transaction.created_at).toLocaleTimeString('en-US', {
                              hour: '2-digit',
                              minute: '2-digit'
                            })}
                          </div>
                        </TableCell>
                        {/* <TableCell>
                          <Link href={`/mahasiswa/transactions/${transaction.transaction_id}`}>
                            <Button variant="ghost" size="sm">
                              <Eye className="h-4 w-4" />
                            </Button>
                          </Link>
                        </TableCell> */}
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </div>

            {/* Pagination */}
            {transactions.last_page > 1 && (
              <div className="flex items-center justify-between mt-4">
                <div className="text-sm text-muted-foreground">
                  Showing {(transactions.current_page - 1) * transactions.per_page + 1} to{' '}
                  {Math.min(transactions.current_page * transactions.per_page, transactions.total)} of{' '}
                  {transactions.total} results
                </div>
                <div className="flex items-center gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    disabled={transactions.current_page <= 1}
                    onClick={() => router.get(`${transactionsIndex.url()}?page=${transactions.current_page - 1}`, filters)}
                  >
                    <ChevronLeft className="h-4 w-4" />
                    Previous
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    disabled={transactions.current_page >= transactions.last_page}
                    onClick={() => router.get(`${transactionsIndex.url()}?page=${transactions.current_page + 1}`, filters)}
                  >
                    Next
                    <ChevronRight className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </MahasiswaLayout>
  );
}