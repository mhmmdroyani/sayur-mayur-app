<?php 
session_start();
include 'config/functions.php';
include 'includes/header.php'; 

// Generate CSRF token
$csrfToken = generateCSRFToken();
?>

<div class="container py-5">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
          <h4 class="mb-0"><i class="bi bi-bag-check"></i> Checkout</h4>
        </div>
        <div class="card-body p-4">
          
          <!-- Cart Items rendered by DOMContentLoaded script below -->
          <div id="cartItemsWrapper" class="mb-4">
            <h5 class="mb-3">Ringkasan Pesanan</h5>
            <div id="checkoutCartItems" style="min-height: 50px; background: #f9fafb; padding: 15px; border-radius: 8px;">
              <div style="text-align: center; color: #9ca3af;">
                <p style="margin: 0;">Memuat ringkasan pesanan...</p>
              </div>
            </div>
          </div>

          <!-- Checkout Form -->
          <form id="checkoutForm">
            <input type="hidden" id="csrf_token" value="<?= $csrfToken; ?>">
            
            <h5 class="mb-3">Informasi Pembeli</h5>
            
            <div class="mb-3">
              <label class="form-label fw-bold">
                Nama Lengkap <span class="text-danger">*</span>
              </label>
              <input type="text" id="nama_pembeli" class="form-control form-control-lg" 
                     placeholder="Masukkan nama lengkap Anda" required minlength="3">
              <div class="invalid-feedback">Nama harus minimal 3 karakter</div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">
                Nomor Telepon <span class="text-danger">*</span>
              </label>
              <input type="tel" id="no_telp" class="form-control form-control-lg" 
                     placeholder="08xxxxxxxxxx" pattern="[0-9]{10,13}" required>
              <div class="invalid-feedback">Nomor telepon harus 10-13 digit</div>
            </div>

            <div class="mb-4">
              <label class="form-label fw-bold">
                Alamat Pengiriman <span class="text-danger">*</span>
              </label>
              <textarea id="alamat" class="form-control" rows="3" 
                        placeholder="Masukkan alamat lengkap" required minlength="10"></textarea>
              <div class="invalid-feedback">Alamat harus minimal 10 karakter</div>
            </div>

            <div class="mb-4">
              <label class="form-label fw-bold">
                Lokasi Pengiriman <span class="text-danger">*</span>
              </label>
              <select id="lokasi_kirim" class="form-control form-control-lg" required onchange="updateShippingCost()">
                <option value="">-- Pilih Lokasi Pengiriman --</option>
              </select>
              <small class="text-muted" id="estimasiHari"></small>
              <div class="invalid-feedback">Lokasi pengiriman harus dipilih</div>
            </div>

            <!-- Payment Method -->
            <div class="mb-4">
              <label class="form-label fw-bold">
                Metode Pembayaran <span class="text-danger">*</span>
              </label>
              <div class="payment-methods">
                <div class="form-check payment-option">
                  <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="COD" checked>
                  <label class="form-check-label" for="payment_cod">
                    <div class="payment-label">
                      <i class="bi bi-cash-coin text-success fs-4"></i>
                      <div>
                        <strong>COD (Cash on Delivery)</strong>
                        <small class="d-block text-muted">Bayar saat barang diterima</small>
                      </div>
                    </div>
                  </label>
                </div>
                <div class="form-check payment-option">
                  <input class="form-check-input" type="radio" name="payment_method" id="payment_transfer" value="Transfer Bank">
                  <label class="form-check-label" for="payment_transfer">
                    <div class="payment-label">
                      <i class="bi bi-bank text-primary fs-4"></i>
                      <div>
                        <strong>Transfer Bank</strong>
                        <small class="d-block text-muted">BCA, BNI, Mandiri, BRI</small>
                      </div>
                    </div>
                  </label>
                </div>
                <div class="form-check payment-option">
                  <input class="form-check-input" type="radio" name="payment_method" id="payment_ewallet" value="E-Wallet">
                  <label class="form-check-label" for="payment_ewallet">
                    <div class="payment-label">
                      <i class="bi bi-wallet2 text-warning fs-4"></i>
                      <div>
                        <strong>E-Wallet</strong>
                        <small class="d-block text-muted">GoPay, OVO, Dana, ShopeePay</small>
                      </div>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4">
              <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">
                  <i class="bi bi-receipt text-success"></i> Ringkasan Pesanan
                </h6>
              </div>
              <div class="card-body">
                <!-- Voucher Input -->
                <div class="voucher-section p-3 mb-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 12px; border: 2px dashed #22c55e;">
                  <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-ticket-perforated-fill text-success fs-5 me-2"></i>
                    <label class="form-label fw-bold mb-0">Punya Kode Voucher?</label>
                  </div>
                  <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0">
                      <i class="bi bi-tag text-success"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" id="voucherCode" 
                           placeholder="MASUKKAN KODE VOUCHER" 
                           style="text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">
                    <button class="btn btn-success px-4" type="button" id="applyVoucher">
                      <i class="bi bi-check-circle"></i> Gunakan
                    </button>
                  </div>
                  <div id="voucherMessage" class="mt-2"></div>
                  <div id="voucherInfo" class="alert alert-success mt-3 mb-0" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span id="voucherName" class="fw-bold"></span>
                      </div>
                      <button type="button" class="btn btn-sm btn-outline-danger" id="removeVoucher">
                        <i class="bi bi-x-circle"></i> Hapus
                      </button>
                    </div>
                  </div>
                </div>
                
                <hr class="my-3">
                
                <!-- Price Breakdown -->
                <div class="price-breakdown">
                  <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
                    <span class="text-muted">Subtotal</span>
                    <strong class="fs-5" id="checkoutCartTotal">Rp 0</strong>
                  </div>
                  <div id="discountRow" class="d-flex justify-content-between align-items-center mb-3 pb-2" style="display: none !important;">
                    <span class="text-success">
                      <i class="bi bi-tag-fill me-1"></i> Diskon
                    </span>
                    <strong class="fs-5 text-success" id="discountAmount">- Rp 0</strong>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
                    <span class="text-muted">Ongkos Kirim</span>
                    <strong class="text-success" id="shippingCost">
                      <i class="bi bi-truck me-1"></i> Pilih lokasi
                    </strong>
                  </div>
                </div>
                
                <!-- Total Section -->
                <div class="total-section p-3 mt-3" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: 12px;">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="text-white fw-bold fs-5">
                      <i class="bi bi-cash-stack me-2"></i> Total Pembayaran
                    </span>
                    <strong class="text-white fs-3" id="grandTotal">Rp 0</strong>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                <i class="bi bi-check-circle"></i> Proses Pesanan
              </button>
              <a href="products.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali Belanja
              </a>
            </div>
          </form>

          <!-- Result Message -->
          <div id="checkoutResult" class="mt-3"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  try {
    // Get cart from localStorage
    let cart = [];
    try {
      const cartData = localStorage.getItem('sayur_mayur.cart');
      console.log('Raw cart from localStorage:', cartData);
      
      if (cartData) {
        cart = JSON.parse(cartData);
      }
    } catch (parseError) {
      console.error('JSON Parse error:', parseError);
      cart = [];
    }
    
    console.log('Parsed cart:', cart);
    console.log('Cart length:', cart.length);
    console.log('Cart is array:', Array.isArray(cart));

    const cartItemsDiv = document.getElementById('checkoutCartItems');
    const cartTotalEl = document.getElementById('checkoutCartTotal');
    const grandTotalEl = document.getElementById('grandTotal');
    const cartItemsWrapper = document.getElementById('cartItemsWrapper');
    
    let subtotal = 0;
    let discount = 0;
    let appliedVoucher = null;

    // Function untuk update total
    function updateTotals(newSubtotal = null, newDiscount = null) {
      if (newSubtotal !== null) subtotal = newSubtotal;
      if (newDiscount !== null) discount = newDiscount;
      
      // Simpan ke localStorage untuk diakses form submit
      localStorage.setItem('sayur_mayur.discount', discount);
      localStorage.setItem('sayur_mayur.subtotal', subtotal);
      if (appliedVoucher) {
        localStorage.setItem('sayur_mayur.voucher', JSON.stringify(appliedVoucher));
      }
      
      console.log('updateTotals called with subtotal:', subtotal, 'discount:', discount);
      
      // Re-query elements untuk memastikan mereka ada
      const cartTotalElement = document.getElementById('checkoutCartTotal');
      const grandTotalElement = document.getElementById('grandTotal');
      
      console.log('cartTotal element:', cartTotalElement);
      console.log('grandTotal element:', grandTotalElement);
      
      if (cartTotalElement) {
        const formattedSubtotal = 'Rp ' + subtotal.toLocaleString('id-ID');
        cartTotalElement.textContent = formattedSubtotal;
        console.log('Set checkoutCartTotal to:', formattedSubtotal);
      } else {
        console.error('checkoutCartTotal element not found!');
      }
      
      const total = subtotal - discount;
      if (grandTotalElement) {
        grandTotalElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
      } else {
        console.error('grandTotal element not found!');
      }
      
      const discountRow = document.getElementById('discountRow');
      if (discount > 0) {
        if (discountRow) discountRow.style.display = 'flex';
        const discountAmount = document.getElementById('discountAmount');
        if (discountAmount) {
          discountAmount.textContent = '- Rp ' + discount.toLocaleString('id-ID');
        }
      } else {
        if (discountRow) discountRow.style.display = 'none';
      }
    }

    if (cart.length === 0) {
      console.log('Cart is empty');
      if (cartItemsWrapper) {
        cartItemsWrapper.innerHTML = `
          <div class="alert alert-warning text-center border-0 shadow-sm">
            <i class="bi bi-cart-x" style="font-size: 4rem; color: #f59e0b;"></i>
            <h4 class="mt-3 mb-2">Keranjang Belanja Kosong</h4>
            <p class="text-muted mb-4">Sepertinya Anda belum menambahkan produk ke keranjang</p>
            <a href="products.php" class="btn btn-success btn-lg px-5">
              <i class="bi bi-shop"></i> Mulai Belanja Sekarang
            </a>
          </div>`;
      }
      const checkoutForm = document.getElementById('checkoutForm');
      if (checkoutForm) checkoutForm.style.display = 'none';
      
      // Update totals dengan 0
      updateTotals(0, 0);
      setupVoucherListeners();
      return;
    }

    let html = '<div class="cart-items-container">';
    
    cart.forEach((item) => {
      console.log('Processing cart item:', item);
      const itemSubtotal = item.price * item.qty;
      subtotal += itemSubtotal;
      console.log(`Item: ${item.name}, Price: ${item.price}, Qty: ${item.qty}, Subtotal: ${itemSubtotal}, Running total: ${subtotal}`);
      
      html += `
        <div style="display:flex; gap:14px; padding:12px 14px; border:1px solid #e5e7eb; border-radius:12px; background:#fff; align-items:center; margin-bottom:12px; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
          <div style="flex-shrink:0;">
            <img src="${item.image}" alt="${item.name}" style="width:120px; height:80px; object-fit:cover; border-radius:10px;">
          </div>
          <div style="flex:1; min-width:0;">
            <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
              <div style="flex:1; min-width:0;">
                <div style="font-weight:700; font-size:15px; margin-bottom:4px; color:#111827;">${item.name}</div>
                <div style="font-size:12px; color:#6b7280;">Rp ${item.price.toLocaleString('id-ID')} × ${item.qty}</div>
              </div>
              <div style="text-align:right; white-space:nowrap;">
                <div style="font-weight:700; color:#16a34a;">Rp ${itemSubtotal.toLocaleString('id-ID')}</div>
              </div>
            </div>
          </div>
        </div>
      `;
    });
    html += '</div>';
    
    console.log('===== FINAL SUBTOTAL CALCULATED =====');
    console.log('Total subtotal:', subtotal);
    console.log('======================================');
    
    if (cartItemsDiv) {
      cartItemsDiv.innerHTML = html;
    }
    
    // Update totals dengan subtotal yang sudah dihitung
    console.log('Calling updateTotals with:', subtotal, 0);
    updateTotals(subtotal, 0);
    
    // Store subtotal to localStorage first
    window.checkoutSubtotal = subtotal;
    
    setupVoucherListeners();
    loadShippingLocations();

  } catch (error) {
    console.error('Error in checkout DOMContentLoaded:', error);
    console.error('Stack:', error.stack);
  }

  // Load shipping locations
  function loadShippingLocations() {
    const lokasi_kirimSelect = document.getElementById('lokasi_kirim');
    if (!lokasi_kirimSelect) return;

    fetch('api/get_shipping.php')
      .then(response => response.json())
      .then(data => {
        if (data.success && Array.isArray(data.data)) {
          data.data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.dataset.biaya = item.biaya;
            option.dataset.estimasi = item.estimasi_hari;
            option.dataset.lokasi = item.lokasi;
            option.textContent = item.lokasi + ' - Rp ' + parseInt(item.biaya).toLocaleString('id-ID');
            lokasi_kirimSelect.appendChild(option);
          });
        }
      })
      .catch(error => console.error('Error loading shipping locations:', error));
  }

  // Update shipping cost
  window.updateShippingCost = function() {
    const lokasi_kirimSelect = document.getElementById('lokasi_kirim');
    const shippingCostEl = document.getElementById('shippingCost');
    const estimasiEl = document.getElementById('estimasiHari');
    
    const selectedOption = lokasi_kirimSelect.options[lokasi_kirimSelect.selectedIndex];
    if (!selectedOption.value) {
      if (shippingCostEl) shippingCostEl.innerHTML = '<i class="bi bi-truck me-1"></i> Pilih lokasi';
      if (estimasiEl) estimasiEl.innerHTML = '';
      updateGrandTotal(0);
      return;
    }

    const biaya = parseFloat(selectedOption.dataset.biaya);
    const estimasi = parseInt(selectedOption.dataset.estimasi);
    const lokasi = selectedOption.dataset.lokasi;

    if (shippingCostEl) {
      shippingCostEl.innerHTML = '<i class="bi bi-truck me-1"></i> Rp ' + biaya.toLocaleString('id-ID');
    }
    if (estimasiEl) {
      estimasiEl.innerHTML = '<i class="bi bi-clock me-1"></i> Estimasi ' + estimasi + ' hari';
    }

    // Store shipping info to localStorage
    localStorage.setItem('sayur_mayur.shipping', JSON.stringify({
      id: selectedOption.value,
      lokasi: lokasi,
      biaya: biaya
    }));

    updateGrandTotal(biaya);
  };

  // Update grand total with shipping cost
  function updateGrandTotal(shippingCost = 0) {
    const cartTotalEl = document.getElementById('checkoutCartTotal');
    const grandTotalEl = document.getElementById('grandTotal');
    const discountStored = parseFloat(localStorage.getItem('sayur_mayur.discount')) || 0;
    
    // Use cached subtotal from window object if available
    let subtotal = window.checkoutSubtotal || 0;
    
    // If no cached subtotal, calculate from cart
    if (subtotal === 0) {
      const cartData = JSON.parse(localStorage.getItem('sayur_mayur.cart')) || [];
      subtotal = cartData.reduce((sum, item) => sum + (item.price * item.qty), 0);
      window.checkoutSubtotal = subtotal;
    }
    
    const total = subtotal - discountStored + shippingCost;
    
    console.log('updateGrandTotal - subtotal:', subtotal, 'discount:', discountStored, 'shipping:', shippingCost, 'total:', total);
    
    // Update subtotal display
    if (cartTotalEl) {
      cartTotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
      console.log('Updated checkoutCartTotal to:', cartTotalEl.textContent);
    } else {
      console.error('checkoutCartTotal element not found!');
    }
    
    // Update grand total display
    if (grandTotalEl) {
      grandTotalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
    } else {
      console.error('grandTotal element not found!');
    }
  }

  // Setup voucher listeners
  function setupVoucherListeners() {
    const applyBtn = document.getElementById('applyVoucher');
    const removeBtn = document.getElementById('removeVoucher');
    
    console.log('setupVoucherListeners - applyBtn:', applyBtn, 'removeBtn:', removeBtn);
    
    if (applyBtn) {
      applyBtn.addEventListener('click', async function() {
        const voucherCode = document.getElementById('voucherCode');
        if (!voucherCode) {
          console.error('voucherCode input not found');
          return;
        }
        
        const code = voucherCode.value.trim().toUpperCase();
        const messageDiv = document.getElementById('voucherMessage');
        
        if (!code) {
          if (messageDiv) {
            messageDiv.innerHTML = '<small class="text-danger">Masukkan kode voucher</small>';
          }
          return;
        }

        try {
          const cartData = JSON.parse(localStorage.getItem('sayur_mayur.cart')) || [];
          const currentSubtotal = cartData.reduce((sum, item) => sum + (item.price * item.qty), 0);
          const formData = new FormData();
          formData.append('kode', code);
          formData.append('subtotal', currentSubtotal);

          const response = await fetch('cek_voucher.php', {
            method: 'POST',
            body: formData
          });

          let result;
          try {
            result = await response.json();
          } catch (parseErr) {
            const txt = await response.text();
            throw new Error('Gagal membaca respon: ' + txt);
          }

          if (result.status === 'success') {
            console.log('Voucher success:', result);
            updateTotals(currentSubtotal, result.diskon);
            appliedVoucher = result.voucher;
            
            const voucherInfo = document.getElementById('voucherInfo');
            if (voucherInfo) {
              voucherInfo.style.display = 'block';
              const voucherName = document.getElementById('voucherName');
              if (voucherName) {
                voucherName.innerHTML = `
                  <i class="bi bi-check-circle"></i> <strong>${appliedVoucher.kode}</strong> - ${result.voucher.nama}
                `;
              }
            }
            voucherCode.disabled = true;
            this.disabled = true;
            if (messageDiv) messageDiv.innerHTML = '';
          } else {
            if (messageDiv) {
              messageDiv.innerHTML = `<small class="text-danger">${result.message}</small>`;
            }
          }
        } catch (error) {
          console.error('Error applying voucher:', error);
          if (messageDiv) {
            messageDiv.innerHTML = '<small class="text-danger">Terjadi kesalahan, coba lagi.</small>';
          }
        }
      });
    }

    if (removeBtn) {
      removeBtn.addEventListener('click', function() {
        console.log('Remove voucher clicked');
        discount = 0;
        appliedVoucher = null;
        
        // Clear dari localStorage
        localStorage.removeItem('sayur_mayur.discount');
        localStorage.removeItem('sayur_mayur.voucher');
        
        updateTotals();
        
        const voucherInfo = document.getElementById('voucherInfo');
        if (voucherInfo) voucherInfo.style.display = 'none';
        
        const voucherCode = document.getElementById('voucherCode');
        if (voucherCode) {
          voucherCode.value = '';
          voucherCode.disabled = false;
        }
        
        const applyBtnRef = document.getElementById('applyVoucher');
        if (applyBtnRef) applyBtnRef.disabled = false;
        
        const messageDiv = document.getElementById('voucherMessage');
        if (messageDiv) messageDiv.innerHTML = '';
      });
    }
  }

  // Form submission
  const checkoutFormEl = document.getElementById('checkoutForm');
  if (checkoutFormEl) {
    checkoutFormEl.addEventListener('submit', async function (e) {
      e.preventDefault();
      
      const form = e.target;
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }

      const nama = document.getElementById('nama_pembeli').value.trim();
      const noTelp = document.getElementById('no_telp').value.trim();
      const alamat = document.getElementById('alamat').value.trim();
      const csrfToken = document.getElementById('csrf_token').value;
      const cartForSubmit = JSON.parse(localStorage.getItem('sayur_mayur.cart')) || [];

      if (cartForSubmit.length === 0) {
        showAlert('Keranjang belanja kosong', 'danger');
        return;
      }

      const submitBtn = document.getElementById('submitBtn');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
      }

      const paymentMethodEl = document.querySelector('input[name="payment_method"]:checked');
      const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'cod';
      
      const lokasi_kirim = document.getElementById('lokasi_kirim').value;
      if (!lokasi_kirim) {
        showAlert('Pilih lokasi pengiriman', 'danger');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Proses Pesanan';
        }
        return;
      }
      
      const subtotalValue = cartForSubmit.reduce((sum, item) => sum + (item.price * item.qty), 0);
      const discountValue = parseFloat(localStorage.getItem('sayur_mayur.discount') || 0);
      const appliedVoucherData = JSON.parse(localStorage.getItem('sayur_mayur.voucher') || 'null');
      const shippingData = JSON.parse(localStorage.getItem('sayur_mayur.shipping') || 'null');
      const shippingCost = shippingData ? shippingData.biaya : 0;
      const totalValue = subtotalValue - discountValue + shippingCost;

      try {
        const response = await fetch('proses_checkout.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            nama_pembeli: nama,
            no_telp: noTelp,
            alamat: alamat,
            lokasi_kirim: parseInt(lokasi_kirim),
            payment_method: paymentMethod,
            subtotal: subtotalValue,
            kode_voucher: appliedVoucherData ? appliedVoucherData.kode : null,
            diskon: discountValue,
            total: totalValue,
            items: cartForSubmit,
            csrf_token: csrfToken
          })
        });

        // Check response OK first
        if (!response.ok) {
          const text = await response.text();
          console.error('Server response error:', response.status, text);
          throw new Error(`HTTP ${response.status}: ${text.substring(0, 100)}`);
        }

        // Parse JSON with error handling
        let result;
        try {
          result = await response.json();
        } catch (parseError) {
          const text = await response.text();
          console.error('JSON parse error:', parseError, 'Response text:', text);
          throw new Error(`Invalid JSON response: ${text.substring(0, 100)}`);
        }

        if (result.status === 'success') {
          localStorage.removeItem('sayur_mayur.cart');
          window.location.href = 'invoice.php?id=' + result.transaksi_id;
        } else {
          showAlert(result.message || 'Gagal memproses pesanan', 'danger');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Proses Pesanan';
          }
        }
      } catch (error) {
        console.error('Checkout error:', error);
        showAlert('Terjadi kesalahan: ' + error.message, 'danger');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Proses Pesanan';
        }
      }
    });
  }

  function showAlert(message, type) {
    const resultDiv = document.getElementById('checkoutResult');
    if (resultDiv) {
      resultDiv.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show">
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
      resultDiv.scrollIntoView({ behavior: 'smooth' });
    }
  }
});
</script>

<?php include 'includes/footer.php'; ?>
