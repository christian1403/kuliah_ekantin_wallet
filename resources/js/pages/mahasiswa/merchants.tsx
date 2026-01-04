import { Head, useForm, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Alert, AlertDescription } from '@/components/ui/alert';
import MahasiswaLayout from '@/layouts/mahasiswa/mahasiswa-layout';
import { 
  Store, 
  Search, 
  ShoppingCart, 
  Plus, 
  Minus, 
  MapPin,
  Clock,
  Star,
  ArrowLeft,
  Check,
  Loader2,
  Wallet,
  AlertTriangle
} from 'lucide-react';
import { formatCurrency } from '@/lib/utils';

interface Produk {
  produk_id: string;
  nama: string;
  harga: number;
  stok: number;
  gambar?: string;
  kode_produk: string;
}

interface Merchant {
  merchant_id: string;
  nama: string;
  alamat?: string;
  no_telepon?: string;
  email?: string;
  produks?: Produk[];
}

interface CartItem extends Produk {
  quantity: number;
}

interface Props extends PageProps {
  visitedMerchants: Merchant[];
  allMerchants: Merchant[];
  currentBalance: number;
}

export default function MerchantsIndex({ 
  visitedMerchants, 
  allMerchants, 
  currentBalance 
}: Props) {
  const [selectedMerchant, setSelectedMerchant] = useState<Merchant | null>(null);
  const [products, setProducts] = useState<Produk[]>([]);
  const [cart, setCart] = useState<CartItem[]>([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [loading, setLoading] = useState(false);
  const [loadingProducts, setLoadingProducts] = useState(false);
  const [processing, setProcessing] = useState(false);
  const [showPinModal, setShowPinModal] = useState(false);
  const [pin, setPin] = useState('');
  const [pinError, setPinError] = useState('');
  const [processingPin, setProcessingPin] = useState(false);

  // Filter merchants based on search
  const filteredMerchants = allMerchants.filter(merchant =>
    merchant.nama.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Load merchant products
  const loadMerchantProducts = async (merchantId: string) => {
    setLoadingProducts(true);
    try {
      const response = await fetch(`/mahasiswa/merchants/${merchantId}/products`);
      const data = await response.json();
      setProducts(data.products || []);
      setSelectedMerchant(data.merchant);
    } catch (error) {
      console.error('Failed to load products:', error);
    } finally {
      setLoadingProducts(false);
    }
  };

  // Add product to cart
  const addToCart = (product: Produk) => {
    setCart(prevCart => {
      const existingItem = prevCart.find(item => item.produk_id === product.produk_id);
      if (existingItem) {
        if (existingItem.quantity < product.stok) {
          return prevCart.map(item =>
            item.produk_id === product.produk_id
              ? { ...item, quantity: item.quantity + 1 }
              : item
          );
        }
        return prevCart;
      } else {
        return [...prevCart, { ...product, quantity: 1 }];
      }
    });
  };

  // Update cart item quantity
  const updateCartQuantity = (produkId: string, quantity: number) => {
    if (quantity <= 0) {
      setCart(prevCart => prevCart.filter(item => item.produk_id !== produkId));
    } else {
      const product = products.find(p => p.produk_id === produkId);
      if (product && quantity <= product.stok) {
        setCart(prevCart =>
          prevCart.map(item =>
            item.produk_id === produkId
              ? { ...item, quantity }
              : item
          )
        );
      }
    }
  };

  // Calculate total
  const cartTotal = cart.reduce((total, item) => total + (item.harga * item.quantity), 0);
  const cartItemsCount = cart.reduce((total, item) => total + item.quantity, 0);

  // PIN handling functions
  const handleNumpadClick = (num: string) => {
    if (pin.length < 6) {
      setPin(prev => prev + num);
    }
  };

  const handlePinBackspace = () => {
    setPin(prev => prev.slice(0, -1));
  };

  const handleVerifyPin = () => {
    if (pin.length !== 6) return;
    
    setProcessingPin(true);
    setPinError('');
    
    router.post('/mahasiswa/wallet/verify-pin', { pin }, {
      onSuccess: () => {
        setProcessingPin(false);
        setPinError('');
        setShowPinModal(false);
        setPin('');
        // Proceed with order creation
        proceedWithOrder();
      },
      onError: (errors) => {
        console.error('PIN verification failed:', errors);
        setPinError('Invalid PIN. Please try again.');
        setPin('');
        setProcessingPin(false);
      },
      onFinish: () => {
        setProcessingPin(false);
      }
    });
  };

  const resetPinModal = () => {
    setPin('');
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

  // Create order - show PIN verification first
  const createOrder = () => {
    if (!selectedMerchant || cart.length === 0 || processing) return;
    
    // Show PIN verification modal
    setShowPinModal(true);
  };

  // Proceed with order after PIN verification
  const proceedWithOrder = () => {
    if (!selectedMerchant || cart.length === 0 || processing) return;

    const orderData = {
      merchant_id: selectedMerchant.merchant_id,
      items: cart.map(item => ({
        produk_id: item.produk_id,
        quantity: item.quantity,
      })),
      total_amount: cartTotal,
    };

    console.log('Sending order data:', orderData); // Debug log

    setProcessing(true);
    
    router.post('/mahasiswa/orders', orderData, {
      onSuccess: () => {
        console.log('Order created successfully!');
        setCart([]);
        setSelectedMerchant(null);
        setProducts([]);
        setProcessing(false);
      },
      onError: (errors) => {
        console.error('Order creation failed:', errors);
        setProcessing(false);
      },
      onFinish: () => {
        setProcessing(false);
      }
    });
  };

  // Go back to merchant list
  const goBackToMerchants = () => {
    setSelectedMerchant(null);
    setProducts([]);
    setCart([]);
  };

  if (selectedMerchant) {
    return (
      <MahasiswaLayout 
        title="Order Products - Student Portal"
        breadcrumbs={[
          { title: 'Merchants', href: '/mahasiswa/merchants' },
          { title: selectedMerchant.nama, href: '#' }
        ]}
      >
        <div className="flex-1 space-y-6 p-6">
          {/* Header */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Button 
                variant="outline" 
                size="sm"
                onClick={goBackToMerchants}
              >
                <ArrowLeft className="h-4 w-4 mr-2" />
                Back to Merchants
              </Button>
              <div>
                <h1 className="text-3xl font-bold tracking-tight">{selectedMerchant.nama}</h1>
                <p className="text-muted-foreground">Select products to add to your order</p>
              </div>
            </div>
            <div className="flex items-center gap-4">
              <div className="text-right">
                <div className="text-sm text-muted-foreground">Your Balance</div>
                <div className="text-lg font-semibold text-green-600">
                  {formatCurrency(currentBalance)}
                </div>
              </div>
              {cart.length > 0 && (
                <div className="relative">
                  <Button size="sm" className="relative">
                    <ShoppingCart className="h-4 w-4 mr-2" />
                    Cart ({cartItemsCount})
                    <Badge className="absolute -top-2 -right-2 bg-red-500">
                      {cartItemsCount}
                    </Badge>
                  </Button>
                </div>
              )}
            </div>
          </div>

          <div className="grid gap-6 lg:grid-cols-4">
            {/* Products List */}
            <div className="lg:col-span-3">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Store className="h-5 w-5" />
                    Available Products
                  </CardTitle>
                  <CardDescription>
                    Choose from our available menu items
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  {loadingProducts ? (
                    <div className="flex items-center justify-center py-12">
                      <Loader2 className="h-8 w-8 animate-spin" />
                      <span className="ml-2">Loading products...</span>
                    </div>
                  ) : products.length === 0 ? (
                    <div className="text-center py-12">
                      <Store className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                      <p className="text-muted-foreground">No products available</p>
                    </div>
                  ) : (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                      {products.map(product => (
                        <Card key={product.produk_id} className="relative">
                          <CardContent className="p-4">
                            {product.gambar && (
                              <div className="aspect-square mb-3 rounded-md overflow-hidden bg-muted">
                                <img 
                                  src={product.gambar} 
                                  alt={product.nama}
                                  className="w-full h-full object-cover"
                                />
                              </div>
                            )}
                            <div className="space-y-2">
                              <h3 className="font-medium leading-tight">{product.nama}</h3>
                              <div className="flex items-center justify-between">
                                <div className="text-lg font-semibold text-green-600">
                                  {formatCurrency(product.harga)}
                                </div>
                                <Badge variant={product.stok > 10 ? 'default' : product.stok > 0 ? 'secondary' : 'destructive'}>
                                  Stock: {product.stok}
                                </Badge>
                              </div>
                              <div className="text-sm text-muted-foreground">
                                Code: {product.kode_produk}
                              </div>
                              
                              {product.stok > 0 ? (
                                <Button 
                                  size="sm" 
                                  className="w-full"
                                  onClick={() => addToCart(product)}
                                >
                                  <Plus className="h-4 w-4 mr-2" />
                                  Add to Cart
                                </Button>
                              ) : (
                                <Button size="sm" className="w-full" disabled>
                                  Out of Stock
                                </Button>
                              )}
                            </div>
                          </CardContent>
                        </Card>
                      ))}
                    </div>
                  )}
                </CardContent>
              </Card>
            </div>

            {/* Cart Sidebar */}
            <div className="lg:col-span-1">
              <Card className="sticky top-6">
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <ShoppingCart className="h-5 w-5" />
                    Your Order
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  {cart.length === 0 ? (
                    <div className="text-center py-8">
                      <ShoppingCart className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                      <p className="text-muted-foreground">Your cart is empty</p>
                      <p className="text-sm text-muted-foreground">Add some products to get started</p>
                    </div>
                  ) : (
                    <div className="space-y-4">
                      <ScrollArea className="h-64">
                        <div className="space-y-3">
                          {cart.map(item => (
                            <div key={item.produk_id} className="flex items-center justify-between p-3 border rounded-lg">
                              <div className="flex-1 min-w-0">
                                <h4 className="text-sm font-medium truncate">{item.nama}</h4>
                                <div className="text-sm text-muted-foreground">
                                  {formatCurrency(item.harga)} each
                                </div>
                              </div>
                              <div className="flex items-center gap-2 ml-2">
                                <Button 
                                  size="sm" 
                                  variant="outline"
                                  className="h-8 w-8 p-0"
                                  onClick={() => updateCartQuantity(item.produk_id, item.quantity - 1)}
                                >
                                  <Minus className="h-3 w-3" />
                                </Button>
                                <span className="w-8 text-center text-sm">{item.quantity}</span>
                                <Button 
                                  size="sm" 
                                  variant="outline"
                                  className="h-8 w-8 p-0"
                                  onClick={() => updateCartQuantity(item.produk_id, item.quantity + 1)}
                                  disabled={item.quantity >= item.stok}
                                >
                                  <Plus className="h-3 w-3" />
                                </Button>
                              </div>
                            </div>
                          ))}
                        </div>
                      </ScrollArea>

                      <Separator />
                      
                      <div className="space-y-2">
                        <div className="flex justify-between text-sm">
                          <span>Items ({cartItemsCount})</span>
                          <span>{formatCurrency(cartTotal)}</span>
                        </div>
                        <div className="flex justify-between font-semibold">
                          <span>Total</span>
                          <span className="text-green-600">{formatCurrency(cartTotal)}</span>
                        </div>
                      </div>

                      {cartTotal > currentBalance && (
                        <div className="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                          <AlertTriangle className="h-4 w-4 text-red-500" />
                          <span className="text-sm text-red-600">Insufficient balance</span>
                        </div>
                      )}

                      <Button 
                        className="w-full" 
                        onClick={createOrder}
                        disabled={processing || cartTotal > currentBalance}
                      >
                        {processing ? (
                          <>
                            <Loader2 className="h-4 w-4 animate-spin mr-2" />
                            Processing...
                          </>
                        ) : (
                          <>
                            <Check className="h-4 w-4 mr-2" />
                            Place Order
                          </>
                        )}
                      </Button>

                      <p className="text-xs text-muted-foreground text-center">
                        Order will be pending until confirmed by merchant
                      </p>
                    </div>
                  )}
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
        
        {/* PIN Verification Modal */}
        <Dialog open={showPinModal} onOpenChange={resetPinModal}>
          <DialogContent className="sm:max-w-md">
            <DialogHeader>
              <DialogTitle>Verify Your PIN</DialogTitle>
              <DialogDescription>
                Enter your 6-digit wallet PIN to confirm this order
              </DialogDescription>
            </DialogHeader>
            
            <div className="space-y-4">
              {/* Order Summary */}
              <div className="bg-muted p-3 rounded-lg">
                <div className="text-sm font-medium mb-1">Order Summary</div>
                <div className="text-xs text-muted-foreground">
                  {cartItemsCount} items • Total: {formatCurrency(cartTotal)}
                </div>
              </div>

              {/* Error Alert */}
              {pinError && (
                <Alert variant="destructive">
                  <AlertDescription>{pinError}</AlertDescription>
                </Alert>
              )}

              {/* PIN Display */}
              <div className="flex justify-center space-x-2">
                {Array.from({ length: 6 }, (_, i) => {
                  const filled = i < pin.length;
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
                <Button 
                  className="flex-1" 
                  onClick={handleVerifyPin}
                  disabled={pin.length !== 6 || processingPin}
                >
                  {processingPin ? 'Verifying...' : 'Verify & Order'}
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </MahasiswaLayout>
    );
  }

  return (
    <MahasiswaLayout 
      title="Merchants - Student Portal"
      breadcrumbs={[
        { title: 'Merchants', href: '/mahasiswa/merchants' }
      ]}
    >
      <div className="flex-1 space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Merchants</h1>
            <p className="text-muted-foreground">
              Browse and order from available merchants
            </p>
          </div>
          <div className="text-right">
            <div className="text-sm text-muted-foreground">Your Balance</div>
            <div className="text-xl font-semibold text-green-600 flex items-center gap-2">
              <Wallet className="h-5 w-5" />
              {formatCurrency(currentBalance)}
            </div>
          </div>
        </div>

        {/* Search */}
        <Card>
          <CardContent className="pt-6">
            <div className="relative">
              <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Search merchants..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
          </CardContent>
        </Card>

        {/* Recently Visited Merchants */}
        {visitedMerchants.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Clock className="h-5 w-5" />
                Recently Visited
              </CardTitle>
              <CardDescription>
                Merchants you've ordered from before
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                {visitedMerchants.map(merchant => (
                  <Card 
                    key={merchant.merchant_id} 
                    className="cursor-pointer hover:shadow-md transition-shadow"
                    onClick={() => loadMerchantProducts(merchant.merchant_id)}
                  >
                    <CardContent className="p-4">
                      <div className="flex items-start gap-3">
                        <Avatar className="h-12 w-12">
                          <AvatarFallback>
                            {merchant.nama.charAt(0)}
                          </AvatarFallback>
                        </Avatar>
                        <div className="flex-1 min-w-0">
                          <h3 className="font-semibold truncate">{merchant.nama}</h3>
                          <p className="text-sm text-muted-foreground truncate">
                            {merchant.alamat || 'No address provided'}
                          </p>
                          <div className="flex items-center gap-2 mt-2">
                            <Badge variant="secondary" className="text-xs">
                              <Star className="h-3 w-3 mr-1" />
                              Previously visited
                            </Badge>
                          </div>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            </CardContent>
          </Card>
        )}

        {/* All Merchants */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Store className="h-5 w-5" />
              All Merchants
            </CardTitle>
            <CardDescription>
              Browse all available merchants ({filteredMerchants.length} found)
            </CardDescription>
          </CardHeader>
          <CardContent>
            {filteredMerchants.length === 0 ? (
              <div className="text-center py-12">
                <Store className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <p className="text-muted-foreground">No merchants found</p>
                <p className="text-sm text-muted-foreground">Try adjusting your search terms</p>
              </div>
            ) : (
              <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                {filteredMerchants.map(merchant => (
                  <Card 
                    key={merchant.merchant_id} 
                    className="cursor-pointer hover:shadow-md transition-shadow"
                    onClick={() => loadMerchantProducts(merchant.merchant_id)}
                  >
                    <CardContent className="p-4">
                      <div className="flex items-start gap-3">
                        <Avatar className="h-12 w-12">
                          <AvatarFallback>
                            {merchant.nama.charAt(0)}
                          </AvatarFallback>
                        </Avatar>
                        <div className="flex-1 min-w-0">
                          <h3 className="font-semibold truncate">{merchant.nama}</h3>
                          <p className="text-sm text-muted-foreground truncate">
                            {merchant.alamat || 'No address provided'}
                          </p>
                          {merchant.produks && (
                            <div className="flex items-center gap-2 mt-2">
                              <Badge variant="outline" className="text-xs">
                                {merchant.produks.length} products
                              </Badge>
                              <Badge variant="outline" className="text-xs">
                                <MapPin className="h-3 w-3 mr-1" />
                                Available
                              </Badge>
                            </div>
                          )}
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </MahasiswaLayout>
  );
}