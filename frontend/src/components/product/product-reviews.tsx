
// src/components/product/product-reviews.tsx
'use client';

import { useState } from 'react';
import { Star, ThumbsUp, MessageCircle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

interface ProductReviewsProps {
  productId: number;
}

// Mock data - replace with actual API calls
const mockReviews = [
  {
    id: 1,
    user_name: 'Ahmad S.',
    rating: 5,
    comment: 'Produk sangat bagus, sesuai dengan deskripsi. Pengiriman cepat!',
    created_at: '2024-01-15',
    helpful_count: 12,
    images: [],
  },
  {
    id: 2,
    user_name: 'Sari W.',
    rating: 4,
    comment: 'Kualitas ok, tapi packaging bisa diperbaiki.',
    created_at: '2024-01-10',
    helpful_count: 5,
    images: [],
  },
];

export function ProductReviews({ productId }: ProductReviewsProps) {
  const [reviews] = useState(mockReviews);
  const [sortBy, setSortBy] = useState('newest');

  const averageRating = 4.2;
  const totalReviews = 247;

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center justify-between">
            <span>Ulasan Pelanggan</span>
            <Button variant="outline" size="sm">
              Tulis Ulasan
            </Button>
          </CardTitle>
        </CardHeader>
        <CardContent>
          {/* Rating Summary */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div className="text-center">
              <div className="text-4xl font-bold mb-2">{averageRating}</div>
              <div className="flex items-center justify-center space-x-1 mb-2">
                {[...Array(5)].map((_, i) => (
                  <Star
                    key={i}
                    className={`h-5 w-5 ${
                      i < Math.floor(averageRating)
                        ? 'fill-yellow-400 text-yellow-400'
                        : 'text-gray-300'
                    }`}
                  />
                ))}
              </div>
              <div className="text-sm text-gray-600">
                Dari {totalReviews} ulasan
              </div>
            </div>
            
            <div className="space-y-2">
              {[5, 4, 3, 2, 1].map((rating) => (
                <div key={rating} className="flex items-center space-x-2">
                  <span className="text-sm w-8">{rating}</span>
                  <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                  <div className="flex-1 bg-gray-200 rounded-full h-2">
                    <div 
                      className="bg-yellow-400 h-2 rounded-full" 
                      style={{ width: `${rating === 5 ? 60 : rating === 4 ? 25 : 10}%` }}
                    ></div>
                  </div>
                  <span className="text-sm text-gray-600 w-12">
                    {rating === 5 ? '60%' : rating === 4 ? '25%' : '10%'}
                  </span>
                </div>
              ))}
            </div>
          </div>

          {/* Sort Options */}
          <div className="flex items-center space-x-2 mb-4">
            <span className="text-sm text-gray-600">Urutkan:</span>
            <select
              value={sortBy}
              onChange={(e) => setSortBy(e.target.value)}
              className="text-sm border border-gray-300 rounded px-2 py-1"
            >
              <option value="newest">Terbaru</option>
              <option value="oldest">Terlama</option>
              <option value="highest">Rating Tertinggi</option>
              <option value="lowest">Rating Terendah</option>
            </select>
          </div>

          {/* Reviews List */}
          <div className="space-y-4">
            {reviews.map((review) => (
              <div key={review.id} className="border-b border-gray-200 pb-4 last:border-b-0">
                <div className="flex items-start justify-between mb-2">
                  <div>
                    <div className="flex items-center space-x-2 mb-1">
                      <span className="font-medium">{review.user_name}</span>
                      <Badge variant="outline" className="text-xs">
                        Verified Purchase
                      </Badge>
                    </div>
                    <div className="flex items-center space-x-1">
                      {[...Array(5)].map((_, i) => (
                        <Star
                          key={i}
                          className={`h-4 w-4 ${
                            i < review.rating
                              ? 'fill-yellow-400 text-yellow-400'
                              : 'text-gray-300'
                          }`}
                        />
                      ))}
                    </div>
                  </div>
                  <span className="text-sm text-gray-500">
                    {new Date(review.created_at).toLocaleDateString('id-ID')}
                  </span>
                </div>
                
                <p className="text-gray-700 mb-3">{review.comment}</p>
                
                <div className="flex items-center space-x-4">
                  <button className="flex items-center space-x-1 text-sm text-gray-500 hover:text-gray-700">
                    <ThumbsUp className="h-4 w-4" />
                    <span>Membantu ({review.helpful_count})</span>
                  </button>
                  <button className="flex items-center space-x-1 text-sm text-gray-500 hover:text-gray-700">
                    <MessageCircle className="h-4 w-4" />
                    <span>Balas</span>
                  </button>
                </div>
              </div>
            ))}
          </div>

          {/* Load More */}
          <div className="text-center mt-6">
            <Button variant="outline">
              Lihat Lebih Banyak Ulasan
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}