# CI/CD & Deployment Guide

Complete CI/CD pipeline configurations and deployment strategies for all major platforms.

---

## Table of Contents
1. [GitHub Actions Workflows](#github-actions-workflows)
2. [Vercel Deployment](#vercel-deployment)
3. [Railway Deployment](#railway-deployment)
4. [AWS Deployment](#aws-deployment)
5. [Docker Production](#docker-production)
6. [Environment Management](#environment-management)

---

## GitHub Actions Workflows

### Main CI/CD Workflow

**File**: `.github/workflows/ci.yml`

```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

env:
  NODE_VERSION: "20"
  PNPM_VERSION: "8"

jobs:
  # Lint and Type Check
  lint:
    name: Lint & Type Check
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup pnpm
        uses: pnpm/action-setup@v2
        with:
          version: ${{ env.PNPM_VERSION }}

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: ${{ env.NODE_VERSION }}
          cache: "pnpm"

      - name: Install dependencies
        run: pnpm install --frozen-lockfile

      - name: Run ESLint
        run: pnpm lint

      - name: Run Prettier check
        run: pnpm format:check

      - name: TypeScript type check
        run: pnpm type-check

  # Unit & Integration Tests
  test:
    name: Unit & Integration Tests
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:15-alpine
        env:
          POSTGRES_USER: postgres
          POSTGRES_PASSWORD: password
          POSTGRES_DB: serpsurfer_test
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

      redis:
        image: redis:7-alpine
        ports:
          - 6379:6379
        options: >-
          --health-cmd "redis-cli ping"
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup pnpm
        uses: pnpm/action-setup@v2
        with:
          version: ${{ env.PNPM_VERSION }}

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: ${{ env.NODE_VERSION }}
          cache: "pnpm"

      - name: Install dependencies
        run: pnpm install --frozen-lockfile

      - name: Generate Prisma Client
        run: pnpm db:generate

      - name: Run database migrations
        run: pnpm db:migrate:deploy
        env:
          DATABASE_URL: postgresql://postgres:password@localhost:5432/serpsurfer_test

      - name: Run tests with coverage
        run: pnpm test:coverage
        env:
          DATABASE_URL: postgresql://postgres:password@localhost:5432/serpsurfer_test
          REDIS_URL: redis://localhost:6379
          NEXTAUTH_SECRET: test-secret
          NEXTAUTH_URL: http://localhost:3000

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage/lcov.info
          flags: unittests
          name: codecov-umbrella

  # E2E Tests
  e2e:
    name: E2E Tests
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:15-alpine
        env:
          POSTGRES_USER: postgres
          POSTGRES_PASSWORD: password
          POSTGRES_DB: serpsurfer_test
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup pnpm
        uses: pnpm/action-setup@v2
        with:
          version: ${{ env.PNPM_VERSION }}

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: ${{ env.NODE_VERSION }}
          cache: "pnpm"

      - name: Install dependencies
        run: pnpm install --frozen-lockfile

      - name: Install Playwright browsers
        run: pnpm exec playwright install --with-deps

      - name: Run database migrations
        run: pnpm db:migrate:deploy
        env:
          DATABASE_URL: postgresql://postgres:password@localhost:5432/serpsurfer_test

      - name: Seed test database
        run: pnpm db:seed
        env:
          DATABASE_URL: postgresql://postgres:password@localhost:5432/serpsurfer_test

      - name: Run E2E tests
        run: pnpm test:e2e
        env:
          DATABASE_URL: postgresql://postgres:password@localhost:5432/serpsurfer_test
          NEXTAUTH_SECRET: test-secret
          NEXTAUTH_URL: http://localhost:3000

      - name: Upload Playwright report
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: playwright-report
          path: playwright-report/
          retention-days: 30

  # Build Check
  build:
    name: Build Check
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup pnpm
        uses: pnpm/action-setup@v2
        with:
          version: ${{ env.PNPM_VERSION }}

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: ${{ env.NODE_VERSION }}
          cache: "pnpm"

      - name: Install dependencies
        run: pnpm install --frozen-lockfile

      - name: Generate Prisma Client
        run: pnpm db:generate

      - name: Build application
        run: pnpm build
        env:
          SKIP_ENV_VALIDATION: true

      - name: Check build size
        run: |
          echo "Build completed successfully"
          du -sh .next

  # Security Scan
  security:
    name: Security Scan
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Run Trivy vulnerability scanner
        uses: aquasecurity/trivy-action@master
        with:
          scan-type: "fs"
          scan-ref: "."
          format: "sarif"
          output: "trivy-results.sarif"

      - name: Upload Trivy results to GitHub Security
        uses: github/codeql-action/upload-sarif@v2
        with:
          sarif_file: "trivy-results.sarif"

      - name: Run npm audit
        run: npm audit --audit-level=high
        continue-on-error: true

  # Deploy to Production
  deploy-production:
    name: Deploy to Production
    needs: [lint, test, e2e, build]
    if: github.ref == 'refs/heads/main' && github.event_name == 'push'
    runs-on: ubuntu-latest
    environment:
      name: production
      url: https://serpsurfer.com
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Deploy to Vercel
        uses: amondnet/vercel-action@v25
        with:
          vercel-token: ${{ secrets.VERCEL_TOKEN }}
          vercel-org-id: ${{ secrets.VERCEL_ORG_ID }}
          vercel-project-id: ${{ secrets.VERCEL_PROJECT_ID }}
          vercel-args: "--prod"

      - name: Run database migrations
        run: |
          pnpm install --frozen-lockfile
          pnpm db:migrate:deploy
        env:
          DATABASE_URL: ${{ secrets.DATABASE_URL }}

      - name: Notify deployment
        uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          text: "Production deployment completed"
          webhook_url: ${{ secrets.SLACK_WEBHOOK }}
        if: always()

  # Deploy to Staging
  deploy-staging:
    name: Deploy to Staging
    needs: [lint, test, build]
    if: github.ref == 'refs/heads/develop' && github.event_name == 'push'
    runs-on: ubuntu-latest
    environment:
      name: staging
      url: https://staging.serpsurfer.com
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Deploy to Vercel (Staging)
        uses: amondnet/vercel-action@v25
        with:
          vercel-token: ${{ secrets.VERCEL_TOKEN }}
          vercel-org-id: ${{ secrets.VERCEL_ORG_ID }}
          vercel-project-id: ${{ secrets.VERCEL_PROJECT_ID }}
```

### Database Migration Workflow

**File**: `.github/workflows/db-migrate.yml`

```yaml
name: Database Migrations

on:
  workflow_dispatch:
    inputs:
      environment:
        description: "Environment to migrate"
        required: true
        type: choice
        options:
          - staging
          - production

jobs:
  migrate:
    name: Run Migrations
    runs-on: ubuntu-latest
    environment: ${{ github.event.inputs.environment }}
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup pnpm
        uses: pnpm/action-setup@v2
        with:
          version: "8"

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: "20"
          cache: "pnpm"

      - name: Install dependencies
        run: pnpm install --frozen-lockfile

      - name: Run migrations
        run: pnpm db:migrate:deploy
        env:
          DATABASE_URL: ${{ secrets.DATABASE_URL }}

      - name: Verify migration
        run: |
          echo "Migrations completed successfully"
          pnpm prisma db seed
        env:
          DATABASE_URL: ${{ secrets.DATABASE_URL }}
```

### Dependency Update Workflow

**File**: `.github/workflows/dependency-update.yml`

```yaml
name: Dependency Updates

on:
  schedule:
    - cron: "0 0 * * 1" # Every Monday at midnight
  workflow_dispatch:

jobs:
  update:
    name: Update Dependencies
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup pnpm
        uses: pnpm/action-setup@v2
        with:
          version: "8"

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: "20"

      - name: Update dependencies
        run: pnpm update --latest

      - name: Run tests
        run: pnpm test

      - name: Create Pull Request
        uses: peter-evans/create-pull-request@v5
        with:
          commit-message: "chore: update dependencies"
          title: "chore: automated dependency updates"
          body: "Automated dependency updates"
          branch: "chore/dependency-updates"
```

---

## Vercel Deployment

### Vercel Configuration

**File**: `vercel.json`

```json
{
  "buildCommand": "pnpm build",
  "devCommand": "pnpm dev",
  "installCommand": "pnpm install",
  "framework": "nextjs",
  "regions": ["iad1"],
  "env": {
    "DATABASE_URL": "@database-url",
    "REDIS_URL": "@redis-url",
    "NEXTAUTH_SECRET": "@nextauth-secret",
    "NEXTAUTH_URL": "@nextauth-url",
    "GOOGLE_CLIENT_ID": "@google-client-id",
    "GOOGLE_CLIENT_SECRET": "@google-client-secret"
  },
  "build": {
    "env": {
      "SKIP_ENV_VALIDATION": "1"
    }
  },
  "functions": {
    "api/**/*.ts": {
      "maxDuration": 30
    },
    "api/queue/**/*.ts": {
      "maxDuration": 60
    }
  },
  "crons": [
    {
      "path": "/api/cron/scan-sitemaps",
      "schedule": "0 8 * * *"
    },
    {
      "path": "/api/cron/cleanup",
      "schedule": "0 */6 * * *"
    }
  ]
}
```

### Vercel CLI Commands

```bash
# Install Vercel CLI
pnpm add -g vercel

# Login
vercel login

# Link project
vercel link

# Set environment variables
vercel env add DATABASE_URL production
vercel env add REDIS_URL production
vercel env add NEXTAUTH_SECRET production

# Deploy to preview
vercel

# Deploy to production
vercel --prod

# View logs
vercel logs

# Run locally with production env
vercel dev
```

---

## Railway Deployment

### Railway Configuration

**File**: `railway.json`

```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "nixpacks",
    "buildCommand": "pnpm install && pnpm build"
  },
  "deploy": {
    "startCommand": "pnpm start",
    "healthcheckPath": "/api/health",
    "healthcheckTimeout": 300,
    "restartPolicyType": "on-failure",
    "restartPolicyMaxRetries": 10
  }
}
```

### Railway Services Setup

```yaml
# railway.toml
[build]
builder = "nixpacks"
buildCommand = "pnpm install && pnpm build && pnpm db:migrate:deploy"

[deploy]
startCommand = "pnpm start"
healthcheckPath = "/api/health"
healthcheckTimeout = 300

[[services]]
name = "web"
source = "."
[services.env]
PORT = "3000"

[[services]]
name = "worker"
source = "."
[services.deploy]
startCommand = "pnpm worker"

[[services]]
name = "scheduler"
source = "."
[services.deploy]
startCommand = "pnpm scheduler"
```

### Railway CLI Commands

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Initialize project
railway init

# Link to existing project
railway link

# Add PostgreSQL
railway add --database postgres

# Add Redis
railway add --database redis

# Set environment variables
railway variables set NEXTAUTH_SECRET=your-secret

# Deploy
railway up

# View logs
railway logs

# Run migrations
railway run pnpm db:migrate:deploy
```

---

## AWS Deployment

### ECS Task Definition

**File**: `aws/task-definition.json`

```json
{
  "family": "serp-surfer",
  "networkMode": "awsvpc",
  "requiresCompatibilities": ["FARGATE"],
  "cpu": "1024",
  "memory": "2048",
  "executionRoleArn": "arn:aws:iam::ACCOUNT_ID:role/ecsTaskExecutionRole",
  "taskRoleArn": "arn:aws:iam::ACCOUNT_ID:role/ecsTaskRole",
  "containerDefinitions": [
    {
      "name": "web",
      "image": "ACCOUNT_ID.dkr.ecr.REGION.amazonaws.com/serp-surfer:latest",
      "portMappings": [
        {
          "containerPort": 3000,
          "protocol": "tcp"
        }
      ],
      "environment": [
        {
          "name": "NODE_ENV",
          "value": "production"
        },
        {
          "name": "PORT",
          "value": "3000"
        }
      ],
      "secrets": [
        {
          "name": "DATABASE_URL",
          "valueFrom": "arn:aws:secretsmanager:REGION:ACCOUNT_ID:secret:DATABASE_URL"
        },
        {
          "name": "NEXTAUTH_SECRET",
          "valueFrom": "arn:aws:secretsmanager:REGION:ACCOUNT_ID:secret:NEXTAUTH_SECRET"
        }
      ],
      "logConfiguration": {
        "logDriver": "awslogs",
        "options": {
          "awslogs-group": "/ecs/serp-surfer",
          "awslogs-region": "us-east-1",
          "awslogs-stream-prefix": "web"
        }
      },
      "healthCheck": {
        "command": ["CMD-SHELL", "wget --no-verbose --tries=1 --spider http://localhost:3000/api/health || exit 1"],
        "interval": 30,
        "timeout": 5,
        "retries": 3,
        "startPeriod": 60
      }
    }
  ]
}
```

### CloudFormation Template

**File**: `aws/cloudformation.yml`

```yaml
AWSTemplateFormatVersion: "2010-09-09"
Description: SERP Surfer Infrastructure

Parameters:
  Environment:
    Type: String
    Default: production
    AllowedValues:
      - production
      - staging

Resources:
  # VPC
  VPC:
    Type: AWS::EC2::VPC
    Properties:
      CidrBlock: 10.0.0.0/16
      EnableDnsHostnames: true
      EnableDnsSupport: true
      Tags:
        - Key: Name
          Value: !Sub ${Environment}-serp-surfer-vpc

  # RDS PostgreSQL
  DBSubnetGroup:
    Type: AWS::RDS::DBSubnetGroup
    Properties:
      DBSubnetGroupDescription: Subnet group for RDS
      SubnetIds:
        - !Ref PrivateSubnet1
        - !Ref PrivateSubnet2

  Database:
    Type: AWS::RDS::DBInstance
    Properties:
      DBInstanceIdentifier: !Sub ${Environment}-serp-surfer-db
      AllocatedStorage: 20
      DBInstanceClass: db.t3.micro
      Engine: postgres
      EngineVersion: "15.3"
      MasterUsername: postgres
      MasterUserPassword: !Sub "{{resolve:secretsmanager:${DBPasswordSecret}:SecretString:password}}"
      DBSubnetGroupName: !Ref DBSubnetGroup
      VPCSecurityGroups:
        - !Ref DBSecurityGroup
      BackupRetentionPeriod: 7
      PreferredBackupWindow: "03:00-04:00"
      PreferredMaintenanceWindow: "mon:04:00-mon:05:00"

  # ElastiCache Redis
  CacheSubnetGroup:
    Type: AWS::ElastiCache::SubnetGroup
    Properties:
      Description: Subnet group for Redis
      SubnetIds:
        - !Ref PrivateSubnet1
        - !Ref PrivateSubnet2

  RedisCluster:
    Type: AWS::ElastiCache::CacheCluster
    Properties:
      CacheNodeType: cache.t3.micro
      Engine: redis
      NumCacheNodes: 1
      CacheSubnetGroupName: !Ref CacheSubnetGroup
      VpcSecurityGroupIds:
        - !Ref RedisSecurityGroup

  # ECS Cluster
  ECSCluster:
    Type: AWS::ECS::Cluster
    Properties:
      ClusterName: !Sub ${Environment}-serp-surfer

  # Application Load Balancer
  ALB:
    Type: AWS::ElasticLoadBalancingV2::LoadBalancer
    Properties:
      Name: !Sub ${Environment}-serp-surfer-alb
      Subnets:
        - !Ref PublicSubnet1
        - !Ref PublicSubnet2
      SecurityGroups:
        - !Ref ALBSecurityGroup

  # ECS Service
  ECSService:
    Type: AWS::ECS::Service
    Properties:
      ServiceName: web
      Cluster: !Ref ECSCluster
      TaskDefinition: !Ref ECSTaskDefinition
      DesiredCount: 2
      LaunchType: FARGATE
      NetworkConfiguration:
        AwsvpcConfiguration:
          Subnets:
            - !Ref PrivateSubnet1
            - !Ref PrivateSubnet2
          SecurityGroups:
            - !Ref ECSSecurityGroup
      LoadBalancers:
        - ContainerName: web
          ContainerPort: 3000
          TargetGroupArn: !Ref TargetGroup

Outputs:
  LoadBalancerDNS:
    Description: DNS name of the load balancer
    Value: !GetAtt ALB.DNSName

  DatabaseEndpoint:
    Description: Database endpoint
    Value: !GetAtt Database.Endpoint.Address

  RedisEndpoint:
    Description: Redis endpoint
    Value: !GetAtt RedisCluster.RedisEndpoint.Address
```

---

## Docker Production

### Production Dockerfile

**File**: `Dockerfile.production`

```dockerfile
# Multi-stage build for optimal image size

# Stage 1: Dependencies
FROM node:20-alpine AS deps
RUN apk add --no-cache libc6-compat openssl
WORKDIR /app

# Install pnpm
RUN corepack enable pnpm

# Copy package files
COPY package.json pnpm-lock.yaml ./
COPY prisma ./prisma/

# Install dependencies
RUN pnpm install --frozen-lockfile --prod=false

# Generate Prisma client
RUN pnpm prisma generate

# Stage 2: Builder
FROM node:20-alpine AS builder
RUN apk add --no-cache libc6-compat openssl
WORKDIR /app

RUN corepack enable pnpm

COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Set environment for build
ENV NEXT_TELEMETRY_DISABLED 1
ENV SKIP_ENV_VALIDATION 1

# Build application
RUN pnpm build

# Stage 3: Runner
FROM node:20-alpine AS runner
WORKDIR /app

ENV NODE_ENV production
ENV NEXT_TELEMETRY_DISABLED 1

RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nextjs

# Copy necessary files
COPY --from=builder /app/public ./public
COPY --from=builder /app/.next/standalone ./
COPY --from=builder /app/.next/static ./.next/static
COPY --from=builder /app/prisma ./prisma
COPY --from=builder /app/node_modules/.prisma ./node_modules/.prisma
COPY --from=builder /app/node_modules/@prisma ./node_modules/@prisma

# Set correct permissions
RUN chown -R nextjs:nodejs /app

USER nextjs

EXPOSE 3000

ENV PORT 3000
ENV HOSTNAME "0.0.0.0"

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
  CMD node -e "require('http').get('http://localhost:3000/api/health', (r) => {process.exit(r.statusCode === 200 ? 0 : 1)})"

CMD ["node", "server.js"]
```

### Docker Compose Production

**File**: `docker-compose.prod.yml`

```yaml
version: "3.8"

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.production
    ports:
      - "3000:3000"
    environment:
      - NODE_ENV=production
      - DATABASE_URL=postgresql://postgres:${DB_PASSWORD}@db:5432/serpsurfer
      - REDIS_URL=redis://redis:6379
      - NEXTAUTH_SECRET=${NEXTAUTH_SECRET}
      - NEXTAUTH_URL=${NEXTAUTH_URL}
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_healthy
    restart: unless-stopped
    networks:
      - serpsurfer-network

  worker:
    build:
      context: .
      dockerfile: Dockerfile.production
    command: node workers/index.js
    environment:
      - NODE_ENV=production
      - DATABASE_URL=postgresql://postgres:${DB_PASSWORD}@db:5432/serpsurfer
      - REDIS_URL=redis://redis:6379
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_healthy
    restart: unless-stopped
    networks:
      - serpsurfer-network
    deploy:
      replicas: 2

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile.production
    command: node workers/scheduler.js
    environment:
      - NODE_ENV=production
      - DATABASE_URL=postgresql://postgres:${DB_PASSWORD}@db:5432/serpsurfer
      - REDIS_URL=redis://redis:6379
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_healthy
    restart: unless-stopped
    networks:
      - serpsurfer-network

  db:
    image: postgres:15-alpine
    environment:
      - POSTGRES_USER=postgres
      - POSTGRES_PASSWORD=${DB_PASSWORD}
      - POSTGRES_DB=serpsurfer
      - PGDATA=/var/lib/postgresql/data/pgdata
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres"]
      interval: 10s
      timeout: 5s
      retries: 5
    restart: unless-stopped
    networks:
      - serpsurfer-network

  redis:
    image: redis:7-alpine
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data
    ports:
      - "6379:6379"
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 3s
      retries: 5
    restart: unless-stopped
    networks:
      - serpsurfer-network

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
      - ./ssl:/etc/nginx/ssl:ro
    depends_on:
      - app
    restart: unless-stopped
    networks:
      - serpsurfer-network

volumes:
  postgres_data:
  redis_data:

networks:
  serpsurfer-network:
    driver: bridge
```

This comprehensive CI/CD and deployment guide covers all major platforms and provides production-ready configurations.
