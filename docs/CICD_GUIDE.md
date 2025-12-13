# CI/CD Pipeline Guide

## Overview

This project uses **GitHub Actions** for continuous integration and deployment. The CI/CD pipeline automatically tests, builds, and deploys the application.

## Workflows

### 1. Laravel CI/CD (`laravel.yml`)

**Triggers:**
- Push to `main`, `master`, or `develop` branches
- Pull requests to `main`, `master`, or `develop` branches

**Jobs:**

#### Test Job
- **PHP Versions**: 8.2
- **Database**: SQLite (in-memory)
- **Steps**:
  1. Checkout code
  2. Setup PHP with required extensions
  3. Copy `.env` from `.env.example`
  4. Cache Composer dependencies
  5. Install Composer dependencies
  6. Generate application key
  7. Run database migrations
  8. Run PHPUnit tests with code coverage
  9. Upload coverage reports to Codecov

**Coverage Requirements:**
- Minimum: 30% (will increase gradually to 75%)
- Reports uploaded to Codecov

#### Code Quality Job
- **Checks**:
  1. Laravel Pint (PSR-12 code style)
  2. Composer Audit (security vulnerabilities)

#### Build Job
- **Triggers**: Only on push to `main`/`master`
- **Dependencies**: Requires `test` and `code-quality` jobs to pass
- **Steps**:
  1. Install Node.js dependencies
  2. Build frontend assets with Vite
  3. Archive production artifacts
  4. Upload build artifacts (retained for 7 days)

### 2. Security Scanning (`security.yml`)

**Triggers:**
- Daily schedule (2 AM UTC)
- Push to `main`/`master`
- Pull requests to `main`/`master`
- Manual dispatch

**Jobs:**

#### Dependency Scan
- Composer security audit
- Check for known vulnerabilities in PHP packages

#### Code Scan
- PHPStan static analysis (Level 5)
- Identifies potential bugs and code issues

#### Environment Check
- Ensures `.env` is not committed
- Scans for hardcoded passwords
- Detects hardcoded API keys

#### SQL Injection Scan
- Checks for unsafe raw SQL queries
- Reviews DB::raw usage
- Identifies potential injection points

### 3. Deployment (`deployment.yml`)

**Triggers:**
- Manual workflow dispatch only

**Environments:**
- Staging
- Production

**Steps:**
1. Build optimized application
2. Install production dependencies
3. Build frontend assets
4. Create deployment package
5. Deploy to target server
6. Run post-deployment tasks:
   - Database migrations
   - Cache clearing
   - Application optimization
   - Queue restart
7. Perform health checks
8. Send deployment notifications

## Setup Instructions

### 1. Enable GitHub Actions

GitHub Actions are automatically enabled for repositories. No additional setup required.

### 2. Configure Secrets

Add the following secrets in **Settings → Secrets and variables → Actions**:

#### Required Secrets:
```
CODECOV_TOKEN          # For code coverage reporting (optional)
```

#### For Production Deployment (if using):
```
DEPLOY_SSH_KEY         # SSH key for deployment
DEPLOY_HOST            # Production server host
DEPLOY_USER            # Deployment user
DEPLOY_PATH            # Deployment directory path
```

#### For Third-party Services:
```
RAILWAY_TOKEN          # For Railway deployment
SLACK_WEBHOOK_URL      # For Slack notifications
DISCORD_WEBHOOK_URL    # For Discord notifications
```

### 3. Configure Environments

Create environments in **Settings → Environments**:

- **staging**: For staging deployments
- **production**: For production deployments

Set environment-specific secrets and protection rules.

### 4. Branch Protection

Configure branch protection for `main`/`master`:

1. Go to **Settings → Branches**
2. Add rule for `main`/`master`
3. Enable:
   - ✅ Require pull request before merging
   - ✅ Require status checks to pass
   - ✅ Require branches to be up to date
   - Select: `test`, `code-quality`
   - ✅ Do not allow bypassing the above settings

## Local Development

### Running Tests Locally

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific suite
php artisan test --testsuite=Feature
```

### Code Style Check

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

### Security Audit

```bash
# Check for vulnerabilities
composer audit
```

## Workflow Status Badges

Add these badges to your README.md:

```markdown
![Tests](https://github.com/username/repository/actions/workflows/laravel.yml/badge.svg)
![Security](https://github.com/username/repository/actions/workflows/security.yml/badge.svg)
![Code Coverage](https://codecov.io/gh/username/repository/branch/main/graph/badge.svg)
```

## Continuous Deployment

### Automatic Deployment (Optional)

To enable automatic deployment on push to `main`:

1. Modify `deployment.yml` to trigger on push:
```yaml
on:
  push:
    branches: [main]
```

2. Add deployment commands specific to your hosting:

**Railway:**
```yaml
- name: Deploy to Railway
  run: |
    npm i -g @railway/cli
    railway up
  env:
    RAILWAY_TOKEN: ${{ secrets.RAILWAY_TOKEN }}
```

**SSH Deployment:**
```yaml
- name: Deploy via SSH
  uses: appleboy/ssh-action@master
  with:
    host: ${{ secrets.DEPLOY_HOST }}
    username: ${{ secrets.DEPLOY_USER }}
    key: ${{ secrets.DEPLOY_SSH_KEY }}
    script: |
      cd ${{ secrets.DEPLOY_PATH }}
      git pull
      composer install --no-dev
      npm install && npm run build
      php artisan migrate --force
      php artisan optimize
```

## Deployment Checklist

Before deploying to production:

- [ ] All tests passing
- [ ] Code style check passing
- [ ] Security scan completed
- [ ] Code coverage meets minimum (30%+)
- [ ] `.env` configured on server
- [ ] Database backups created
- [ ] Maintenance mode enabled
- [ ] Deploy application
- [ ] Run migrations
- [ ] Clear caches
- [ ] Test critical functionality
- [ ] Disable maintenance mode
- [ ] Monitor error logs

## Monitoring

### GitHub Actions Monitoring

- View workflow runs: **Actions** tab
- Check job logs for errors
- Review failed tests
- Monitor build times

### Post-Deployment Monitoring

1. **Application Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Queue Monitoring**
   ```bash
   php artisan queue:work --verbose
   ```

3. **Performance Monitoring**
   - Use Laravel Telescope (development)
   - Use New Relic / Scout (production)

## Troubleshooting

### Tests Failing in CI but Passing Locally

1. Check PHP version matches (8.2)
2. Verify all environment variables
3. Check database configuration (SQLite vs MySQL)
4. Review dependency versions

### Build Failing

1. Check Node.js version (18)
2. Clear npm cache: `npm cache clean --force`
3. Delete `node_modules` and `package-lock.json`
4. Run `npm install` fresh

### Deployment Failing

1. Check server connectivity
2. Verify SSH keys/credentials
3. Check disk space on server
4. Review deployment logs
5. Verify server PHP version

## Best Practices

1. **Commit Often**: Small, focused commits
2. **Test Locally**: Run tests before pushing
3. **Code Style**: Run Pint before committing
4. **Branch Strategy**: Use feature branches
5. **Pull Requests**: Require reviews
6. **Semantic Versioning**: Tag releases properly

## Rollback Strategy

If deployment fails:

1. **Immediate Rollback:**
   ```bash
   git revert HEAD
   git push
   ```

2. **Database Rollback:**
   ```bash
   php artisan migrate:rollback
   ```

3. **Restore from Backup:**
   ```bash
   # Restore database backup
   mysql database < backup.sql

   # Restore files
   rsync -av backup/ current/
   ```

## Next Steps

1. ✅ CI/CD pipeline configured
2. ⬜ Add code coverage reporting
3. ⬜ Set up automated deployments
4. ⬜ Configure monitoring alerts
5. ⬜ Add performance testing
6. ⬜ Set up staging environment

## Summary

✅ **Phase 1 Complete: CI/CD Pipeline**

- Created GitHub Actions workflows
- Automated testing on every push/PR
- Code quality checks (Pint, Composer Audit)
- Security scanning (dependencies, code, SQL)
- Build automation for assets
- Deployment workflow (manual trigger)
- Comprehensive documentation

**Benefits:**
- Catch bugs early
- Maintain code quality
- Ensure security
- Fast, reliable deployments
- Automated testing
- Consistent builds
