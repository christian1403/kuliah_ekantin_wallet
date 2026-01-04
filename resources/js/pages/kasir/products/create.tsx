import { Head, Link, router, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import { FormEventHandler } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

import { Alert, AlertDescription } from '@/components/ui/alert';
import KasirLayout from '@/layouts/kasir/kasir-layout';
import { 
  ArrowLeft,
  Package,
  DollarSign,
  Hash,
  Image,
  Tag,
  FileText,
  Save,
  AlertTriangle,
  CheckCircle
} from 'lucide-react';

interface Merchant {
  merchant_id: string;
  nama: string;
  alamat?: string;
  no_telepon?: string;
  email?: string;
}

interface Props extends PageProps {
  merchant: Merchant;
}

export default function CreateProduct({ merchant }: Props) {
  const { data, setData, post, processing, errors, reset } = useForm({
    kode_produk: '',
    nama: '',
    harga: '',
    stok: '',
    gambar: '',
    kategori: '',
    deskripsi: ''
  });



  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    router.post('/kasir/products', data, {
      onSuccess: () => {
        reset();
      },
      onError: (errors) => {
        console.error('Failed to create product:', errors);
      }
    });
  };

  const generateProductCode = () => {
    const prefix = 'PRD';
    const randomNum = Math.floor(Math.random() * 9999) + 1;
    const paddedNum = randomNum.toString().padStart(4, '0');
    setData('kode_produk', `${prefix}${paddedNum}`);
  };

  const validateImageUrl = (url: string) => {
    if (!url) return true;
    try {
      new URL(url);
      return /\.(jpg|jpeg|png|gif|webp)$/i.test(url);
    } catch {
      return false;
    }
  };

  const formatCurrency = (value: string) => {
    // Remove non-numeric characters
    const numericValue = value.replace(/[^0-9]/g, '');
    // Format with thousand separators
    return numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  };

  const handlePriceChange = (value: string) => {
    const formatted = formatCurrency(value);
    setData('harga', formatted);
  };

  const getPriceValue = () => {
    return data.harga.replace(/,/g, '');
  };

  return (
    <KasirLayout 
      title="Create Product - Kasir Portal"
      breadcrumbs={[
        { title: 'Products', href: '/kasir/products' },
        { title: 'Create Product', href: '/kasir/products/create' }
      ]}
    >
      <div className="flex-1 space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Create New Product</h1>
            <p className="text-muted-foreground">
              Add a new product to {merchant.nama}'s inventory
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Link href="/kasir/products">
              <Button variant="outline" size="sm">
                <ArrowLeft className="h-4 w-4 mr-2" />
                Back to Products
              </Button>
            </Link>
          </div>
        </div>

        {/* Form */}
        <div className="grid gap-6 lg:grid-cols-3">
          <div className="lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Package className="h-5 w-5" />
                  Product Information
                </CardTitle>
                <CardDescription>
                  Fill in the details for your new product
                </CardDescription>
              </CardHeader>
              <CardContent>
                <form onSubmit={submit} className="space-y-6">
                  {/* Product Code */}
                  <div className="space-y-2">
                    <Label htmlFor="kode_produk" className="flex items-center gap-2">
                      <Hash className="h-4 w-4" />
                      Product Code
                    </Label>
                    <div className="flex gap-2">
                      <Input
                        id="kode_produk"
                        type="text"
                        value={data.kode_produk}
                        onChange={(e) => setData('kode_produk', e.target.value)}
                        placeholder="PRD0001"
                        className={errors.kode_produk ? 'border-red-500' : ''}
                      />
                      <Button
                        type="button"
                        variant="outline"
                        onClick={generateProductCode}
                      >
                        Generate
                      </Button>
                    </div>
                    {errors.kode_produk && (
                      <Alert variant="destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>{errors.kode_produk}</AlertDescription>
                      </Alert>
                    )}
                  </div>

                  {/* Product Name */}
                  <div className="space-y-2">
                    <Label htmlFor="nama" className="flex items-center gap-2">
                      <Package className="h-4 w-4" />
                      Product Name *
                    </Label>
                    <Input
                      id="nama"
                      type="text"
                      value={data.nama}
                      onChange={(e) => setData('nama', e.target.value)}
                      placeholder="Enter product name"
                      className={errors.nama ? 'border-red-500' : ''}
                      required
                    />
                    {errors.nama && (
                      <Alert variant="destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>{errors.nama}</AlertDescription>
                      </Alert>
                    )}
                  </div>

                  {/* Price and Stock */}
                  <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                      <Label htmlFor="harga" className="flex items-center gap-2">
                        <DollarSign className="h-4 w-4" />
                        Price (Rp) *
                      </Label>
                      <div className="relative">
                        <span className="absolute left-3 top-3 text-sm text-muted-foreground">Rp</span>
                        <Input
                          id="harga"
                          type="text"
                          value={data.harga}
                          onChange={(e) => handlePriceChange(e.target.value)}
                          placeholder="0"
                          className={`pl-8 ${errors.harga ? 'border-red-500' : ''}`}
                          required
                        />
                      </div>
                      {errors.harga && (
                        <Alert variant="destructive">
                          <AlertTriangle className="h-4 w-4" />
                          <AlertDescription>{errors.harga}</AlertDescription>
                        </Alert>
                      )}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="stok" className="flex items-center gap-2">
                        <Package className="h-4 w-4" />
                        Stock Quantity *
                      </Label>
                      <Input
                        id="stok"
                        type="number"
                        min="0"
                        value={data.stok}
                        onChange={(e) => setData('stok', e.target.value)}
                        placeholder="0"
                        className={errors.stok ? 'border-red-500' : ''}
                        required
                      />
                      {errors.stok && (
                        <Alert variant="destructive">
                          <AlertTriangle className="h-4 w-4" />
                          <AlertDescription>{errors.stok}</AlertDescription>
                        </Alert>
                      )}
                    </div>
                  </div>

                  {/* Category */}
                  <div className="space-y-2">
                    <Label htmlFor="kategori" className="flex items-center gap-2">
                      <Tag className="h-4 w-4" />
                      Category *
                    </Label>
                    <Input
                      id="kategori"
                      type="text"
                      value={data.kategori}
                      onChange={(e) => setData('kategori', e.target.value)}
                      placeholder="Enter product category (e.g., Food, Beverage, Snack)"
                      className={errors.kategori ? 'border-red-500' : ''}
                      required
                    />
                    {errors.kategori && (
                      <Alert variant="destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>{errors.kategori}</AlertDescription>
                      </Alert>
                    )}
                  </div>

                  {/* Product Image */}
                  <div className="space-y-2">
                    <Label htmlFor="gambar" className="flex items-center gap-2">
                      <Image className="h-4 w-4" />
                      Product Image URL
                    </Label>
                    <Input
                      id="gambar"
                      type="url"
                      value={data.gambar}
                      onChange={(e) => setData('gambar', e.target.value)}
                      placeholder="https://example.com/image.jpg"
                      className={errors.gambar ? 'border-red-500' : ''}
                    />
                    {data.gambar && !validateImageUrl(data.gambar) && (
                      <Alert variant="destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>Please enter a valid image URL (jpg, jpeg, png, gif, webp)</AlertDescription>
                      </Alert>
                    )}
                    {errors.gambar && (
                      <Alert variant="destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>{errors.gambar}</AlertDescription>
                      </Alert>
                    )}
                  </div>

                  {/* Description */}
                  <div className="space-y-2">
                    <Label htmlFor="deskripsi" className="flex items-center gap-2">
                      <FileText className="h-4 w-4" />
                      Description
                    </Label>
                    <Textarea
                      id="deskripsi"
                      value={data.deskripsi}
                      onChange={(e) => setData('deskripsi', e.target.value)}
                      placeholder="Enter product description (optional)"
                      rows={4}
                      className={errors.deskripsi ? 'border-red-500' : ''}
                    />
                    {errors.deskripsi && (
                      <Alert variant="destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>{errors.deskripsi}</AlertDescription>
                      </Alert>
                    )}
                  </div>

                  {/* Submit Button */}
                  <div className="flex items-center gap-4 pt-4">
                    <Button type="submit" disabled={processing}>
                      {processing ? (
                        <>
                          <Package className="h-4 w-4 mr-2 animate-spin" />
                          Creating...
                        </>
                      ) : (
                        <>
                          <Save className="h-4 w-4 mr-2" />
                          Create Product
                        </>
                      )}
                    </Button>
                    <Button type="button" variant="outline" onClick={() => reset()}>
                      Reset Form
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          </div>

          {/* Preview Sidebar */}
          <div className="lg:col-span-1">
            <Card className="sticky top-6">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <CheckCircle className="h-5 w-5" />
                  Product Preview
                </CardTitle>
                <CardDescription>
                  See how your product will appear
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {/* Preview Image */}
                  {data.gambar && validateImageUrl(data.gambar) ? (
                    <div className="aspect-square rounded-md overflow-hidden bg-muted">
                      <img 
                        src={data.gambar} 
                        alt={data.nama || 'Product preview'}
                        className="w-full h-full object-cover"
                        onError={(e) => {
                          e.currentTarget.style.display = 'none';
                        }}
                      />
                    </div>
                  ) : (
                    <div className="aspect-square rounded-md bg-muted flex items-center justify-center">
                      <Image className="h-12 w-12 text-muted-foreground" />
                    </div>
                  )}

                  {/* Preview Info */}
                  <div className="space-y-2">
                    <h3 className="font-semibold text-lg">
                      {data.nama || 'Product Name'}
                    </h3>
                    
                    <div className="flex items-center gap-2">
                      <Badge variant="outline">
                        {data.kode_produk || 'PRODUCT_CODE'}
                      </Badge>
                      {data.kategori && (
                        <Badge variant="secondary">
                          {data.kategori}
                        </Badge>
                      )}
                    </div>

                    <div className="text-2xl font-bold text-green-600">
                      Rp {data.harga || '0'}
                    </div>

                    <div className="flex items-center justify-between text-sm">
                      <span className="text-muted-foreground">Stock:</span>
                      <span className="font-medium">{data.stok || '0'} items</span>
                    </div>

                    {data.deskripsi && (
                      <div className="text-sm text-muted-foreground">
                        <p className="line-clamp-3">{data.deskripsi}</p>
                      </div>
                    )}
                  </div>

                  {/* Merchant Info */}
                  <div className="pt-4 border-t">
                    <div className="text-sm text-muted-foreground">
                      <div className="flex items-center gap-2 mb-2">
                        <Package className="h-4 w-4" />
                        <span className="font-medium">Merchant</span>
                      </div>
                      <p>{merchant.nama}</p>
                      {merchant.alamat && (
                        <p className="text-xs">{merchant.alamat}</p>
                      )}
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </KasirLayout>
  );
}