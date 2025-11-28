# Test Pesanan API Endpoints

## Prerequisites
1. Server Laravel harus running: `php artisan serve`
2. Database sudah termigrasi
3. User penjual dan pembeli sudah terdaftar
4. Pesanan sudah dibuat

## Endpoints yang Sudah Diimplementasikan

### 1. Update Status Pesanan (untuk Penjual)
**Endpoint:** `PUT /api/pesanan/{id}/status`

**Headers:**
```
Authorization: Bearer {token_penjual}
Content-Type: application/json
```

**Body:**
```json
{
  "status": "proses"
}
```

**Valid Status:**
- `pending`
- `proses`
- `selesai`
- `batal`

**Response Success (200):**
```json
{
  "message": "Status pesanan berhasil diupdate",
  "pesanan": {
    "id": 1,
    "user_pembeli_id": 2,
    "user_penjual_id": 1,
    "total_harga": 50000,
    "status_pesanan": "proses",
    "bukti_penerimaan": null,
    "alamat_pengiriman": null,
    "catatan": null,
    "pembeli": {...},
    "penjual": {...},
    "detail": [...]
  }
}
```

**Response Error (403):**
```json
{
  "message": "Anda tidak memiliki akses untuk mengupdate pesanan ini"
}
```

**Response Error (404):**
```json
{
  "message": "Pesanan tidak ditemukan"
}
```

### 2. Complete Order dengan Upload Bukti (untuk Pembeli)
**Endpoint:** `POST /api/pesanan/{id}/complete`

**Headers:**
```
Authorization: Bearer {token_pembeli}
Content-Type: multipart/form-data
```

**Body (Form Data):**
```
bukti_penerimaan: [file gambar jpeg/png/jpg/gif, max 2MB]
```

**Kondisi:**
- Pesanan harus berstatus `proses`
- User harus pembeli dari pesanan tersebut

**Response Success (200):**
```json
{
  "message": "Pesanan berhasil diselesaikan",
  "pesanan": {
    "id": 1,
    "user_pembeli_id": 2,
    "user_penjual_id": 1,
    "total_harga": 50000,
    "status_pesanan": "selesai",
    "bukti_penerimaan": "bukti_pesanan/1732694123_bukti.jpg",
    "alamat_pengiriman": null,
    "catatan": null,
    "pembeli": {...},
    "penjual": {...},
    "detail": [...]
  }
}
```

**Response Error (403):**
```json
{
  "message": "Anda tidak memiliki akses untuk menyelesaikan pesanan ini"
}
```

**Response Error (400):**
```json
{
  "message": "Pesanan harus berstatus proses untuk dapat diselesaikan"
}
```

**Response Error (422):**
```json
{
  "message": "Validasi gagal",
  "errors": {
    "bukti_penerimaan": ["The bukti penerimaan field is required."]
  }
}
```

## Testing dengan cURL (PowerShell)

### Test Update Status
```powershell
$token = "your_seller_token_here"
$pesananId = 1

Invoke-WebRequest -Uri "http://localhost:8000/api/pesanan/$pesananId/status" `
  -Method PUT `
  -Headers @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
  } `
  -Body '{"status":"proses"}' | Select-Object -Expand Content
```

### Test Complete Order
```powershell
$token = "your_buyer_token_here"
$pesananId = 1
$imagePath = "C:\path\to\bukti.jpg"

$form = @{
    bukti_penerimaan = Get-Item -Path $imagePath
}

Invoke-WebRequest -Uri "http://localhost:8000/api/pesanan/$pesananId/complete" `
  -Method POST `
  -Headers @{
    "Authorization" = "Bearer $token"
  } `
  -Form $form | Select-Object -Expand Content
```

## File Upload Info
- Files disimpan di: `storage/app/public/bukti_pesanan/`
- Accessible via: `http://localhost:8000/storage/bukti_pesanan/{filename}`
- Format nama file: `{timestamp}_{original_filename}`

## Security Features
1. Authorization check: Hanya penjual yang bisa update status pesanannya
2. Authorization check: Hanya pembeli yang bisa complete pesanannya
3. Status validation: Pesanan harus 'diproses' sebelum bisa diselesaikan
4. File validation: Max 2MB, hanya image (jpeg, png, jpg, gif)
5. Sanctum authentication untuk semua endpoints

## Database Changes
Migration `add_additional_columns_to_pesanans_table` menambahkan:
- `bukti_penerimaan` (string, nullable)
- `alamat_pengiriman` (text, nullable)
- `catatan` (text, nullable)

## Model Changes
`Pesanan.php` fillable ditambahkan:
- `bukti_penerimaan`
- `alamat_pengiriman`
- `catatan`
