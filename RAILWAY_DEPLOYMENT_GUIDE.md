# Deploy Laravel to Railway - Step by Step Guide

## 🚀 Quick Railway Deployment for KYC Testing

### **Step 1: Prepare Your Laravel App**

1. **Create a Procfile** in your project root:
```
web: php artisan serve --host=0.0.0.0 --port=$PORT
```

2. **Update your .env for production**:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.up.railway.app
```

### **Step 2: Install Railway CLI**

```bash
# Install Railway CLI
npm install -g @railway/cli

# Or download from: https://railway.app/cli
```

### **Step 3: Deploy to Railway**

```bash
# Login to Railway
railway login

# Initialize project
railway init

# Deploy your app
railway up

# Add MySQL database
railway add mysql

# Set environment variables
railway variables set APP_KEY=your_app_key_here
railway variables set DB_CONNECTION=mysql
railway variables set DB_HOST=${{MYSQL_HOST}}
railway variables set DB_PORT=${{MYSQL_PORT}}
railway variables set DB_DATABASE=${{MYSQL_DATABASE}}
railway variables set DB_USERNAME=${{MYSQL_USER}}
railway variables set DB_PASSWORD=${{MYSQL_PASSWORD}}

# Run migrations
railway run php artisan migrate
```

### **Step 4: Configure Didit**

Once deployed, you'll get a URL like: `https://your-app-name.up.railway.app`

Update your Didit Business Console:
- **Callback URL**: `https://your-app-name.up.railway.app/kyc/webhook`
- **Redirect URL**: `https://your-app-name.up.railway.app/kyc/success`

### **Step 5: Test KYC Flow**

1. Visit: `https://your-app-name.up.railway.app/kyc/start`
2. Complete real Didit verification
3. Get redirected back to your app
4. ✅ No more ngrok issues!

## 💰 **Cost Breakdown**

- **Railway**: $5/month credit (free to start)
- **Domain**: Free subdomain included
- **SSL**: Free automatic HTTPS
- **Database**: Included in free tier

## 🎯 **Benefits for KYC Testing**

✅ **Real domain** - Didit can reach your app
✅ **HTTPS enabled** - Required for webhooks
✅ **No DNS issues** - Professional hosting
✅ **Easy updates** - Push code to deploy
✅ **Database included** - Full Laravel support
✅ **Logs & monitoring** - Debug production issues

## 🔄 **Alternative: Quick Test with Ngrok Fix**

If you want to stick with local development, try:

1. **Different network** (mobile hotspot)
2. **Different DNS** (8.8.8.8, 1.1.1.1)
3. **VPN** to change your IP location
4. **Different browser** (Chrome, Firefox, Edge)

But for reliable KYC testing, a staging server is the best solution! 🚀