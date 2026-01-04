import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import KasirLayout from '@/layouts/kasir/kasir-layout';
import { 
  Search, 
  Filter,
  Plus,
  Eye,
  Edit,
  Trash2,
  Package,
  DollarSign,
  AlertTriangle,
  CheckCircle,
  Clock,
  ChevronLeft,
  ChevronRight,
  Store,
  Tag,
  BarChart3,
  TrendingUp,
  TrendingDown
} from 'lucide-react';
import { formatCurrency } from '@/lib/utils';

interface Product {
  produk_id: string;
  nama: string;
  deskripsi: string;
  harga: number;
  kategori: string;
  stok: number;
  kode_produk: string;
  gambar?: string;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

interface Merchant {
  merchant_id: string;
  nama: string;
  alamat?: string;
  no_telepon?: string;
  email?: string;
}

interface PaginationLinks {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginatedProducts {
  data: Product[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  links: PaginationLinks[];
}

interface Props extends PageProps {
  produk: PaginatedProducts;
  merchant: Merchant;
  categories: string[];
  filters: {
    search?: string;
    kategori?: string;
    status?: string;
    stock?: string;
    sort_price?: string;
  };
}

export default function ProductsIndex({ produk, merchant, categories, filters }: Props) {
  const [searchTerm, setSearchTerm] = useState(filters.search || '');
  const [categoryFilter, setCategoryFilter] = useState(filters.kategori || 'all');
  const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
  const [stockFilter, setStockFilter] = useState(filters.stock || 'all');
  const [priceSort, setPriceSort] = useState(filters.sort_price || '');

  const getStatusBadge = (status: string) => {
    const variants: { [key: string]: "default" | "secondary" | "destructive" | "outline" } = {
      active: "default",
      inactive: "secondary"
    };
    
    const colors: { [key: string]: string } = {
      active: "bg-green-100 text-green-800 hover:bg-green-100",
      inactive: "bg-gray-100 text-gray-800 hover:bg-gray-100"
    };
    
    return (
      <Badge variant={variants[status] || "outline"} className={colors[status]}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  const getStockBadge = (stock: number) => {
    if (stock === 0) {
      return (
        <Badge variant="destructive" className="bg-red-100 text-red-800 hover:bg-red-100">
          Out of Stock
        </Badge>
      );
    } else if (stock <= 10) {
      return (
        <Badge variant="secondary" className="bg-yellow-100 text-yellow-800 hover:bg-yellow-100">
          Low Stock ({stock})
        </Badge>
      );
    } else {
      return (
        <Badge variant="default" className="bg-green-100 text-green-800 hover:bg-green-100">
          In Stock ({stock})
        </Badge>
      );
    }
  };

  const getStockIcon = (stock: number) => {
    if (stock === 0) {
      return <AlertTriangle className="h-4 w-4 text-red-500" />;
    } else if (stock <= 10) {
      return <Clock className="h-4 w-4 text-yellow-500" />;
    } else {
      return <CheckCircle className="h-4 w-4 text-green-500" />;
    }
  };

  const applyFilters = (newFilters: { 
    search: string; 
    kategori: string; 
    // status: string; 
    stock: string; 
    sort_price: string 
  }) => {
    const params = new URLSearchParams();
    
    if (newFilters.search) params.append('search', newFilters.search);
    if (newFilters.kategori && newFilters.kategori !== 'all') params.append('kategori', newFilters.kategori);
    if (newFilters.status && newFilters.status !== 'all') params.append('status', newFilters.status);
    if (newFilters.stock && newFilters.stock !== 'all') params.append('stock', newFilters.stock);
    if (newFilters.sort_price) params.append('sort_price', newFilters.sort_price);
    
    const queryString = params.toString();
    const url = queryString ? `/kasir/products?${queryString}` : '/kasir/products';
    
    router.get(url, {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleSearch = (value: string) => {
    setSearchTerm(value);
    applyFilters({ 
      search: value, 
      kategori: categoryFilter, 
    //   status: statusFilter, 
      stock: stockFilter, 
      sort_price: priceSort 
    });
  };

  const handleCategoryFilter = (value: string) => {
    setCategoryFilter(value);
    applyFilters({ 
      search: searchTerm, 
      kategori: value, 
    //   status: statusFilter, 
      stock: stockFilter, 
      sort_price: priceSort 
    });
  };

  const handleStatusFilter = (value: string) => {
    setStatusFilter(value);
    applyFilters({ 
      search: searchTerm, 
      kategori: categoryFilter, 
    //   status: value, 
      stock: stockFilter, 
      sort_price: priceSort 
    });
  };

  const handleStockFilter = (value: string) => {
    setStockFilter(value);
    applyFilters({ 
      search: searchTerm, 
      kategori: categoryFilter, 
    //   status: statusFilter, 
      stock: value, 
      sort_price: priceSort 
    });
  };

  const handlePriceSort = (value: string) => {
    setPriceSort(value);
    applyFilters({ 
      search: searchTerm, 
      kategori: categoryFilter, 
    //   status: statusFilter, 
      stock: stockFilter, 
      sort_price: value 
    });
  };

  const clearFilters = () => {
    setSearchTerm('');
    setCategoryFilter('all');
    setStatusFilter('all');
    setStockFilter('all');
    setPriceSort('');
    router.get('/kasir/products');
  };

  const navigateToPage = (url: string) => {
    router.get(url);
  };

  // Calculate statistics
  const totalProducts = produk.total;
  const activeProducts = produk.data.filter(p => p.status === 'active').length;
  const outOfStock = produk.data.filter(p => p.stok === 0).length;
  const lowStock = produk.data.filter(p => p.stok > 0 && p.stok <= 10).length;
  const totalValue = produk.data.reduce((sum, p) => sum + (p.harga * p.stok), 0);

  return (
    <KasirLayout 
      title="Products - Kasir Portal"
      breadcrumbs={[
        { title: 'Products', href: '/kasir/products' }
      ]}
    >
      <div className="flex-1 space-y-6 p-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Product Management</h1>
            <p className="text-muted-foreground">
              Manage products for {merchant.nama} ({totalProducts} total products)
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Link href="/kasir/products/create">
              <Button>
                <Plus className="h-4 w-4 mr-2" />
                Add Product
              </Button>
            </Link>
          </div>
        </div>

        {/* Statistics Cards */}
        <div className="grid gap-4 md:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Products</CardTitle>
              <Package className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{totalProducts}</div>
              <p className="text-xs text-muted-foreground">
                All products
              </p>
            </CardContent>
          </Card>
          {/* <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Active</CardTitle>
              <CheckCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{activeProducts}</div>
              <p className="text-xs text-muted-foreground">
                On current page
              </p>
            </CardContent>
          </Card> */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Low Stock</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-yellow-600">{lowStock}</div>
              <p className="text-xs text-muted-foreground">
                Need restocking
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Out of Stock</CardTitle>
              <AlertTriangle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">{outOfStock}</div>
              <p className="text-xs text-muted-foreground">
                Unavailable
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Inventory Value</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">
                {formatCurrency(totalValue)}
              </div>
              <p className="text-xs text-muted-foreground">
                Current page total
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Filter className="h-5 w-5" />
              Filter Products
            </CardTitle>
            <CardDescription>
              Search and filter products by various criteria
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-5">
              <div className="relative">
                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search products..."
                  value={searchTerm}
                  onChange={(e) => handleSearch(e.target.value)}
                  className="pl-10"
                />
              </div>
              <Select value={categoryFilter} onValueChange={handleCategoryFilter}>
                <SelectTrigger>
                  <SelectValue placeholder="Category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Categories</SelectItem>
                  {categories.map((category) => (
                    <SelectItem key={category} value={category}>
                      {category}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              {/* <Select value={statusFilter} onValueChange={handleStatusFilter}>
                <SelectTrigger>
                  <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="active">Active</SelectItem>
                  <SelectItem value="inactive">Inactive</SelectItem>
                </SelectContent>
              </Select> */}
              <Select value={stockFilter} onValueChange={handleStockFilter}>
                <SelectTrigger>
                  <SelectValue placeholder="Stock Level" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Stock</SelectItem>
                  <SelectItem value="in_stock">In Stock</SelectItem>
                  <SelectItem value="low_stock">Low Stock</SelectItem>
                  <SelectItem value="out_of_stock">Out of Stock</SelectItem>
                </SelectContent>
              </Select>
              <Select value={priceSort} onValueChange={handlePriceSort}>
                <SelectTrigger>
                  <SelectValue placeholder="Sort by Price" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Default</SelectItem>
                  <SelectItem value="low_to_high">Price: Low to High</SelectItem>
                  <SelectItem value="high_to_low">Price: High to Low</SelectItem>
                </SelectContent>
              </Select>
              <Button variant="outline" onClick={clearFilters}>
                Clear Filters
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Products List */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Package className="h-5 w-5" />
              Products
            </CardTitle>
            <CardDescription>
              Showing {produk.from} to {produk.to} of {produk.total} products
            </CardDescription>
          </CardHeader>
          <CardContent>
            {produk.data.length === 0 ? (
              <div className="text-center py-12">
                <Package className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <p className="text-muted-foreground">No products found</p>
                <p className="text-sm text-muted-foreground">Try adjusting your search or filter criteria</p>
              </div>
            ) : (
              <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                {produk.data.map((product) => (
                    <Card key={product.produk_id} className="hover:shadow-md transition-shadow">
                    <CardContent className="p-4">
                      {/* Product Image */}
                      {product.gambar ? (
                        <div className="aspect-square mb-3 rounded-md overflow-hidden bg-muted">
                          <img 
                            src={`${product.gambar}`} 
                            alt={product.nama}
                            className="w-full h-full object-cover"
                          />
                        </div>
                      ) : (
                        <div className="aspect-square mb-3 rounded-md bg-muted flex items-center justify-center">
                          <Package className="h-12 w-12 text-muted-foreground" />
                          {product.gambar}
                        </div>
                      )}

                      {/* Product Info */}
                      <div className="space-y-2">
                        <div className="flex items-start justify-between">
                          <h3 className="font-semibold text-lg leading-tight">{product.nama}</h3>
                          {getStockIcon(product.stok)}
                        </div>
                        
                        {/* <div className="text-sm text-muted-foreground">
                          <div className="truncate" title={product.deskripsi}>
                            {product.deskripsi || 'No description available'}
                          </div>
                        </div> */}

                        <div className="flex items-center gap-2">
                          <Badge variant="outline" className="text-xs">
                            <Tag className="h-3 w-3 mr-1" />
                            {product.kategori}
                          </Badge>
                          <Badge variant="outline" className="text-xs">
                            {product.kode_produk}
                          </Badge>
                        </div>

                        <div className="flex items-center justify-between">
                          <div className="text-xl font-bold text-green-600">
                            {formatCurrency(product.harga)}
                          </div>
                          {getStockBadge(product.stok)}
                        </div>

                        <div className="flex items-center justify-between pt-2">
                          {getStatusBadge('active')}
                          <div className="flex gap-1">
                            {/* <Button 
                              variant="outline" 
                              size="sm"
                              onClick={() => router.get(`/kasir/products/${product.produk_id}`)}
                            >
                              <Eye className="h-3 w-3" />
                            </Button> */}
                            <Button 
                              variant="outline" 
                              size="sm"
                              onClick={() => router.get(`/kasir/products/${product.produk_id}/edit`)}
                            >
                              <Edit className="h-3 w-3" />
                            </Button>
                          </div>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </CardContent>
        </Card>

        {/* Pagination */}
        {produk.last_page > 1 && (
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div className="text-sm text-muted-foreground">
                  Showing {produk.from} to {produk.to} of {produk.total} products
                </div>
                <div className="flex items-center space-x-2">
                  {produk.links.map((link, index) => {
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
                    
                    if (link.label === '...') {
                      return (
                        <span key={index} className="px-3 py-2 text-sm text-muted-foreground">
                          ...
                        </span>
                      );
                    }
                    
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