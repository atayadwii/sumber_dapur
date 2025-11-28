# PAYMENT VALIDATION SYSTEM - API DOCUMENTATION

## Overview
Sistem validasi pembayaran dengan flow lengkap: pembeli upload bukti pembayaran dalam 1 jam → penjual konfirmasi → pembeli submit rating & review setelah terima barang.

---

## STATUS FLOW DIAGRAM (SIMPLIFIED - NO DEADLINE)

```
CHECKOUT
   ↓
menunggu_pembayaran (no time limit)
   ↓
   └─→ [Upload payment proof] → menunggu_konfirmasi
                                    ↓
                              ├─→ [Seller accepts] → proses → selesai (after review)
                              └─→ [Seller rejects] → batal
```

---

## DATABASE CHANGES

### Migration: `add_payment_validation_to_pesanan_table`

**New Columns:**
- `bukti_pembayaran` (string, nullable) - Path to payment proof image
- `payment_deadline` (timestamp, nullable) - 1 hour from order creation
- `is_paid` (boolean, default: false) - Payment status flag
- `rating` (decimal 2,1, nullable) - Customer rating (1-5)
- `review` (text, nullable) - Customer review text
- `review_images` (json, nullable) - Array of review image paths
- `paid_at` (timestamp, nullable) - Payment confirmation time
- `completed_at` (timestamp, nullable) - Order completion time
- `rejection_reason` (text, nullable) - Reason if payment rejected

**Status Values:**
- `menunggu_pembayaran` - Waiting for payment proof (1 hour)
- `menunggu_konfirmasi` - Waiting for seller confirmation
- `proses` - Payment accepted, order processing
- `selesai` - Order completed (with rating & review)
- `batal` - Order cancelled (payment rejected)
- `kadaluarsa` - Order expired (payment not uploaded)

---

## API ENDPOINTS

### 1. CREATE ORDER (Updated)
**Endpoint:** `POST /api/pesanan`

**Changes:**
- Status: `pending` → `menunggu_pembayaran`
- Added: `payment_deadline` (1 hour from now)

**Response:**
```json
{
  "success": true,
  "message": "Pesanan berhasil dibuat. Silakan upload bukti pembayaran dalam 1 jam.",
  "pesanan_id": 1,
  "payment_deadline": "2025-11-28T03:00:00.000000Z"
}
```

---

### 2. UPLOAD PAYMENT PROOF (Pembeli)
**Endpoint:** `POST /api/pesanan/{id}/upload-payment`

**Authorization:** Bearer Token (Pembeli only)

**Request:**
- Content-Type: `multipart/form-data`
- Body:
  ```
  bukti_pembayaran: [file] (required, image, jpeg/png/jpg, max 5MB)
  ```

**Validation:**
- ✅ Only buyer of the order can upload
- ✅ Must be within payment deadline (not expired)
- ✅ Status must be `menunggu_pembayaran`
- ✅ Image validation (format, size)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Bukti pembayaran berhasil diupload. Menunggu konfirmasi penjual.",
  "pesanan": {
    "id": 1,
    "status_pesanan": "menunggu_konfirmasi",
    "bukti_pembayaran": "payment_proofs/1732756800_1.jpg",
    "bukti_pembayaran_url": "http://localhost:8000/storage/payment_proofs/1732756800_1.jpg",
    "is_expired": false,
    "remaining_time": "45:23",
    "pembeli": {...},
    "penjual": {...}
  }
}
```

**Error Responses:**
- `403`: Not your order
- `400`: Order expired or invalid status
- `422`: Validation failed (file format, size)

---

### 3. CONFIRM PAYMENT (Penjual)
**Endpoint:** `POST /api/pesanan/{id}/confirm-payment`

**Authorization:** Bearer Token (Penjual only)

**Request:**
- Content-Type: `application/json`
- Body (Accept):
  ```json
  {
    "is_accepted": true
  }
  ```
- Body (Reject):
  ```json
  {
    "is_accepted": false,
    "rejection_reason": "Bukti pembayaran tidak sesuai / tidak jelas"
  }
  ```

**Validation:**
- ✅ Only seller of the order can confirm
- ✅ Status must be `menunggu_konfirmasi`
- ✅ `rejection_reason` required if rejected (max 500 chars)

**Success Response - Accepted (200):**
```json
{
  "success": true,
  "message": "Pembayaran diterima. Pesanan sedang diproses.",
  "pesanan": {
    "id": 1,
    "status_pesanan": "proses",
    "is_paid": true,
    "paid_at": "2025-11-28T02:30:00.000000Z",
    "pembeli": {...},
    "penjual": {...}
  }
}
```

**Success Response - Rejected (200):**
```json
{
  "success": true,
  "message": "Pembayaran ditolak. Pesanan dibatalkan.",
  "pesanan": {
    "id": 1,
    "status_pesanan": "batal",
    "is_paid": false,
    "rejection_reason": "Bukti pembayaran tidak sesuai",
    "pembeli": {...},
    "penjual": {...}
  }
}
```

**Error Responses:**
- `403`: Not your order
- `400`: Invalid status for confirmation
- `422`: Validation failed

---

### 4. SUBMIT RATING & REVIEW (Pembeli)
**Endpoint:** `POST /api/pesanan/{id}/review`

**Authorization:** Bearer Token (Pembeli only)

**Request:**
- Content-Type: `multipart/form-data`
- Body:
  ```
  rating: 5 (required, numeric, 1-5)
  review: "Produk sangat bagus, pengiriman cepat!" (required, string, min:10, max:1000)
  review_images[]: [file1] (optional, image, jpeg/png/jpg, max 5MB)
  review_images[]: [file2] (optional, image, jpeg/png/jpg, max 5MB)
  review_images[]: [file3] (optional, image, jpeg/png/jpg, max 5MB)
  (max 5 images total)
  ```

**Validation:**
- ✅ Only buyer of the order can submit
- ✅ Status must be `proses` or `selesai`
- ✅ Rating: 1-5
- ✅ Review: 10-1000 characters
- ✅ Max 5 review images (each max 5MB)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Terima kasih! Pesanan telah diselesaikan.",
  "pesanan": {
    "id": 1,
    "status_pesanan": "selesai",
    "rating": 5,
    "review": "Produk sangat bagus, pengiriman cepat!",
    "review_images": [
      "review_images/1732757000_1_0.jpg",
      "review_images/1732757000_1_1.jpg"
    ],
    "review_images_urls": [
      "http://localhost:8000/storage/review_images/1732757000_1_0.jpg",
      "http://localhost:8000/storage/review_images/1732757000_1_1.jpg"
    ],
    "completed_at": "2025-11-28T03:00:00.000000Z",
    "pembeli": {...},
    "penjual": {...}
  }
}
```

**Error Responses:**
- `403`: Not your order
- `400`: Order not ready for completion
- `422`: Validation failed

---

## MODEL ACCESSORS

### Pesanan Model - New Computed Attributes

```php
// Check if payment deadline expired
$pesanan->is_expired // boolean

// Remaining time until deadline (MM:SS format)
$pesanan->remaining_time // "45:23" or "00:00"

// Full URL for payment proof image
$pesanan->bukti_pembayaran_url // "http://localhost:8000/storage/payment_proofs/..."

// Full URL for delivery proof image
$pesanan->bukti_penerimaan_url // "http://localhost:8000/storage/bukti_pesanan/..."

// Array of full URLs for review images
$pesanan->review_images_urls // ["http://localhost:8000/storage/review_images/..."]
```

---

## SCHEDULED JOB - Auto-Expire Orders

**Command:** `orders:check-expired`

**Schedule:** Every minute

**Function:** Automatically marks orders as `kadaluarsa` if:
- Status is `menunggu_pembayaran`
- `payment_deadline` has passed

**Run Manually:**
```bash
php artisan orders:check-expired
```

**Run Scheduler (Development):**
```bash
php artisan schedule:work
```

**Production Setup (Crontab):**
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## STORAGE DIRECTORIES

Files are stored in `storage/app/public/`:
- `payment_proofs/` - Payment proof images from buyers
- `bukti_pesanan/` - Delivery proof images (legacy)
- `review_images/` - Review product images from buyers

**Accessible URLs:**
- `http://localhost:8000/storage/payment_proofs/{filename}`
- `http://localhost:8000/storage/review_images/{filename}`

**Storage Link:**
```bash
php artisan storage:link
```

---

## TESTING WITH POSTMAN/CURL

### 1. Upload Payment Proof
```bash
POST http://localhost:8000/api/pesanan/1/upload-payment
Headers:
  Authorization: Bearer {buyer_token}
  Content-Type: multipart/form-data
Body:
  bukti_pembayaran: [select image file]
```

### 2. Confirm Payment (Accept)
```bash
POST http://localhost:8000/api/pesanan/1/confirm-payment
Headers:
  Authorization: Bearer {seller_token}
  Content-Type: application/json
Body:
{
  "is_accepted": true
}
```

### 3. Confirm Payment (Reject)
```bash
POST http://localhost:8000/api/pesanan/1/confirm-payment
Headers:
  Authorization: Bearer {seller_token}
  Content-Type: application/json
Body:
{
  "is_accepted": false,
  "rejection_reason": "Bukti pembayaran tidak jelas"
}
```

### 4. Submit Review
```bash
POST http://localhost:8000/api/pesanan/1/review
Headers:
  Authorization: Bearer {buyer_token}
  Content-Type: multipart/form-data
Body:
  rating: 5
  review: "Produk sangat bagus, pengiriman cepat dan sesuai ekspektasi!"
  review_images[]: [image1.jpg]
  review_images[]: [image2.jpg]
```

---

## FRONTEND INTEGRATION NOTES

### Status Mapping for UI
```dart
// Status display
Map<String, String> statusLabels = {
  'menunggu_pembayaran': 'Menunggu Pembayaran',
  'menunggu_konfirmasi': 'Menunggu Konfirmasi',
  'proses': 'Diproses',
  'selesai': 'Selesai',
  'batal': 'Dibatalkan',
  'kadaluarsa': 'Kadaluarsa',
};

// Status colors
Map<String, Color> statusColors = {
  'menunggu_pembayaran': Colors.orange,
  'menunggu_konfirmasi': Colors.blue,
  'proses': Colors.green,
  'selesai': Colors.teal,
  'batal': Colors.red,
  'kadaluarsa': Colors.grey,
};
```

### Countdown Timer (Flutter)
```dart
// Use remaining_time from API response
Timer.periodic(Duration(seconds: 1), (timer) {
  // Refresh order data to get updated remaining_time
  // Display in UI: "Bayar dalam: 45:23"
});
```

### Image Upload
```dart
// Use image_picker package
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;

// Upload payment proof
Future<void> uploadPaymentProof(int orderId, File image) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('$baseUrl/pesanan/$orderId/upload-payment'),
  );
  request.headers['Authorization'] = 'Bearer $token';
  request.files.add(await http.MultipartFile.fromPath(
    'bukti_pembayaran',
    image.path,
  ));
  
  var response = await request.send();
  // Handle response...
}
```

---

## SECURITY FEATURES

✅ **Authorization Checks:**
- Upload payment: Only buyer
- Confirm payment: Only seller
- Submit review: Only buyer

✅ **Validation:**
- File size limits (5MB)
- Image format validation (jpeg, png, jpg)
- Status flow enforcement
- Deadline expiration checks

✅ **Data Integrity:**
- Old payment proof deleted when new one uploaded
- Transaction rollback on errors
- Proper error logging

---

## ERROR CODES SUMMARY

| Code | Meaning | Common Causes |
|------|---------|---------------|
| 200 | Success | Operation completed |
| 403 | Forbidden | Not authorized for this order |
| 404 | Not Found | Order ID doesn't exist |
| 400 | Bad Request | Invalid status, expired, etc. |
| 422 | Validation Failed | Invalid input data |
| 500 | Server Error | Internal error (check logs) |

---

## NEXT STEPS

1. ✅ Migration applied
2. ✅ Model updated with accessors
3. ✅ Controllers implemented
4. ✅ Routes registered
5. ✅ Scheduled job created
6. ✅ Storage linked

**Ready for Production!** 🚀

---

## Support & Troubleshooting

**Check Logs:**
```bash
tail -f storage/logs/laravel.log
```

**Test Commands:**
```bash
# Test expired orders check
php artisan orders:check-expired

# List all routes
php artisan route:list --path=pesanan

# Clear cache
php artisan cache:clear
php artisan config:clear
```
