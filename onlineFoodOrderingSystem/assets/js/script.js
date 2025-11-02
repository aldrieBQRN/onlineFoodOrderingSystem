document.addEventListener("DOMContentLoaded", () => {
  let cart = [];
  let checkoutOrderData = null; // <-- This variable stores the form data

  // DOM elements
  const cartItems = document.getElementById("cartItems");
  const cartTotal = document.getElementById("cartTotal");
  const emptyCart = document.getElementById("emptyCart");

  // Mobile elements
  const mobileCartItems = document.getElementById("mobileCartItems");
  const mobileCartTotal = document.getElementById("mobileCartTotal");
  const floatingCartTotal = document.getElementById("floatingCartTotal");

  // Checkout modal elements
  const checkoutSummary = document.getElementById("checkoutSummary");
  const checkoutTotal = document.getElementById("checkoutTotal");

  const finalOrderType = document.getElementById("finalOrderType");
  const addressField = document.getElementById("addressField");
  const createOrderBtn = document.getElementById("createOrderBtn");

  // Function to bind cart events
  function bindCartEvents() {
    document.querySelectorAll(".add-to-cart-btn").forEach((button) => {
      button.replaceWith(button.cloneNode(true));
    });

    document.querySelectorAll(".add-to-cart-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const name = e.target.dataset.name;
        const price = parseFloat(e.target.dataset.price);
        const existingItem = cart.find((item) => item.name === name);
        if (existingItem) {
          existingItem.quantity += 1;
        } else {
          cart.push({ name, price, quantity: 1 });
        }
        updateCartDisplay();
        const originalHTML = e.target.innerHTML;
        e.target.innerHTML = '<i class="bi bi-check"></i> Added!';
        e.target.disabled = true;
        setTimeout(() => {
          e.target.innerHTML = originalHTML;
          e.target.disabled = false;
        }, 1000);
      });
    });
  }

  // Toggle delivery address field based on order type
  function toggleAddressField() {
    if (finalOrderType && addressField) {
      addressField.style.display =
        finalOrderType.value === "Pickup" ? "none" : "block";
      const addressFields = addressField.querySelectorAll(
        "input[name], textarea[name]"
      );
      addressFields.forEach((field) => {
        field.required = finalOrderType.value === "Delivery";
      });
    }
  }

  if (finalOrderType) {
    finalOrderType.addEventListener("change", toggleAddressField);
    toggleAddressField();
  }

  // Setup checkout form submission
  function setupCheckoutForm() {
    const checkoutForm = document.getElementById("checkoutForm");
    const orderSuccessToastEl = document.getElementById("orderSuccessToast");
    const toastMessageBody = document.getElementById("toastMessageBody");

    let orderSuccessToast;
    if (orderSuccessToastEl && typeof bootstrap !== "undefined") {
      orderSuccessToast = new bootstrap.Toast(orderSuccessToastEl);
    }

    if (checkoutForm) {
      checkoutForm.addEventListener("submit", function (e) {
        e.preventDefault();

        if (cart.length === 0) {
          alert("Your cart is empty. Please add items before checking out.");
          return;
        }

        const formData = new FormData(checkoutForm);
        
        // --- THIS IS THE KEY PART ---
        // We build the orderData object HERE
        const orderData = {
          contact: {
            firstName: formData.get("first_name"),
            lastName: formData.get("last_name"),
            email: formData.get("email"),
            phone: formData.get("phone_number"),
          },
          delivery: {
            street: formData.get("street_address"),
            barangay: formData.get("barangay"),
            city: formData.get("city"),
            province: formData.get("province"),
            zipCode: formData.get("zip_code"),
            landmarks: formData.get("landmarks"),
            instructions: formData.get("delivery_instructions"),
          },
          order_type: formData.get("order_type"),
          payment_method: formData.get("payment_method"),
          order_time: formData.get("order_time"),
          items: cart,
          // !! THIS IS THE LINE YOUR OLD FILE IS MISSING !!
          total: cart.reduce((sum, item) => sum + item.price * item.quantity, 0),
        };
        // --- END OF KEY PART ---


        // --- NEW LOGIC ---
        if (orderData.payment_method === 'Gcash') {
            // 1. Don't create the order. Just store the data.
            checkoutOrderData = orderData;
            
            // 2. Hide checkout modal
            const checkoutModal = bootstrap.Modal.getInstance(document.getElementById("checkoutModal"));
            if (checkoutModal) checkoutModal.hide();

            // 3. Show payment modal
            const paymentModalEl = document.getElementById('paymentModal');
            if (paymentModalEl) {
                const paymentModal = new bootstrap.Modal(paymentModalEl);
                
                // Fetch GCash QR code and details
                fetch('actions/get_payment_details.php?method=Gcash')
                    .then(res => res.json())
                    .then(details => {
                        if (details.success && details.data) {
                            document.getElementById('gcashQrCode').src = details.data.qr_code_url;
                            document.getElementById('gcashAccountName').textContent = details.data.account_name;
                            document.getElementById('gcashAccountNumber').textContent = details.data.account_number;
                            document.getElementById('gcashInstructions').textContent = details.data.instructions;
                        } else {
                            document.getElementById('paymentModalBody').innerHTML = `<p class="text-danger">Error: Could not load payment details. Please contact support.</p>`;
                        }
                    });
                
                paymentModal.show();
            }
        
        } else {
            // --- ORIGINAL LOGIC for COD/Other ---
            // Send order data to server to create the order
            fetch("actions/create_order.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(orderData), // Send the complete orderData
            })
              .then((response) => response.json())
              .then((data) => {
                if (data.success) {
                  // Show success toast
                  if (orderSuccessToast && toastMessageBody) {
                    toastMessageBody.textContent = `Order placed successfully! Your order number is: ${data.order_number}`;
                    orderSuccessToast.show();
                  } else {
                    alert(`Order placed successfully! Your order number is: ${data.order_number}`);
                  }
                  
                  // Reset cart and close checkout modal
                  cart = [];
                  updateCartDisplay();
                  const checkoutModal = bootstrap.Modal.getInstance(document.getElementById("checkoutModal"));
                  if (checkoutModal) checkoutModal.hide();

                } else {
                  alert("Failed to place order: " + data.message);
                }
              })
              .catch((error) => {
                console.error("Error:", error);
                alert("An error occurred while placing your order. Please try again.");
              });
        }
      });
    }
  }

  // MODIFIED FUNCTION to handle payment receipt upload
  function setupPaymentUploadForm() {
    const paymentForm = document.getElementById('paymentUploadForm');
    if (!paymentForm) return;

    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!checkoutOrderData) {
            alert("Error: Order data is missing. Please try checking out again.");
            return;
        }

        const submitBtn = document.getElementById('uploadPaymentBtn');
        const messageDiv = document.getElementById('paymentUploadMessage');
        const originalBtnHtml = submitBtn.innerHTML;

        // --- NEW: Prepare FormData for combined upload ---
        const formData = new FormData();
        
        // 1. Add the order data (as a JSON string)
        formData.append('orderData', JSON.stringify(checkoutOrderData));
        
        // 2. Add the reference number
        formData.append('reference_number', document.getElementById('reference_number').value);
        
        // 3. Add the receipt image file
        const receiptImageFile = document.getElementById('receipt_image').files[0];
        if (!receiptImageFile) {
            messageDiv.textContent = 'Please select a receipt image to upload.';
            messageDiv.classList.remove('d-none');
            messageDiv.classList.add('alert-danger');
            return;
        }
        formData.append('receipt_image', receiptImageFile);


        // Disable button and show spinner
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...';
        messageDiv.classList.add('d-none');

        // --- Send to the NEW PHP file ---
        fetch('actions/create_order_with_payment.php', {
            method: 'POST',
            body: formData
            // Note: Don't set Content-Type header when using FormData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the payment modal
                const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                if (paymentModal) paymentModal.hide();

                // Show the final success toast
                const orderSuccessToastEl = document.getElementById("orderSuccessToast");
                const toastMessageBody = document.getElementById("toastMessageBody");
                if (orderSuccessToastEl && toastMessageBody) {
                     const orderSuccessToast = new bootstrap.Toast(orderSuccessToastEl);
                     toastMessageBody.textContent = `Order placed successfully! Your order number is: ${data.order_number}`;
                     orderSuccessToast.show();
                } else {
                    alert(`Order placed successfully! Your order number is: ${data.order_number}`);
                }
                
                // Reset everything
                paymentForm.reset();
                checkoutOrderData = null;
                cart = [];
                updateCartDisplay();

            } else {
                // Show error message
                messageDiv.textContent = data.message;
                messageDiv.classList.remove('d-none', 'alert-success');
                messageDiv.classList.add('alert-danger');
            }
        })
        .catch(error => {
            console.error('Payment Upload Error:', error);
            messageDiv.textContent = 'An error occurred. Please try again.';
            messageDiv.classList.remove('d-none', 'alert-success');
            messageDiv.classList.add('alert-danger');
        })
        .finally(() => {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        });
    });
  }


  function updateCartDisplay() {
    const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    if (cartTotal) cartTotal.textContent = `₱${totalPrice.toFixed(2)}`;
    if (mobileCartTotal) mobileCartTotal.textContent = `₱${totalPrice.toFixed(2)}`;
    if (floatingCartTotal) floatingCartTotal.textContent = `₱${totalPrice.toFixed(2)}`;
    if (checkoutTotal) checkoutTotal.textContent = `₱${totalPrice.toFixed(2)}`;

    if (cartItems) cartItems.innerHTML = "";
    if (mobileCartItems) mobileCartItems.innerHTML = "";
    if (checkoutSummary) checkoutSummary.innerHTML = "";

    if (cart.length === 0) {
      if (emptyCart) emptyCart.style.display = "block";
      if (mobileCartItems) mobileCartItems.innerHTML = `<div class="empty-cart text-center py-4"><i class="bi bi-cart-x"></i><p class="mb-0">Your cart is empty</p><small class="text-muted">Add some delicious items!</small></div>`;
      if (checkoutSummary) checkoutSummary.innerHTML = '<li class="list-group-item text-center text-muted">Your cart is empty.</li>';
      if (createOrderBtn) {
        createOrderBtn.disabled = true;
        createOrderBtn.textContent = "Cart is Empty";
      }
    } else {
      if (emptyCart) emptyCart.style.display = "none";
      cart.forEach((item, index) => {
        const cartItemHTML = `
            <div class="cart-item">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">₱${item.price.toFixed(2)} each</div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(${index}, -1)"><i class="bi bi-dash"></i></button>
                        <span class="fw-semibold">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${index}, 1)"><i class="bi bi-plus"></i></button>
                    </div>
                    <div class="fw-bold">₱${(item.price * item.quantity).toFixed(2)}</div>
                </div>
            </div>`;
        if (cartItems) cartItems.innerHTML += cartItemHTML;
        if (mobileCartItems) mobileCartItems.innerHTML += cartItemHTML;
        if (checkoutSummary) checkoutSummary.innerHTML += `<li class="list-group-item d-flex justify-content-between"><span>${item.quantity}x ${item.name}</span><span>₱${(item.price * item.quantity).toFixed(2)}</span></li>`;
      });
      if (createOrderBtn) {
        createOrderBtn.disabled = false;
        createOrderBtn.innerHTML = '<i class="bi bi-bag-check"></i> Create Order';
      }
    }
  }

  // Make updateQuantity globally available
  window.updateQuantity = function (index, change) {
    if (cart[index]) {
      cart[index].quantity += change;
      if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
      }
      updateCartDisplay();
    }
  };

  // --- Authentication Logic (Unchanged) ---
  function handleAuthResponse(data, messageDiv, form, successCallback) {
    messageDiv.classList.remove("d-none", "alert-success", "alert-danger");
    if (data.success) {
      messageDiv.classList.add("alert-success");
      messageDiv.textContent = data.message;
      form.reset();
      if (successCallback) successCallback(data);
    } else {
      messageDiv.classList.add("alert-danger");
      messageDiv.textContent = data.message;
    }
  }

  function updateNavbar(isLoggedIn, userName) {
    if (isLoggedIn) {
      location.reload();
    }
  }

  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const form = e.target;
      const messageDiv = document.getElementById("loginMessage");
      const formData = new FormData(form);
      fetch("actions/login.php", { method: "POST", body: formData })
        .then((response) => response.json())
        .then((data) => {
          handleAuthResponse(data, messageDiv, form, () => {
            const loginModal = bootstrap.Modal.getInstance(document.getElementById("loginModal"));
            if (loginModal) loginModal.hide();
            if (data.role === "admin") {
              window.location.href = "admin/dashboard.php";
            } else {
              updateNavbar(true, data.full_name);
            }
          });
        })
        .catch((error) => console.error("Login Error:", error));
    });
  }

  const signupForm = document.getElementById("signupForm");
  if (signupForm) {
    signupForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const form = e.target;
      const messageDiv = document.getElementById("signupMessage");
      const formData = new FormData(form);
      fetch("actions/signup.php", { method: "POST", body: formData })
        .then((response) => response.json())
        .then((data) => {
          handleAuthResponse(data, messageDiv, form, () => {
            const signupModal = bootstrap.Modal.getInstance(document.getElementById("signupModal"));
            if (signupModal) signupModal.hide();
            setTimeout(() => {
              const loginModal = new bootstrap.Modal(document.getElementById("loginModal"));
              loginModal.show();
            }, 500);
          });
        })
        .catch((error) => console.error("Signup Error:", error));
    });
  }

  // Initial bind of cart events
  bindCartEvents();
  // Initial update of cart display
  updateCartDisplay();
  // Setup checkout form
  setupCheckoutForm();
  // SETUP THE NEW PAYMENT FORM
  setupPaymentUploadForm(); 

  // --- Scroll Animation (Unchanged) ---
  const animatedElements = document.querySelectorAll(".fade-in, .fade-in-left, .fade-in-right");
  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1 }
    );
    animatedElements.forEach((el) => observer.observe(el));
  } else {
    animatedElements.forEach((el) => el.classList.add("is-visible"));
  }
});