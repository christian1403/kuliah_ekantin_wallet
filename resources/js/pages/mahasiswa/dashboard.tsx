import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Alert, AlertDescription } from '@/components/ui/alert';
import MahasiswaLayout from '@/layouts/mahasiswa/mahasiswa-layout';
import { 
  Wallet, 
  TrendingUp, 
  TrendingDown, 
  ArrowUpRight, 
  ArrowDownRight,
  CreditCard,
  Store,
  Calendar,
  PieChart,
  BarChart3,
  QrCode,
  Plus,
  ArrowRightLeft,
  Eye,
  Clock,
  DollarSign,
  ShoppingBag,
  Lock
} from 'lucide-react';
import { formatCurrency } from '@/lib/utils';

interface DashboardStats {
  current_balance: number;
  total_transactions_today: number;
  total_spent_today: number;
  total_spent_month: number;
}

interface DailySpending {
  date: string;
  amount: number;
}

interface CategorySpending {
  [key: string]: number;
}

interface RecentTransaction {
  id: string;
  invoice: string;
  tipe_transaksi: 'credit' | 'debit';
  amount: number;
  deskripsi: string;
  status_transaksi: 'pending' | 'completed' | 'failed';
  created_at: string;
  detail_pengeluaran?: {
    kasir: {
      merchant: {
        nama: string;
      };
    };
    merchant?: {
      nama: string;
    };
  };
  detail_pemasukan?: {
    keterangan: string;
  };
}

interface Mahasiswa {
  id: string;
  npm: string;
  nama: string;
  jurusan: string;
  angkatan: number;
  user: {
    name: string;
    email: string;
  };
  wallet: {
    saldo: number;
  };
}

interface Props extends PageProps {
  mahasiswa: Mahasiswa;
  stats: DashboardStats;
  recentTransactions: RecentTransaction[];
  dailySpending: DailySpending[];
  categorySpending: CategorySpending;
  hasWallet: boolean;
}

export default function MahasiswaDashboard({ 
  mahasiswa, 
  stats, 
  recentTransactions, 
  dailySpending, 
  categorySpending,
  hasWallet
}: Props) {
  const [showPinModal, setShowPinModal] = useState(false);
  const [oldPin, setOldPin] = useState('');
  const [pin, setPin] = useState('');
  const [confirmPin, setConfirmPin] = useState('');
  const [pinStep, setPinStep] = useState<'verify' | 'enter' | 'confirm'>('verify');
  const [processingPin, setProcessingPin] = useState(false);
  const [pinError, setPinError] = useState('');

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
    if (transaction.tipe_transaksi === 'credit') {
      return <ArrowDownRight className="h-4 w-4 text-green-500" />;
    }
    return <ArrowUpRight className="h-4 w-4 text-red-500" />;
  };

  const getTransactionColor = (transaction: RecentTransaction) => {
    if (transaction.tipe_transaksi === 'credit') {
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
    
    return <Badge variant={variants[status] || "outline"}>{status}</Badge>;
  };

  // Calculate spending trend
  const yesterdaySpending = dailySpending.length >= 2 ? dailySpending[dailySpending.length - 2]?.amount || 0 : 0;
  const todaySpending = stats.total_spent_today;
  const spendingTrend = todaySpending - yesterdaySpending;
  const spendingTrendPercentage = yesterdaySpending > 0 ? ((spendingTrend / yesterdaySpending) * 100) : 0;

  // Prepare category data for display
  const categoryData = Object.entries(categorySpending).map(([category, amount]) => ({
    category,
    amount,
    percentage: (amount / Object.values(categorySpending).reduce((a, b) => a + b, 0)) * 100
  })).sort((a, b) => b.amount - a.amount);

  const maxDailySpending = Math.max(...dailySpending.map(d => d.amount));

  const handleCreateWallet = () => {
    router.post('/mahasiswa/wallet/create', {}, {
      onSuccess: () => {
        // Success message will be handled by the backend
      },
      onError: (errors) => {
        console.error('Failed to create wallet:', errors);
      }
    });
  };

  // PIN handling functions
  const handleNumpadClick = (num: string) => {
    if (pinStep === 'verify') {
      if (oldPin.length < 6) {
        setOldPin(prev => prev + num);
      }
    } else if (pinStep === 'enter') {
      if (pin.length < 6) {
        setPin(prev => prev + num);
      }
    } else {
      if (confirmPin.length < 6) {
        setConfirmPin(prev => prev + num);
      }
    }
  };

  const handlePinBackspace = () => {
    if (pinStep === 'verify') {
      setOldPin(prev => prev.slice(0, -1));
    } else if (pinStep === 'enter') {
      setPin(prev => prev.slice(0, -1));
    } else {
      setConfirmPin(prev => prev.slice(0, -1));
    }
  };

  const handlePinNext = () => {
    if (pin.length === 6) {
      setPinStep('confirm');
    }
  };

  const handleVerifyPin = () => {
    if (oldPin.length !== 6) return;
    
    setProcessingPin(true);
    setPinError('');
    
    router.post('/mahasiswa/wallet/verify-pin', { pin: oldPin }, {
      onSuccess: () => {
        setPinStep('enter');
        setProcessingPin(false);
        setPinError('');
      },
      onError: (errors) => {
        console.error('PIN verification failed:', errors);
        setPinError('Invalid PIN. Please try again.');
        setOldPin('');
        setProcessingPin(false);
      },
      onFinish: () => {
        setProcessingPin(false);
      }
    });
  };

  const handlePinSave = () => {
    if (pin !== confirmPin) {
      setPinError('PIN confirmation does not match!');
      return;
    }

    setProcessingPin(true);
    setPinError('');
    
    router.post('/mahasiswa/wallet/update-pin', { 
      old_pin: oldPin,
      new_pin: pin 
    }, {
      onSuccess: () => {
        setShowPinModal(false);
        setOldPin('');
        setPin('');
        setConfirmPin('');
        setPinStep('verify');
        setProcessingPin(false);
        setPinError('');
      },
      onError: (errors) => {
        console.error('Failed to update PIN:', errors);
        setPinError('Failed to update PIN. Please try again.');
        setProcessingPin(false);
      },
      onFinish: () => {
        setProcessingPin(false);
      }
    });
  };

  const resetPinModal = () => {
    setOldPin('');
    setPin('');
    setConfirmPin('');
    setPinStep('verify');
    setPinError('');
    setShowPinModal(false);
  };

  const renderNumpad = () => {
    const numbers = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '', '0', '⌫'];
    
    return (
      <div className="grid grid-cols-3 gap-3 mt-4">
        {numbers.map((num, index) => (
          <Button
            key={index}
            variant={num === '' ? 'ghost' : 'outline'}
            className="h-12 text-lg font-semibold"
            disabled={num === '' || processingPin}
            onClick={() => {
              if (num === '⌫') {
                handlePinBackspace();
              } else if (num !== '') {
                handleNumpadClick(num);
              }
            }}
          >
            {num}
          </Button>
        ))}
      </div>
    );
  };

  // If user doesn't have a wallet, show wallet creation UI
  if (!hasWallet) {
    return (
      <MahasiswaLayout 
        title="Dashboard - Student Portal"
        breadcrumbs={[{ label: 'Dashboard', href: '/mahasiswa/dashboard' }]}
      >
        <div className="flex-1 space-y-6 p-6">
          {/* Header */}
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
              <p className="text-muted-foreground">
                Welcome back, {mahasiswa.nama}
              </p>
            </div>
          </div>

          {/* Student Info Card */}
          <Card>
            <CardHeader>
              <div className="flex items-center space-x-4">
                <Avatar className="h-16 w-16">
                  <AvatarFallback className="text-lg font-semibold">
                    {getInitials(mahasiswa.nama)}
                  </AvatarFallback>
                </Avatar>
                <div>
                  <CardTitle className="text-xl">{mahasiswa.nama}</CardTitle>
                  <CardDescription className="text-base">
                    {mahasiswa.npm}
                  </CardDescription>
                  <div className="flex items-center gap-2 mt-2">
                    <Badge variant="secondary">{mahasiswa.user.email.substring(0, 20)}...</Badge>
                  </div>
                </div>
              </div>
            </CardHeader>
          </Card>

          {/* Create Wallet Card */}
          <Card className="border-dashed border-2">
            <CardHeader className="text-center pb-4">
              <div className="mx-auto mb-4 w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center">
                <Wallet className="h-10 w-10 text-primary" />
              </div>
              <CardTitle className="text-2xl">Create Your Digital Wallet</CardTitle>
              <CardDescription className="text-base max-w-md mx-auto">
                You don't have a digital wallet yet. Create one now to start managing your transactions, 
                top up your balance, and make payments at campus merchants.
              </CardDescription>
            </CardHeader>
            <CardContent className="text-center pb-8">
              <Button 
                onClick={handleCreateWallet}
                size="lg" 
                className="px-8"
              >
                <Plus className="h-5 w-5 mr-2" />
                Create Wallet Now
              </Button>
              <div className="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-muted-foreground">
                <div className="flex items-center gap-2 justify-center">
                  <DollarSign className="h-4 w-4" />
                  <span>Secure Transactions</span>
                </div>
                <div className="flex items-center gap-2 justify-center">
                  <CreditCard className="h-4 w-4" />
                  <span>Easy Top-ups</span>
                </div>
                <div className="flex items-center gap-2 justify-center">
                  <ShoppingBag className="h-4 w-4" />
                  <span>Campus Payments</span>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Getting Started Guide */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Calendar className="h-5 w-5" />
                Getting Started
              </CardTitle>
              <CardDescription>
                Follow these simple steps to start using your digital wallet
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="flex items-start gap-3">
                  <div className="w-6 h-6 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm font-bold">1</div>
                  <div>
                    <h4 className="font-medium">Create Your Wallet</h4>
                    <p className="text-sm text-muted-foreground">Click the button above to create your secure digital wallet</p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <div className="w-6 h-6 bg-muted text-muted-foreground rounded-full flex items-center justify-center text-sm font-bold">2</div>
                  <div>
                    <h4 className="font-medium text-muted-foreground">Top Up Your Balance</h4>
                    <p className="text-sm text-muted-foreground">Add money to your wallet using various payment methods</p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <div className="w-6 h-6 bg-muted text-muted-foreground rounded-full flex items-center justify-center text-sm font-bold">3</div>
                  <div>
                    <h4 className="font-medium text-muted-foreground">Start Spending</h4>
                    <p className="text-sm text-muted-foreground">Use your wallet at campus merchants and track your spending</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </MahasiswaLayout>
    );
  }

  return (
    <MahasiswaLayout 
      title="Dashboard - Student Portal"
      breadcrumbs={[{ title: 'Dashboard', href: '/mahasiswa/dashboard' }]}
    >
      <div className="flex-1 space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
            <p className="text-muted-foreground">
              Welcome back, {mahasiswa.nama}
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Link href="/mahasiswa/wallet/top-up">
              <Button size="sm">
                <Plus className="h-4 w-4 mr-2" />
                Top Up
              </Button>
            </Link>
            <Button 
              size="sm" 
              variant="outline"
              onClick={() => setShowPinModal(true)}
            >
              <Lock className="h-4 w-4 mr-2" />
              Edit PIN
            </Button>
          </div>
        </div>

        {/* Student Info Card */}
        <Card>
          <CardHeader>
            <div className="flex items-center space-x-4">
              <Avatar className="h-16 w-16">
                <AvatarFallback className="text-lg font-semibold">
                  {getInitials(mahasiswa.nama)}
                </AvatarFallback>
              </Avatar>
              <div>
                <CardTitle className="text-xl">{mahasiswa.nama}</CardTitle>
                <CardDescription className="text-base">
                  {mahasiswa.npm}
                   {/* • {mahasiswa.jurusan} • Class of {mahasiswa.angkatan} */}
                </CardDescription>
                <div className="flex items-center gap-2 mt-2">
                  <Badge variant="secondary">{mahasiswa.user.email.substring(0, 20)}...</Badge>
                </div>
              </div>
            </div>
          </CardHeader>
        </Card>

        {/* Stats Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Current Balance</CardTitle>
              <Wallet className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {formatCurrency(stats.current_balance)}
              </div>
              <p className="text-xs text-muted-foreground">
                Available in your wallet
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Today's Spending</CardTitle>
              <TrendingDown className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {formatCurrency(stats?.total_spent_today ?? 0)}
              </div>
              <div className="flex items-center text-xs">
                {spendingTrend >= 0 ? (
                  <TrendingUp className="h-3 w-3 text-red-500 mr-1" />
                ) : (
                  <TrendingDown className="h-3 w-3 text-green-500 mr-1" />
                )}
                <span className={spendingTrend >= 0 ? "text-red-500" : "text-green-500"}>
                  {Math.abs(spendingTrendPercentage).toFixed(1)}% from yesterday
                </span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Transactions Today</CardTitle>
              <CreditCard className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {stats.total_transactions_today}
              </div>
              <p className="text-xs text-muted-foreground">
                Transactions completed
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Monthly Spending</CardTitle>
              <Calendar className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {formatCurrency(stats?.total_spent_month ?? 0)}
              </div>
              <p className="text-xs text-muted-foreground">
                Total this month
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-6 md:grid-cols-2">
          {/* Daily Spending Chart */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <BarChart3 className="h-5 w-5" />
                Daily Spending (Last 7 Days)
              </CardTitle>
              <CardDescription>
                Your spending pattern over the past week
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {dailySpending.map((day, index) => (
                  <div key={index} className="flex items-center justify-between">
                    <div className="text-sm font-medium">{day.date}</div>
                    <div className="flex items-center gap-3">
                      <div className="w-24 bg-muted rounded-full h-2">
                        <div 
                          className="bg-primary h-2 rounded-full" 
                          style={{ 
                            width: maxDailySpending > 0 ? `${(day.amount / maxDailySpending) * 100}%` : '0%' 
                          }}
                        />
                      </div>
                      <div className="text-sm text-muted-foreground w-20 text-right">
                        {formatCurrency(day.amount)}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Category Spending */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <PieChart className="h-5 w-5" />
                Spending by Category
              </CardTitle>
              <CardDescription>
                This month's spending breakdown
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {categoryData.map((item, index) => (
                  <div key={index} className="flex items-center justify-between">
                    <div className="text-sm font-medium">{item.category}</div>
                    <div className="flex items-center gap-3">
                      <div className="w-24 bg-muted rounded-full h-2">
                        <div 
                          className="bg-primary h-2 rounded-full" 
                          style={{ width: `${item.percentage}%` }}
                        />
                      </div>
                      <div className="text-sm text-muted-foreground w-20 text-right">
                        {formatCurrency(item.amount)}
                      </div>
                    </div>
                  </div>
                ))}
                {categoryData.length === 0 && (
                  <p className="text-center text-muted-foreground py-4">
                    No spending data available
                  </p>
                )}
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
                Your latest wallet activities
              </CardDescription>
            </div>
            <Link href="/mahasiswa/transactions">
              <Button variant="outline" size="sm">
                <Eye className="h-4 w-4 mr-2" />
                View All
              </Button>
            </Link>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {recentTransactions.map((transaction) => (
                <div key={transaction.id} className="flex items-center justify-between py-2">
                  <div className="flex items-center space-x-4">
                    <div className="p-2 bg-muted rounded-full">
                      {getTransactionIcon(transaction)}
                    </div>
                    <div>
                      <div className="text-sm font-medium">
                        {transaction.invoice}
                      </div>
                      <div className="text-xs text-muted-foreground">
                        {new Date(transaction.created_at).toLocaleDateString('en-US', {
                          month: 'short',
                          day: 'numeric',
                          hour: '2-digit',
                          minute: '2-digit'
                        })} - {transaction.detail_pengeluaran ? 
                          transaction.detail_pengeluaran?.merchant.nama :
                          (transaction.detail_pemasukan?.keterangan || transaction.deskripsi)
                        }
                      </div>
                    </div>
                  </div>
                  <div className="text-right">
                    <div className={`text-sm font-medium ${getTransactionColor(transaction)}`}>
                      {transaction.tipe_transaksi === 'credit' ? '+' : '-'}{formatCurrency(transaction.amount)}
                    </div>
                    <div className="flex justify-end">
                      {getStatusBadge(transaction.status_transaksi)}
                    </div>
                  </div>
                </div>
              ))}
              {recentTransactions.length === 0 && (
                <div className="text-center py-8">
                  <DollarSign className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground">No transactions yet</p>
                  <p className="text-sm text-muted-foreground">Your wallet activities will appear here</p>
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
              Manage your wallet with quick actions
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-2">
              <Link href="/mahasiswa/wallet/top-up">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <Plus className="h-5 w-5" />
                  Top Up Wallet
                </Button>
              </Link>
              {/* <Link href="/mahasiswa/wallet/transfer">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <ArrowRightLeft className="h-5 w-5" />
                  Transfer Money
                </Button>
              </Link> */}
              {/* <Link href="/mahasiswa/qr-code">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <QrCode className="h-5 w-5" />
                  Show QR Code
                </Button>
              </Link> */}
              <Link href="/mahasiswa/merchants">
                <Button className="h-16 w-full flex-col gap-2" variant="outline">
                  <ShoppingBag className="h-5 w-5" />
                  Browse Merchants
                </Button>
              </Link>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* PIN Update Modal */}
      <Dialog open={showPinModal} onOpenChange={resetPinModal}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>
              {pinStep === 'verify' ? 'Verify Current PIN' : 
               pinStep === 'enter' ? 'Set New PIN' : 'Confirm Your PIN'}
            </DialogTitle>
            <DialogDescription>
              {pinStep === 'verify' 
                ? 'Enter your current 6-digit PIN to proceed'
                : pinStep === 'enter' 
                ? 'Enter your new 6-digit PIN for wallet security'
                : 'Please confirm your new PIN by entering it again'
              }
            </DialogDescription>
          </DialogHeader>
          
          <div className="space-y-4">
            {/* Error Alert */}
            {pinError && (
              <Alert variant="destructive">
                <AlertDescription>{pinError}</AlertDescription>
              </Alert>
            )}

            {/* PIN Display */}
            <div className="flex justify-center space-x-2">
              {Array.from({ length: 6 }, (_, i) => {
                const currentPin = pinStep === 'verify' ? oldPin : 
                                 pinStep === 'enter' ? pin : confirmPin;
                const filled = i < currentPin.length;
                return (
                  <div
                    key={i}
                    className={`w-4 h-4 rounded-full border-2 transition-colors ${
                      filled ? 'bg-primary border-primary' : 'border-muted-foreground'
                    }`}
                  />
                );
              })}
            </div>

            {/* Numpad */}
            {renderNumpad()}

            {/* Action Buttons */}
            <div className="flex gap-2 pt-4">
              <Button 
                variant="outline" 
                className="flex-1" 
                onClick={resetPinModal}
                disabled={processingPin}
              >
                Cancel
              </Button>
              
              {pinStep === 'verify' ? (
                <Button 
                  className="flex-1" 
                  onClick={handleVerifyPin}
                  disabled={oldPin.length !== 6 || processingPin}
                >
                  {processingPin ? 'Verifying...' : 'Verify PIN'}
                </Button>
              ) : pinStep === 'enter' ? (
                <Button 
                  className="flex-1" 
                  onClick={handlePinNext}
                  disabled={pin.length !== 6 || processingPin}
                >
                  Next
                </Button>
              ) : (
                <Button 
                  className="flex-1" 
                  onClick={handlePinSave}
                  disabled={confirmPin.length !== 6 || pin !== confirmPin || processingPin}
                >
                  {processingPin ? 'Saving...' : 'Save PIN'}
                </Button>
              )}
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </MahasiswaLayout>
  );
}