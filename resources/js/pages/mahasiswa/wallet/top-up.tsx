import { Head, Link, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Alert, AlertDescription } from '@/components/ui/alert';
import MahasiswaLayout from '@/layouts/mahasiswa/mahasiswa-layout';
import { processTopUp } from '@/routes/mahasiswa/wallet';
import { 
  Wallet, 
  CreditCard,
  DollarSign,
  ArrowLeft,
  CheckCircle,
  AlertCircle,
  Banknote,
  Smartphone,
  Building
} from 'lucide-react';
import { formatCurrency } from '@/lib/utils';
import { useState } from 'react';

interface MetodeBayar {
  metode_id: string;
  nama: string;
  type: string;
  status: string;
  keterangan?: string;
  fee_percentage?: number;
  fee_fixed?: number;
  icon?: string;
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
    balance: number;
  };
}

interface Props extends PageProps {
  mahasiswa: Mahasiswa;
  metodeBayar: MetodeBayar[];
}

export default function TopUp({ mahasiswa, metodeBayar }: Props) {
  const [selectedAmount, setSelectedAmount] = useState<number | null>(null);
  const predefinedAmounts = [10000, 25000, 50000, 100000, 200000, 500000];

  const { data, setData, post, processing, errors, reset } = useForm({
    amount: '',
    metode_id: '',
    keterangan: '',
    auto_complete: true, // For demo purposes
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(processTopUp.url(), {
      onSuccess: () => {
        reset();
      }
    });
  };

  const handleAmountSelect = (amount: number) => {
    setSelectedAmount(amount);
    setData('amount', amount.toString());
  };

  const handleCustomAmount = (value: string) => {
    const numValue = parseFloat(value);
    setSelectedAmount(numValue || null);
    setData('amount', value);
  };

  const getPaymentMethodIcon = (metodeBayar: MetodeBayar) => {
    const name = metodeBayar.nama.toLowerCase();
    if (name.includes('bank') || name.includes('transfer')) {
      return <Building className="h-5 w-5" />;
    } else if (name.includes('ewallet') || name.includes('digital')) {
      return <Smartphone className="h-5 w-5" />;
    } else {
      return <CreditCard className="h-5 w-5" />;
    }
  };

  const calculateFee = (method: MetodeBayar, amount: number) => {
    let fee = 0;
    if (method.fee_fixed) {
      fee += method.fee_fixed;
    }
    if (method.fee_percentage) {
      fee += (amount * method.fee_percentage) / 100;
    }
    return fee;
  };

  const selectedMethod = metodeBayar.find(m => m.metode_id === data.metode_id);
  const fee = selectedMethod && data.amount ? calculateFee(selectedMethod, parseFloat(data.amount)) : 0;
  const totalAmount = (parseFloat(data.amount) || 0) + fee;

  return (
    <MahasiswaLayout 
      title="Top Up Wallet - Student Portal"
      breadcrumbs={[
        { title: 'Wallet', href: '/mahasiswa/wallet' },
        { title: 'Top Up', href: '/mahasiswa/wallet/top-up' }
      ]}
    >
      <div className="flex-1 space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Link href="/mahasiswa/dashboard">
              <Button variant="outline" size="sm">
                <ArrowLeft className="h-4 w-4 mr-2" />
                Back
              </Button>
            </Link>
            <div>
              <h1 className="text-3xl font-bold tracking-tight">Top Up Wallet</h1>
              <p className="text-muted-foreground">
                Add money to your digital wallet
              </p>
            </div>
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-3">
          {/* Main Form */}
          <div className="lg:col-span-2 space-y-6">
            {/* Current Balance Card */}
            <Card>
              <CardHeader>
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-green-100 rounded-full">
                    <Wallet className="h-6 w-6 text-green-600" />
                  </div>
                  <div>
                    <CardTitle>Current Balance</CardTitle>
                    <CardDescription>Your available wallet balance</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold text-green-600">
                  {formatCurrency(mahasiswa.wallet.balance)}
                </div>
              </CardContent>
            </Card>

            {/* Top Up Form */}
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Amount Selection */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <DollarSign className="h-5 w-5" />
                    Select Amount
                  </CardTitle>
                  <CardDescription>
                    Choose a predefined amount or enter a custom amount
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  {/* Predefined Amounts */}
                  <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                    {predefinedAmounts.map((amount) => (
                      <Button
                        key={amount}
                        type="button"
                        variant={selectedAmount === amount ? "default" : "outline"}
                        className="h-16 text-sm font-medium"
                        onClick={() => handleAmountSelect(amount)}
                      >
                        <div className="text-center">
                          <div className="font-bold">
                            {formatCurrency(amount)}
                          </div>
                        </div>
                      </Button>
                    ))}
                  </div>

                  <Separator className="my-4" />

                  {/* Custom Amount */}
                  <div className="space-y-2">
                    <Label htmlFor="custom-amount">Or enter custom amount</Label>
                    <div className="relative">
                      <span className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">
                        Rp
                      </span>
                      <Input
                        id="custom-amount"
                        type="number"
                        placeholder="0"
                        className="pl-10"
                        value={data.amount}
                        onChange={(e) => handleCustomAmount(e.target.value)}
                        min="1000"
                        max="10000000"
                      />
                    </div>
                    {errors.amount && (
                      <p className="text-sm text-red-500">{errors.amount}</p>
                    )}
                  </div>
                </CardContent>
              </Card>

              {/* Payment Method Selection */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <CreditCard className="h-5 w-5" />
                    Payment Method
                  </CardTitle>
                  <CardDescription>
                    Select your preferred payment method
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <RadioGroup
                    value={data.metode_id}
                    onValueChange={(value) => setData('metode_id', value)}
                  >
                    <div className="space-y-3">
                      {metodeBayar.map((method) => (
                        <div
                          key={method.metode_id}
                          className={`flex items-center space-x-3 p-4 rounded-lg border transition-colors ${
                            data.metode_id === method.metode_id
                              ? 'border-primary bg-primary/5'
                              : 'border-border hover:bg-muted/50'
                          }`}
                        >
                          <RadioGroupItem value={method.metode_id} />
                          <div className="flex items-center gap-3 flex-1">
                            <div className="p-2 bg-muted rounded-lg">
                              {getPaymentMethodIcon(method)}
                            </div>
                            <div className="flex-1">
                              <div className="font-medium">{method.nama}</div>
                              {method.keterangan && (
                                <div className="text-sm text-muted-foreground">
                                  {method.keterangan}
                                </div>
                              )}
                            </div>
                            <div className="text-right">
                              <Badge variant="secondary" className="text-xs">
                                {method.status}
                              </Badge>
                              {(method.fee_fixed || method.fee_percentage) && (
                                <div className="text-xs text-muted-foreground mt-1">
                                  Fee applies
                                </div>
                              )}
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </RadioGroup>
                  {errors.metode_id && (
                    <p className="text-sm text-red-500 mt-2">{errors.metode_id}</p>
                  )}
                </CardContent>
              </Card>

              {/* Description */}
              <Card>
                <CardHeader>
                  <CardTitle>Description (Optional)</CardTitle>
                  <CardDescription>
                    Add a note for this top-up transaction
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <Textarea
                    placeholder="Enter description..."
                    value={data.keterangan}
                    onChange={(e) => setData('keterangan', e.target.value)}
                    rows={3}
                  />
                </CardContent>
              </Card>
            </form>
          </div>

          {/* Summary Sidebar */}
          <div className="space-y-6">
            {/* Transaction Summary */}
            <Card>
              <CardHeader>
                <CardTitle>Transaction Summary</CardTitle>
                <CardDescription>Review your top-up details</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Top-up Amount:</span>
                  <span className="font-medium">
                    {data.amount ? formatCurrency(parseFloat(data.amount)) : formatCurrency(0)}
                  </span>
                </div>
                
                {fee > 0 && (
                  <div className="flex justify-between">
                    <span className="text-muted-foreground">Transaction Fee:</span>
                    <span className="font-medium">{formatCurrency(fee)}</span>
                  </div>
                )}
                
                <Separator />
                
                <div className="flex justify-between text-lg font-semibold">
                  <span>Total to Pay:</span>
                  <span>{formatCurrency(totalAmount)}</span>
                </div>

                <div className="flex justify-between text-sm text-muted-foreground">
                  <span>New Balance:</span>
                  <span>{formatCurrency(mahasiswa.wallet.balance + (parseFloat(data.amount) || 0))}</span>
                </div>

                <Separator />

                <Button
                  type="submit"
                  onClick={handleSubmit}
                  disabled={!data.amount || !data.metode_id || processing}
                  className="w-full"
                  size="lg"
                >
                  {processing ? (
                    <>
                      <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2" />
                      Processing...
                    </>
                  ) : (
                    <>
                      <Banknote className="h-4 w-4 mr-2" />
                      Confirm Top Up

                    </>
                  )}
                </Button>
              </CardContent>
            </Card>

            {/* Security Notice */}
            <Alert>
              <CheckCircle className="h-4 w-4" />
              <AlertDescription>
                <strong>Secure Transaction</strong>
                <br />
                Your payment is protected by bank-level security. All transactions are encrypted and monitored 24/7.
              </AlertDescription>
            </Alert>

            {/* Help Information */}
            <Card>
              <CardHeader>
                <CardTitle className="text-base">Need Help?</CardTitle>
              </CardHeader>
              <CardContent className="text-sm space-y-2">
                <p className="text-muted-foreground">
                  • Minimum top-up amount: {formatCurrency(1000)}
                </p>
                <p className="text-muted-foreground">
                  • Maximum top-up amount: {formatCurrency(10000000)}
                </p>
                <p className="text-muted-foreground">
                  • Funds will be available immediately after confirmation
                </p>
                <p className="text-muted-foreground">
                  • Contact support if you encounter any issues
                </p>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </MahasiswaLayout>
  );
}