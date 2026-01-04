import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ScrollArea } from '@/components/ui/scroll-area';
import KasirLayout from '@/layouts/kasir/kasir-layout';
import { 
  Search, 
  Filter,
  ArrowUpRight, 
  ArrowDownRight,
  Clock,
  CheckCircle,
  AlertCircle,
  Eye,
  Calendar,
  DollarSign,
  ShoppingCart,
  User,
  Package,
  ChevronLeft,
  ChevronRight,
  WalletMinimal
} from 'lucide-react';
import { formatCurrency } from '@/lib/utils';

interface DetailProdukTransaction {
  id: string;
  qty: number;
  produk: {
    produk_id: string;
    nama: string;
    harga: number;
    kode_produk: string;
  };
}

interface Transaction {
  transaction_id: string;
  invoice: string;
  amount: number;
  status_transaksi: 'pending' | 'completed' | 'failed';
  tipe_transaksi: 'credit' | 'debit';
  deskripsi: string;
  created_at: string;
  wallet: {
    mahasiswa: {
      id: string;
      nama: string;
      npm: string;
      user: {
        name: string;
        email: string;
      };
    };
  };
  detail_produk_transactions: DetailProdukTransaction[];
}

interface PaginationLinks {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedTransactions {
  data: Transaction[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  links: PaginationLinks[];
}

interface Props extends PageProps {
  transactions: PaginatedTransactions;
  filters: {
    search?: string;
    status?: string;
    date?: string;
  };
}

export default function KasirTransactions({ transactions, filters }: Props) {
  const [searchTerm, setSearchTerm] = useState(filters.search || '');
  const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
  const [dateFilter, setDateFilter] = useState(filters.date || 'all');
  const [processingConfirm, setProcessingConfirm] = useState<string | null>(null);

  const getInitials = (name: string) => {
    return name
      ?.split(' ')
      .map(n => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  const getTransactionIcon = (transaction: Transaction) => {
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
    
    const colors: { [key: string]: string } = {
      completed: "bg-green-100 text-green-800 hover:bg-green-100",
      pending: "bg-yellow-100 text-yellow-800 hover:bg-yellow-100",
      failed: "bg-red-100 text-red-800 hover:bg-red-100"
    };
    
    return (
      <Badge variant={variants[status] || "outline"} className={colors[status]}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  const getTransactionTypeIcon = (type: string) => {
    return type === 'credit' ? (
      <ArrowDownRight className="h-4 w-4 text-green-500" />
    ) : (
      <ArrowUpRight className="h-4 w-4 text-red-500" />
    );
  };

  // Calculate total items for a transaction
  const getTotalItems = (transaction: Transaction) => {
    console.log(transaction.detail_produk_transactions);
    return transaction.detail_produk_transactions?.reduce((total, item) => total + item.qty, 0) || 0;
  };

  const handleSearch = (value: string) => {
    setSearchTerm(value);
    applyFilters({ search: value, status: statusFilter, date: dateFilter });
  };

  const handleStatusFilter = (value: string) => {
    setStatusFilter(value);
    applyFilters({ search: searchTerm, status: value, date: dateFilter });
  };

  const handleDateFilter = (value: string) => {
    setDateFilter(value);
    applyFilters({ search: searchTerm, status: statusFilter, date: value });
  };

  const applyFilters = (filters: { search: string; status: string; date: string }) => {
    const params = new URLSearchParams();
    
    if (filters.search) params.append('search', filters.search);
    if (filters.status && filters.status !== 'all') params.append('status', filters.status);
    if (filters.date && filters.date !== 'all') params.append('date', filters.date);
    
    const queryString = params.toString();
    const url = queryString ? `/kasir/transactions?${queryString}` : '/kasir/transactions';
    
    router.get(url, {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const clearFilters = () => {
    setSearchTerm('');
    setStatusFilter('all');
    setDateFilter('all');
    router.get('/kasir/transactions');
  };

  const navigateToPage = (url: string) => {
    router.get(url);
  };

  const handleConfirmTransaction = (transactionId: string) => {
    if (processingConfirm) return;
    
    setProcessingConfirm(transactionId);
    
    router.post(`/kasir/transactions/${transactionId}/confirm`, {}, {
      onSuccess: () => {
        setProcessingConfirm(null);
        // Refresh the current page to show updated data
        router.reload();
      },
      onError: (errors) => {
        console.error('Failed to confirm transaction:', errors);
        setProcessingConfirm(null);
      },
      onFinish: () => {
        setProcessingConfirm(null);
      }
    });
  };

  return (
    <KasirLayout 
      title="Transactions - Kasir Portal"
      breadcrumbs={[
        { title: 'Transactions', href: '/kasir/transactions' }
      ]}
    >
      <div className="flex-1 space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Transactions</h1>
            <p className="text-muted-foreground">
              View and manage all customer transactions ({transactions.total} total)
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Link href="/kasir/dashboard">
              <Button variant="outline" size="sm">
                <ArrowUpRight className="h-4 w-4 mr-2" />
                Back to Dashboard
              </Button>
            </Link>
          </div>
        </div>

        {/* Filters and Search */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Filter className="h-5 w-5" />
              Filter Transactions
            </CardTitle>
            <CardDescription>
              Search and filter transactions by various criteria
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-4">
              <div className="relative">
                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search by invoice, student name, or NPM..."
                  value={searchTerm}
                  onChange={(e) => handleSearch(e.target.value)}
                  className="pl-10"
                />
              </div>
              <Select value={statusFilter} onValueChange={handleStatusFilter}>
                <SelectTrigger>
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="failed">Failed</SelectItem>
                </SelectContent>
              </Select>
              <Select value={dateFilter} onValueChange={handleDateFilter}>
                <SelectTrigger>
                  <SelectValue placeholder="Filter by date" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Time</SelectItem>
                  <SelectItem value="today">Today</SelectItem>
                  <SelectItem value="yesterday">Yesterday</SelectItem>
                  <SelectItem value="week">This Week</SelectItem>
                  <SelectItem value="month">This Month</SelectItem>
                </SelectContent>
              </Select>
              <Button 
                variant="outline" 
                onClick={clearFilters}
              >
                Clear Filters
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Transaction Statistics */}
        <div className="grid gap-4 md:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Transactions</CardTitle>
              <ShoppingCart className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{transactions.total}</div>
              <p className="text-xs text-muted-foreground">
                All time transactions
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
                {transactions.data.filter(t => t.status_transaksi === 'completed').length}
              </div>
              <p className="text-xs text-muted-foreground">
                On current page
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-yellow-600">
                {transactions.data.filter(t => t.status_transaksi === 'pending').length}
              </div>
              <p className="text-xs text-muted-foreground">
                Awaiting confirmation
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
              <WalletMinimal className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {formatCurrency(transactions.data.reduce((sum, t) => parseFloat(sum) + parseFloat(t.amount), 0))}
              </div>
              <p className="text-xs text-muted-foreground">
                Current page total
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Transactions List */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <ShoppingCart className="h-5 w-5" />
              Transaction History
            </CardTitle>
            <CardDescription>
              Showing {transactions.from} to {transactions.to} of {transactions.total} transactions
            </CardDescription>
          </CardHeader>
          <CardContent>
            {transactions.data.length === 0 ? (
              <div className="text-center py-12">
                <ShoppingCart className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <p className="text-muted-foreground">No transactions found</p>
                <p className="text-sm text-muted-foreground">Try adjusting your search or filter criteria</p>
              </div>
            ) : (
              <div className="space-y-4">
                {transactions.data.map((transaction) => (
                  <Card key={transaction.transaction_id} className="hover:shadow-md transition-shadow">
                    <CardContent className="p-6">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-4">
                          <div className="flex items-center gap-2">
                            {getTransactionIcon(transaction)}
                            <Avatar className="h-10 w-10">
                              <AvatarFallback className="text-sm">
                                {getInitials(transaction.wallet.mahasiswa.nama)}
                              </AvatarFallback>
                            </Avatar>
                          </div>
                          <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2 mb-1">
                              <h3 className="font-semibold text-lg">{transaction.invoice}</h3>
                              {getStatusBadge(transaction.status_transaksi)}
                            </div>
                            <div className="flex items-center gap-4 text-sm text-muted-foreground">
                              <div className="flex items-center gap-1">
                                <User className="h-3 w-3" />
                                <span>{transaction.wallet.mahasiswa.nama}</span>
                                <span className="text-xs">({transaction.wallet.mahasiswa.npm})</span>
                              </div>
                              <div className="flex items-center gap-1">
                                <Calendar className="h-3 w-3" />
                                <span>
                                  {new Date(transaction.created_at).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                  })}
                                </span>
                              </div>
                              <div className="flex items-center gap-1">
                                <Package className="h-3 w-3" />
                                <span>{getTotalItems(transaction)} items</span>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div className="text-right">
                          <div className="text-2xl font-bold text-green-600 mb-2">
                            {formatCurrency(transaction.amount)}
                          </div>
                          <div className="flex gap-2">
                            {transaction.status_transaksi === 'pending' && (
                              <Button 
                                variant="default" 
                                size="sm"
                                onClick={() => handleConfirmTransaction(transaction.transaction_id)}
                                disabled={processingConfirm === transaction.transaction_id}
                              >
                                {processingConfirm === transaction.transaction_id ? (
                                  <>
                                    <Clock className="h-4 w-4 mr-2 animate-spin" />
                                    Confirming...
                                  </>
                                ) : (
                                  <>
                                    <CheckCircle className="h-4 w-4 mr-2" />
                                    Confirm
                                  </>
                                )}
                              </Button>
                            )}
                            {/* <Button 
                              variant="outline" 
                              size="sm"
                              onClick={() => router.get(`/kasir/transactions/${transaction.transaction_id}`)}
                            >
                              <Eye className="h-4 w-4 mr-2" />
                              View Details
                            </Button> */}
                          </div>
                        </div>
                      </div>

                      {/* Transaction Items Preview */}
                      {transaction.detail_produk_transactions && transaction.detail_produk_transactions.length > 0 && (
                        <div className="mt-4 pt-4 border-t">
                          <h4 className="text-sm font-medium text-muted-foreground mb-2">Items Ordered:</h4>
                          <div className="flex flex-wrap gap-2">
                            {transaction.detail_produk_transactions.slice(0, 3).map((item, index) => (
                              <Badge key={index} variant="outline" className="text-xs">
                                {item.qty}x {item.produk.nama}
                              </Badge>
                            ))}
                            {transaction.detail_produk_transactions.length > 3 && (
                              <Badge variant="outline" className="text-xs">
                                +{transaction.detail_produk_transactions.length - 3} more
                              </Badge>
                            )}
                          </div>
                        </div>
                      )}
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </CardContent>
        </Card>

        {/* Pagination */}
        {transactions.last_page > 1 && (
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div className="text-sm text-muted-foreground">
                  Showing {transactions.from} to {transactions.to} of {transactions.total} transactions
                </div>
                <div className="flex items-center space-x-2">
                  {transactions.links.map((link, index) => {
                    if (link.label === '&laquo; Previous') {
                      return (
                        <Button
                          key={index}
                          variant="outline"
                          size="sm"
                          onClick={() => link.url && navigateToPage(link.url)}
                          disabled={!link.url}
                        >
                          <ChevronLeft className="h-4 w-4" />
                          Previous
                        </Button>
                      );
                    }
                    
                    if (link.label === 'Next &raquo;') {
                      return (
                        <Button
                          key={index}
                          variant="outline"
                          size="sm"
                          onClick={() => link.url && navigateToPage(link.url)}
                          disabled={!link.url}
                        >
                          Next
                          <ChevronRight className="h-4 w-4" />
                        </Button>
                      );
                    }
                    
                    // Skip ellipsis
                    if (link.label === '...') {
                      return (
                        <span key={index} className="px-3 py-2 text-sm text-muted-foreground">
                          ...
                        </span>
                      );
                    }
                    
                    // Page numbers
                    return (
                      <Button
                        key={index}
                        variant={link.active ? "default" : "outline"}
                        size="sm"
                        onClick={() => link.url && navigateToPage(link.url)}
                        disabled={!link.url}
                      >
                        {link.label}
                      </Button>
                    );
                  })}
                </div>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </KasirLayout>
  );
}