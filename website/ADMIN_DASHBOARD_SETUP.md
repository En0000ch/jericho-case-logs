# Admin Dashboard Setup Guide
## Manage Job Posting Prices, Promotions & Revenue

Your admin dashboard has been created and uploaded to your server!

---

## ✅ What's Already Done:

- ✅ Admin dashboard created (`admin-dashboard.html`)
- ✅ Uploaded to server at: `https://jerichocaselogs.com/admin-dashboard.html`
- ✅ Integrated with Back4App
- ✅ Beautiful responsive design
- ✅ Secure login system

---

## 🔐 STEP 1: Set Up Admin User in Back4App (5 minutes)

Before you can log in, you need to mark your user as an admin:

1. **Go to Back4App Dashboard:**
   - https://dashboard.back4app.com

2. **Select Your App:**
   - Click on "Jericho Case Logs" app

3. **Go to Database:**
   - Click "Database" in left sidebar
   - Click "_User" table

4. **Find Your User:**
   - Look for your email/username in the list
   - Click on the row to open it

5. **Add Admin Field:**
   - Click "+ Add Column" (if `isAdmin` doesn't exist)
   - Column name: `isAdmin`
   - Type: Boolean
   - Click "Create"

6. **Set as Admin:**
   - Check the `isAdmin` checkbox for your user
   - Click "Save"

**Done!** ✓ You can now log into the admin dashboard

---

## 📊 STEP 2: Create Promotions Table (3 minutes)

The dashboard needs a table to store promotions:

1. **In Back4App Dashboard:**
   - Go to Database → "+" button → Create a class

2. **Class Name:**
   ```
   Promotions
   ```

3. **Add These Columns:**

| Column Name | Type | Description |
|------------|------|-------------|
| name | String | Promotion name (e.g., "Summer Sale") |
| type | String | "bulk", "discount", or "fixed" |
| startDate | Date | When promotion starts |
| endDate | Date | When promotion ends |
| isActive | Boolean | Whether promotion is active |
| creditsGiven | Number | How many credits (for bulk/fixed) |
| creditsPaid | Number | How many they pay for (bulk only) |
| discountPercent | Number | Discount % (discount only) |
| fixedPrice | Number | Bundle price (fixed only) |

4. **Set Permissions (Important!):**
   - Click "Security" tab
   - Public Read: ✅ Enabled
   - Public Write: ❌ Disabled
   - Only authenticated users can create

**Done!** ✓ Promotions table is ready

---

## 💳 STEP 3: Create Purchases Table (Optional - for revenue tracking)

If you want to track revenue and purchases:

1. **Create Class:**
   ```
   Purchases
   ```

2. **Add These Columns:**

| Column Name | Type | Description |
|------------|------|-------------|
| organization | Pointer (_User) | Who bought credits |
| creditsAmount | Number | How many credits purchased |
| totalPrice | Number | Amount paid ($) |
| promotion | Pointer (Promotions) | Promotion used (if any) |
| purchaseDate | Date | When purchase was made |
| paymentMethod | String | "stripe", "paypal", etc. |
| transactionId | String | Payment provider transaction ID |

**Done!** ✓ Revenue tracking ready

---

## 🌐 STEP 4: Access Your Admin Dashboard

**Your Dashboard URL:**
```
https://jerichocaselogs.com/admin-dashboard.html
```

**Login:**
- Use your Back4App user email/password
- User must have `isAdmin = true` in Back4App _User table

---

## 🎯 What You Can Do in the Dashboard:

### 💰 Base Pricing
- Set the default price per job post credit
- Example: $50 per credit
- Updates `AppSettings.costPerPost` in Back4App

### 🎉 Create Promotions

**Option 1: Bulk Deal**
- Example: "3 for the price of 1"
- Customer pays for 1 credit, gets 3 credits
- Great for first-time customers

**Option 2: Percentage Discount**
- Example: "20% off all purchases"
- Applies discount to base price
- Good for seasonal sales

**Option 3: Fixed Price Bundle**
- Example: "5 credits for $100"
- Set exact number of credits and total price
- Flexible pricing options

### 📊 Revenue Overview
- Total revenue earned
- Monthly revenue
- Total purchases count
- Active promotions count

### 📋 Manage Promotions
- View all promotions (active & scheduled)
- Activate/Deactivate promotions
- Delete old promotions
- Set start/end dates

### 💳 Recent Purchases
- See who bought credits
- Track revenue by organization
- Monitor promotion usage
- View purchase history

---

## 🔗 How Promotions Work with Employer Dashboard:

1. **Employer logs into their dashboard**
2. **Goes to "Buy Credits" section**
3. **System checks for active promotions:**
   - If promotion active → Shows special pricing
   - If no promotion → Shows base price
4. **Employer selects package and pays**
5. **Credits added to their account**

---

## 🛠️ Integration with Existing System:

The admin dashboard works seamlessly with your existing setup:

- ✅ Uses same Back4App database
- ✅ Updates `AppSettings.costPerPost`
- ✅ Employer dashboard automatically reads promotions
- ✅ No code changes needed in employer dashboard
- ✅ Real-time pricing updates

---

## 📱 Add Hidden Link to Home Page (Later):

When you're ready, you can add a subtle admin link to your home page:

**Option 1: Footer Link (Subtle)**
```html
<p style="font-size: 10px; opacity: 0.3; text-align: center; margin-top: 50px;">
    <a href="/admin-dashboard.html" style="color: inherit; text-decoration: none;">•</a>
</p>
```

**Option 2: Keyboard Shortcut**
Add to home page:
```html
<script>
document.addEventListener('keydown', (e) => {
    // Press Ctrl+Shift+A to open admin
    if (e.ctrlKey && e.shiftKey && e.key === 'A') {
        window.location.href = '/admin-dashboard.html';
    }
});
</script>
```

**Option 3: Hidden Button**
```html
<div style="position: fixed; bottom: 0; right: 0; width: 20px; height: 20px;">
    <a href="/admin-dashboard.html" style="display: block; width: 100%; height: 100%;"></a>
</div>
```

---

## 🎨 Dashboard Features:

### Beautiful Design
- Purple gradient theme
- Responsive mobile-friendly layout
- Card-based UI
- Smooth animations

### Security
- Login required (Parse authentication)
- Admin role check
- Secure API calls
- Session management

### User-Friendly
- Clear labels and instructions
- Help text for each field
- Success/error messages
- Real-time updates

---

## 📝 Example Promotions You Can Create:

### Black Friday Sale
```
Name: Black Friday Blowout
Type: Percentage Discount
Discount: 50%
Start: November 24, 2025
End: November 28, 2025
```

### New Customer Special
```
Name: First Time Poster Deal
Type: Bulk Deal
Credits Given: 5
Credits Paid: 2
Start: January 1, 2026
End: December 31, 2026
```

### Holiday Bundle
```
Name: Holiday Hiring Package
Type: Fixed Price Bundle
Credits: 10
Price: $300
Start: November 1, 2025
End: January 15, 2026
```

---

## 🔍 Testing Your Dashboard:

1. **Access:** https://jerichocaselogs.com/admin-dashboard.html
2. **Login** with your admin account
3. **Update base price** to test
4. **Create a test promotion**
5. **Check employer dashboard** to see if promotion appears
6. **Deactivate promotion** and check again

---

## 🚀 Future Enhancements (Already Set Up For):

The dashboard structure supports future features:

- 📊 Detailed analytics charts
- 📧 Email campaigns to employers
- 🏢 View all registered employers
- 📈 Revenue reports by date range
- 💬 Send announcements
- 🎫 Coupon code system
- 📱 Push notifications

Just add more cards/sections as needed!

---

## ⚠️ Security Best Practices:

1. **Never share admin credentials**
2. **Use strong passwords**
3. **Enable 2FA on Back4App** if available
4. **Don't publish admin URL publicly**
5. **Keep hidden link subtle** on home page
6. **Regularly change passwords**
7. **Monitor admin logins** in Back4App

---

## 🐛 Troubleshooting:

**Can't log in:**
- Check if `isAdmin = true` in Back4App _User table
- Verify email/password correct
- Check browser console for errors

**Promotions not showing:**
- Make sure Promotions table exists in Back4App
- Check promotion dates (must be active)
- Verify `isActive = true`

**Revenue shows $0:**
- Need to integrate payment system first (Stripe/PayPal)
- Create Purchases table
- Record purchases when employers buy credits

**Dashboard looks broken:**
- Clear browser cache
- Check internet connection
- Verify file uploaded correctly

---

## 📞 Need Help?

If you get stuck:
1. Check Back4App Database → Check tables exist
2. Browser Console (F12) → Look for errors
3. Back4App Logs → Check API errors
4. Contact me with specific error messages

---

## ✅ Quick Setup Checklist:

- [ ] Access Back4App dashboard
- [ ] Mark your user as admin (`isAdmin = true`)
- [ ] Create Promotions table with all columns
- [ ] Create Purchases table (optional)
- [ ] Access admin dashboard URL
- [ ] Log in with admin credentials
- [ ] Set base price
- [ ] Create test promotion
- [ ] Verify promotion appears (when ready)
- [ ] Add hidden link to home page (later)

---

## 📊 Dashboard Statistics At Launch:

- Total Revenue: $0 (will update when sales start)
- Monthly Revenue: $0
- Total Purchases: 0
- Active Promotions: 0 (create your first!)

---

**Dashboard URL:** https://jerichocaselogs.com/admin-dashboard.html
**Back4App Dashboard:** https://dashboard.back4app.com
**File Location:** `/home/dh_w5amcg/jerichocaselogs.com/admin-dashboard.html`

**Last Updated:** December 30, 2025
**Status:** ✅ Uploaded and ready to configure

---

## 🎯 Next Steps:

1. Set up admin user in Back4App
2. Create Promotions table
3. Log into admin dashboard
4. Create your first promotion
5. Test with employer dashboard
6. Add hidden link to home page (when ready)

Good luck with your admin dashboard!
