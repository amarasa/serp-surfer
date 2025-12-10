# SERP Surfer - Next.js Rewrite Specification

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [Technology Stack](#technology-stack)
3. [System Architecture](#system-architecture)
4. [Database Schema](#database-schema)
5. [Authentication & Authorization](#authentication--authorization)
6. [API Endpoints](#api-endpoints)
7. [Background Jobs & Scheduling](#background-jobs--scheduling)
8. [Frontend Architecture](#frontend-architecture)
9. [Core Features Specification](#core-features-specification)
10. [Google API Integration](#google-api-integration)
11. [Email System](#email-system)
12. [Admin Panel](#admin-panel)
13. [Queue System](#queue-system)
14. [Deployment & Infrastructure](#deployment--infrastructure)
15. [Migration Strategy](#migration-strategy)

---

## Executive Summary

This specification outlines the complete rewrite of SERP Surfer from Laravel/PHP to Next.js/JavaScript (TypeScript). The application will maintain feature parity while leveraging modern JavaScript ecosystem tools and best practices.

**Project Goals:**
- Full feature parity with existing Laravel application
- Improved performance with React Server Components
- Modern TypeScript-first development
- Enhanced developer experience
- Scalable architecture for future growth

---

## Technology Stack

### Frontend
- **Framework**: Next.js 14+ (App Router)
- **Language**: TypeScript 5+
- **UI Components**: shadcn/ui (built on Radix UI)
- **Styling**: TailwindCSS 3+
- **State Management**:
  - Server State: TanStack Query (React Query)
  - Client State: Zustand or Jotai
- **Forms**: React Hook Form + Zod validation
- **Email Builder**: GrapesJS (same as current)
- **Data Tables**: TanStack Table
- **Charts**: Recharts or Chart.js
- **Icons**: Lucide React or Heroicons

### Backend (Next.js API Routes + Separate Services)
- **API Framework**: Next.js API Routes (App Router)
- **Runtime**: Node.js 20+
- **Language**: TypeScript
- **ORM**: Prisma or Drizzle ORM
- **Authentication**: NextAuth.js v5 (Auth.js)
- **Validation**: Zod
- **HTTP Client**: Axios or native fetch

### Database
- **Primary DB**: PostgreSQL 15+ (recommended over SQLite for production)
- **Alternative**: MySQL 8.0+ or SQLite (development)
- **Cache/Session**: Redis
- **Queue Backend**: Redis + BullMQ

### Background Jobs & Scheduling
- **Queue System**: BullMQ (Redis-based)
- **Job Monitoring**: Bull Board (equivalent to Horizon)
- **Scheduler**: node-cron or BullMQ's built-in repeatable jobs
- **Worker Process**: Separate Node.js process(es)

### Google APIs
- **Google API Client**: googleapis (official Node.js client)
- **OAuth 2.0**: NextAuth.js Google Provider + custom token management
- **APIs Used**:
  - Google Search Console API
  - Google Indexing API
  - Google OAuth 2.0

### Email
- **Email Service**: Resend, SendGrid, or AWS SES
- **Template Engine**: React Email or MJML
- **Email Builder**: GrapesJS (frontend only)

### Development Tools
- **Package Manager**: pnpm (recommended) or npm
- **Linting**: ESLint + TypeScript ESLint
- **Formatting**: Prettier
- **Testing**:
  - Unit: Vitest or Jest
  - E2E: Playwright
  - API: Supertest
- **Git Hooks**: Husky + lint-staged

### DevOps & Deployment
- **Containerization**: Docker + Docker Compose
- **Hosting Options**:
  - Vercel (recommended for Next.js)
  - Railway
  - AWS (ECS/Fargate)
  - DigitalOcean App Platform
- **CI/CD**: GitHub Actions
- **Monitoring**: Sentry, LogRocket, or Axiom
- **Logging**: Pino or Winston

---

## System Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         Client (Browser)                     │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │           Next.js Frontend (React Components)          │ │
│  │  - SSR/SSG pages with App Router                       │ │
│  │  - Client components for interactivity                 │ │
│  │  - TanStack Query for data fetching                    │ │
│  └────────────────────────────────────────────────────────┘ │
└───────────────────────────┬──────────────────────────────────┘
                            │ HTTPS
                            │
┌───────────────────────────▼──────────────────────────────────┐
│                     Next.js Application                       │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              App Router (Route Handlers)             │   │
│  │  - API Routes (/api/*)                               │   │
│  │  - Server Actions (form submissions, mutations)      │   │
│  │  - Middleware (auth, rate limiting)                  │   │
│  └─────────────────────────────────────────────────────┘   │
│                            │                                 │
│  ┌─────────────────────────▼─────────────────────────────┐ │
│  │              Business Logic Layer                     │ │
│  │  - Service classes                                    │ │
│  │  - Data access layer (Prisma/Drizzle)                │ │
│  │  - External API integrations                         │ │
│  └─────────────────────────────────────────────────────┘   │
└───────────────────────────┬──────────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
┌───────▼────────┐  ┌──────▼──────┐  ┌────────▼─────────┐
│   PostgreSQL   │  │    Redis    │  │  Google APIs     │
│   Database     │  │ - Cache     │  │ - Search Console │
│                │  │ - Sessions  │  │ - Indexing API   │
│                │  │ - Queues    │  │ - OAuth 2.0      │
└────────────────┘  └──────┬──────┘  └──────────────────┘
                           │
                    ┌──────▼──────┐
                    │  BullMQ     │
                    │  Workers    │
                    │             │
                    │ - Sitemap   │
                    │   Scanner   │
                    │ - URL Check │
                    │ - Indexing  │
                    │ - Cleanup   │
                    └─────────────┘
```

### Application Structure

```
serp-surfer-nextjs/
├── src/
│   ├── app/                          # Next.js App Router
│   │   ├── (auth)/                   # Auth route group
│   │   │   ├── login/
│   │   │   ├── register/
│   │   │   └── layout.tsx
│   │   ├── (dashboard)/              # Protected routes
│   │   │   ├── dashboard/
│   │   │   ├── sitemaps/
│   │   │   ├── urls/
│   │   │   ├── queue/
│   │   │   └── layout.tsx
│   │   ├── (admin)/                  # Admin routes
│   │   │   ├── admin/
│   │   │   │   ├── users/
│   │   │   │   ├── sitemaps/
│   │   │   │   ├── workers/
│   │   │   │   ├── server/
│   │   │   │   └── emails/
│   │   │   └── layout.tsx
│   │   ├── api/                      # API Routes
│   │   │   ├── auth/
│   │   │   │   └── [...nextauth]/
│   │   │   ├── google/
│   │   │   │   ├── callback/
│   │   │   │   ├── sitemaps/
│   │   │   │   └── refresh-token/
│   │   │   ├── sitemaps/
│   │   │   │   ├── route.ts
│   │   │   │   ├── [id]/
│   │   │   │   └── [id]/urls/
│   │   │   ├── urls/
│   │   │   ├── queue/
│   │   │   ├── admin/
│   │   │   │   ├── users/
│   │   │   │   ├── sitemaps/
│   │   │   │   ├── workers/
│   │   │   │   └── jobs/
│   │   │   └── webhooks/
│   │   ├── layout.tsx
│   │   └── page.tsx
│   │
│   ├── components/                   # React components
│   │   ├── ui/                       # shadcn/ui components
│   │   │   ├── button.tsx
│   │   │   ├── input.tsx
│   │   │   ├── table.tsx
│   │   │   └── ...
│   │   ├── auth/
│   │   │   ├── login-form.tsx
│   │   │   └── register-form.tsx
│   │   ├── dashboard/
│   │   │   ├── sitemap-list.tsx
│   │   │   ├── url-table.tsx
│   │   │   └── stats-card.tsx
│   │   ├── admin/
│   │   │   ├── user-table.tsx
│   │   │   ├── worker-manager.tsx
│   │   │   └── email-builder.tsx
│   │   └── shared/
│   │       ├── header.tsx
│   │       ├── sidebar.tsx
│   │       └── footer.tsx
│   │
│   ├── lib/                          # Utility functions & configs
│   │   ├── auth.ts                   # NextAuth configuration
│   │   ├── db.ts                     # Database client (Prisma)
│   │   ├── redis.ts                  # Redis client
│   │   ├── google/
│   │   │   ├── auth.ts               # Google OAuth helpers
│   │   │   ├── search-console.ts    # Search Console API
│   │   │   └── indexing.ts          # Indexing API
│   │   ├── queue/
│   │   │   ├── client.ts             # BullMQ client
│   │   │   ├── jobs/
│   │   │   │   ├── process-url.ts
│   │   │   │   ├── scan-sitemap.ts
│   │   │   │   ├── submit-indexing.ts
│   │   │   │   ├── check-status.ts
│   │   │   │   └── cleanup.ts
│   │   │   └── workers/
│   │   │       └── index.ts
│   │   ├── email/
│   │   │   ├── client.ts
│   │   │   └── templates/
│   │   ├── utils.ts
│   │   └── validations/              # Zod schemas
│   │       ├── sitemap.ts
│   │       ├── url.ts
│   │       └── user.ts
│   │
│   ├── services/                     # Business logic
│   │   ├── sitemap.service.ts
│   │   ├── url.service.ts
│   │   ├── queue.service.ts
│   │   ├── user.service.ts
│   │   └── google.service.ts
│   │
│   ├── types/                        # TypeScript type definitions
│   │   ├── index.ts
│   │   ├── google.ts
│   │   ├── database.ts
│   │   └── api.ts
│   │
│   └── middleware.ts                 # Next.js middleware
│
├── prisma/                           # Database schema & migrations
│   ├── schema.prisma
│   ├── migrations/
│   └── seed.ts
│
├── workers/                          # Background job workers
│   ├── index.ts                      # Main worker process
│   ├── processors/
│   │   ├── process-url.ts
│   │   ├── scan-sitemap.ts
│   │   ├── submit-indexing.ts
│   │   ├── check-status.ts
│   │   └── cleanup.ts
│   └── scheduler.ts                  # Cron job scheduler
│
├── public/                           # Static assets
│   ├── images/
│   └── favicon.ico
│
├── tests/                            # Test files
│   ├── unit/
│   ├── integration/
│   └── e2e/
│
├── .env.example
├── .env.local
├── docker-compose.yml
├── Dockerfile
├── next.config.js
├── package.json
├── tsconfig.json
├── tailwind.config.ts
└── README.md
```

---

## Database Schema

### Prisma Schema (schema.prisma)

```prisma
// This is your Prisma schema file

generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "postgresql" // or "mysql" or "sqlite"
  url      = env("DATABASE_URL")
}

// ============================================================================
// USER & AUTHENTICATION
// ============================================================================

model User {
  id                String    @id @default(cuid())
  name              String?
  email             String    @unique
  emailVerified     DateTime? @map("email_verified")
  password          String?   // Hashed password (optional if using OAuth only)
  image             String?

  // Google OAuth tokens
  googleAccessToken  String?  @map("google_access_token") @db.Text
  googleRefreshToken String?  @map("google_refresh_token") @db.Text
  googleTokenExpiry  DateTime? @map("google_token_expiry")

  // Status
  status            UserStatus @default(ACTIVE)
  isSuspended       Boolean   @default(false) @map("is_suspended")

  // Relationships
  role              Role      @relation(fields: [roleId], references: [id])
  roleId            String    @map("role_id")
  accounts          Account[]
  sessions          Session[]
  sitemaps          Sitemap[]
  urlLists          UrlList[]
  indexQueues       IndexQueue[]

  createdAt         DateTime  @default(now()) @map("created_at")
  updatedAt         DateTime  @updatedAt @map("updated_at")

  @@map("users")
}

enum UserStatus {
  ACTIVE
  SUSPENDED
  DELETED
}

model Role {
  id          String   @id @default(cuid())
  name        String   @unique // "admin" or "user"
  description String?
  users       User[]

  createdAt   DateTime @default(now()) @map("created_at")
  updatedAt   DateTime @updatedAt @map("updated_at")

  @@map("roles")
}

model Account {
  id                String  @id @default(cuid())
  userId            String  @map("user_id")
  type              String
  provider          String
  providerAccountId String  @map("provider_account_id")
  refresh_token     String? @db.Text
  access_token      String? @db.Text
  expires_at        Int?
  token_type        String?
  scope             String?
  id_token          String? @db.Text
  session_state     String?

  user User @relation(fields: [userId], references: [id], onDelete: Cascade)

  @@unique([provider, providerAccountId])
  @@map("accounts")
}

model Session {
  id           String   @id @default(cuid())
  sessionToken String   @unique @map("session_token")
  userId       String   @map("user_id")
  expires      DateTime
  user         User     @relation(fields: [userId], references: [id], onDelete: Cascade)

  @@map("sessions")
}

model VerificationToken {
  identifier String
  token      String   @unique
  expires    DateTime

  @@unique([identifier, token])
  @@map("verification_tokens")
}

// ============================================================================
// SITEMAPS
// ============================================================================

model Sitemap {
  id              String    @id @default(cuid())
  userId          String    @map("user_id")
  url             String    @db.Text
  type            String?   // "sitemap" or "sitemap_index"

  // Google Search Console data
  gscSiteUrl      String?   @map("gsc_site_url") @db.Text
  path            String?   @db.Text
  lastSubmitted   DateTime? @map("last_submitted")
  lastDownloaded  DateTime? @map("last_downloaded")
  warnings        BigInt?   @default(0)
  errors          BigInt?   @default(0)
  isPending       Boolean   @default(false) @map("is_pending")
  isSitemapsIndex Boolean   @default(false) @map("is_sitemaps_index")

  // Auto-scan configuration
  autoScan        Boolean   @default(false) @map("auto_scan")

  // Relationships
  user            User      @relation(fields: [userId], references: [id], onDelete: Cascade)
  urls            SitemapUrl[]

  createdAt       DateTime  @default(now()) @map("created_at")
  updatedAt       DateTime  @updatedAt @map("updated_at")

  @@index([userId])
  @@index([autoScan])
  @@map("sitemaps")
}

model SitemapUrl {
  id              String    @id @default(cuid())
  sitemapId       String    @map("sitemap_id")
  url             String    @db.Text
  priority        Float?
  changefreq      String?   @map("change_freq")
  lastmod         DateTime?

  // Indexing status
  indexed         Boolean   @default(false)
  inspectionUrl   String?   @map("inspection_url") @db.Text

  // Relationships
  sitemap         Sitemap   @relation(fields: [sitemapId], references: [id], onDelete: Cascade)

  createdAt       DateTime  @default(now()) @map("created_at")
  updatedAt       DateTime  @updatedAt @map("updated_at")

  @@index([sitemapId])
  @@index([url])
  @@index([indexed])
  @@map("sitemap_urls")
}

// ============================================================================
// URL TRACKING
// ============================================================================

model UrlList {
  id          String    @id @default(cuid())
  userId      String    @map("user_id")
  url         String    @db.Text
  indexed     Boolean   @default(false)
  lastSeen    DateTime? @map("last_seen")

  // Google Search Console data
  verdict     String?   // "PASS", "FAIL", etc.
  coverageState String? @map("coverage_state") @db.Text
  robotsTxtState String? @map("robots_txt_state") @db.Text
  indexingState  String? @map("indexing_state") @db.Text
  pageFetchState String? @map("page_fetch_state") @db.Text
  crawledAs      String? @map("crawled_as")
  googleCanonical String? @map("google_canonical") @db.Text
  userCanonical   String? @map("user_canonical") @db.Text
  referringUrls   String? @map("referring_urls") @db.Text // JSON array

  // Relationships
  user        User      @relation(fields: [userId], references: [id], onDelete: Cascade)

  createdAt   DateTime  @default(now()) @map("created_at")
  updatedAt   DateTime  @updatedAt @map("updated_at")

  @@index([userId])
  @@index([url])
  @@index([indexed])
  @@index([lastSeen])
  @@map("url_list")
}

// ============================================================================
// QUEUE SYSTEM
// ============================================================================

model IndexQueue {
  id          String    @id @default(cuid())
  userId      String    @map("user_id")
  url         String    @db.Text
  type        QueueType // "sitemap", "url", "index_submission"
  status      QueueStatus @default(PENDING)

  // Metadata
  sitemapId   String?   @map("sitemap_id")
  priority    Int       @default(0)
  attempts    Int       @default(0)
  maxAttempts Int       @default(3) @map("max_attempts")

  // Error handling
  error       String?   @db.Text
  lastAttempt DateTime? @map("last_attempt")

  // Relationships
  user        User      @relation(fields: [userId], references: [id], onDelete: Cascade)

  processedAt DateTime? @map("processed_at")
  createdAt   DateTime  @default(now()) @map("created_at")
  updatedAt   DateTime  @updatedAt @map("updated_at")

  @@index([userId])
  @@index([status])
  @@index([type])
  @@index([createdAt])
  @@map("index_queue")
}

enum QueueType {
  SITEMAP          // Process sitemap
  URL              // Check URL status
  INDEX_SUBMISSION // Submit to Google Indexing API
}

enum QueueStatus {
  PENDING
  PROCESSING
  COMPLETED
  FAILED
}

// ============================================================================
// INDEXING RESULTS
// ============================================================================

model IndexingResult {
  id                String    @id @default(cuid())
  url               String    @db.Text
  status            String    // "URL_UPDATED", "URL_DELETED"

  // Google API response
  metadata          String?   @db.Text // JSON metadata from Google

  createdAt         DateTime  @default(now()) @map("created_at")

  @@index([url])
  @@index([createdAt])
  @@map("indexing_results")
}

// ============================================================================
// SERVICE WORKERS (Google Service Accounts)
// ============================================================================

model ServiceWorker {
  id                String    @id @default(cuid())
  name              String
  email             String    @unique

  // Service account credentials (JSON key file)
  credentials       String    @db.Text // Store encrypted or use secret manager

  // Usage tracking
  dailyQuota        Int       @default(200) @map("daily_quota")
  usedQuota         Int       @default(0) @map("used_quota")
  lastUsed          DateTime? @map("last_used")
  quotaResetAt      DateTime  @default(now()) @map("quota_reset_at")

  // Status
  isActive          Boolean   @default(true) @map("is_active")

  createdAt         DateTime  @default(now()) @map("created_at")
  updatedAt         DateTime  @updatedAt @map("updated_at")

  @@map("service_workers")
}

// ============================================================================
// EMAIL TEMPLATES
// ============================================================================

model EmailTemplate {
  id          String    @id @default(cuid())
  name        String
  subject     String
  htmlContent String    @map("html_content") @db.Text
  jsonContent String?   @map("json_content") @db.Text // GrapesJS JSON
  type        EmailType

  createdAt   DateTime  @default(now()) @map("created_at")
  updatedAt   DateTime  @updatedAt @map("updated_at")

  @@map("email_templates")
}

enum EmailType {
  SUCCESS_NOTIFICATION
  BUG_REPORT
  NEWSLETTER
  CUSTOM
}

// ============================================================================
// JOB HISTORY (Optional - for monitoring)
// ============================================================================

model JobHistory {
  id          String    @id @default(cuid())
  jobName     String    @map("job_name")
  status      String    // "completed", "failed"
  duration    Int?      // milliseconds
  error       String?   @db.Text

  startedAt   DateTime  @map("started_at")
  completedAt DateTime? @map("completed_at")

  @@index([jobName])
  @@index([startedAt])
  @@map("job_history")
}
```

### Database Relationships Summary

```
User (1) ──────── (M) Sitemap
User (1) ──────── (M) UrlList
User (1) ──────── (M) IndexQueue
User (M) ──────── (1) Role

Sitemap (1) ──── (M) SitemapUrl
```

---

## Authentication & Authorization

### NextAuth.js Configuration

**File**: `src/lib/auth.ts`

```typescript
import { NextAuthOptions } from "next-auth";
import { PrismaAdapter } from "@auth/prisma-adapter";
import GoogleProvider from "next-auth/providers/google";
import CredentialsProvider from "next-auth/providers/credentials";
import { prisma } from "@/lib/db";
import bcrypt from "bcryptjs";

export const authOptions: NextAuthOptions = {
  adapter: PrismaAdapter(prisma),
  session: {
    strategy: "jwt",
  },
  pages: {
    signIn: "/login",
    error: "/login",
  },
  providers: [
    GoogleProvider({
      clientId: process.env.GOOGLE_CLIENT_ID!,
      clientSecret: process.env.GOOGLE_CLIENT_SECRET!,
      authorization: {
        params: {
          scope: [
            "openid",
            "email",
            "profile",
            "https://www.googleapis.com/auth/webmasters",
            "https://www.googleapis.com/auth/indexing",
          ].join(" "),
          access_type: "offline",
          prompt: "consent",
        },
      },
    }),
    CredentialsProvider({
      name: "credentials",
      credentials: {
        email: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" },
      },
      async authorize(credentials) {
        if (!credentials?.email || !credentials?.password) {
          throw new Error("Invalid credentials");
        }

        const user = await prisma.user.findUnique({
          where: { email: credentials.email },
          include: { role: true },
        });

        if (!user || !user.password) {
          throw new Error("Invalid credentials");
        }

        if (user.isSuspended) {
          throw new Error("Account is suspended");
        }

        const isValid = await bcrypt.compare(
          credentials.password,
          user.password
        );

        if (!isValid) {
          throw new Error("Invalid credentials");
        }

        return {
          id: user.id,
          email: user.email,
          name: user.name,
          image: user.image,
          role: user.role.name,
        };
      },
    }),
  ],
  callbacks: {
    async signIn({ user, account, profile }) {
      if (account?.provider === "google") {
        // Store Google tokens
        await prisma.user.update({
          where: { email: user.email! },
          data: {
            googleAccessToken: account.access_token,
            googleRefreshToken: account.refresh_token,
            googleTokenExpiry: account.expires_at
              ? new Date(account.expires_at * 1000)
              : null,
          },
        });
      }
      return true;
    },
    async jwt({ token, user, account, trigger, session }) {
      if (user) {
        token.id = user.id;
        token.role = user.role;
      }

      // Handle token refresh
      if (trigger === "update" && session) {
        token = { ...token, ...session };
      }

      return token;
    },
    async session({ session, token }) {
      if (token) {
        session.user.id = token.id as string;
        session.user.role = token.role as string;
      }
      return session;
    },
  },
};
```

### Google Token Management

**File**: `src/lib/google/auth.ts`

```typescript
import { google } from "googleapis";
import { prisma } from "@/lib/db";

export async function getGoogleClient(userId: string) {
  const user = await prisma.user.findUnique({
    where: { id: userId },
  });

  if (!user?.googleRefreshToken) {
    throw new Error("No Google credentials found");
  }

  const oauth2Client = new google.auth.OAuth2(
    process.env.GOOGLE_CLIENT_ID,
    process.env.GOOGLE_CLIENT_SECRET,
    process.env.GOOGLE_REDIRECT_URI
  );

  oauth2Client.setCredentials({
    access_token: user.googleAccessToken,
    refresh_token: user.googleRefreshToken,
    expiry_date: user.googleTokenExpiry?.getTime(),
  });

  // Auto-refresh token
  oauth2Client.on("tokens", async (tokens) => {
    if (tokens.refresh_token) {
      await prisma.user.update({
        where: { id: userId },
        data: {
          googleAccessToken: tokens.access_token,
          googleRefreshToken: tokens.refresh_token,
          googleTokenExpiry: tokens.expiry_date
            ? new Date(tokens.expiry_date)
            : null,
        },
      });
    }
  });

  return oauth2Client;
}

export async function refreshGoogleToken(userId: string) {
  const client = await getGoogleClient(userId);
  const { credentials } = await client.refreshAccessToken();

  await prisma.user.update({
    where: { id: userId },
    data: {
      googleAccessToken: credentials.access_token,
      googleTokenExpiry: credentials.expiry_date
        ? new Date(credentials.expiry_date)
        : null,
    },
  });

  return credentials.access_token;
}
```

### Authorization Middleware

**File**: `src/middleware.ts`

```typescript
import { withAuth } from "next-auth/middleware";
import { NextResponse } from "next/server";

export default withAuth(
  function middleware(req) {
    const token = req.nextauth.token;
    const isAdmin = token?.role === "admin";
    const isAdminRoute = req.nextUrl.pathname.startsWith("/admin");

    if (isAdminRoute && !isAdmin) {
      return NextResponse.redirect(new URL("/dashboard", req.url));
    }

    return NextResponse.next();
  },
  {
    callbacks: {
      authorized: ({ token }) => !!token,
    },
  }
);

export const config = {
  matcher: [
    "/dashboard/:path*",
    "/admin/:path*",
    "/sitemaps/:path*",
    "/urls/:path*",
    "/queue/:path*",
    "/api/((?!auth).)*",
  ],
};
```

---

## API Endpoints

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/signup` | Register new user | No |
| POST | `/api/auth/signin` | Login (credentials) | No |
| POST | `/api/auth/signout` | Logout | Yes |
| GET | `/api/auth/session` | Get current session | No |
| GET | `/api/google/callback` | Google OAuth callback | No |
| POST | `/api/google/refresh-token` | Refresh Google token | Yes |

### User Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/user/profile` | Get user profile | Yes |
| PUT | `/api/user/profile` | Update user profile | Yes |
| PUT | `/api/user/password` | Change password | Yes |
| DELETE | `/api/user/account` | Delete account | Yes |

### Sitemap Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/sitemaps` | List user's sitemaps | Yes |
| POST | `/api/sitemaps` | Create/import sitemap | Yes |
| GET | `/api/sitemaps/[id]` | Get sitemap details | Yes |
| PUT | `/api/sitemaps/[id]` | Update sitemap settings | Yes |
| DELETE | `/api/sitemaps/[id]` | Delete sitemap | Yes |
| POST | `/api/sitemaps/[id]/scan` | Queue sitemap for scanning | Yes |
| GET | `/api/sitemaps/[id]/urls` | Get URLs from sitemap | Yes |
| PUT | `/api/sitemaps/[id]/auto-scan` | Toggle auto-scan | Yes |
| GET | `/api/google/sitemaps` | Fetch sitemaps from GSC | Yes |

### URL Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/urls` | List all tracked URLs | Yes |
| POST | `/api/urls` | Add URL to tracking | Yes |
| GET | `/api/urls/[id]` | Get URL details | Yes |
| DELETE | `/api/urls/[id]` | Remove URL from tracking | Yes |
| POST | `/api/urls/check` | Check URL status | Yes |
| GET | `/api/urls/stats` | Get URL statistics | Yes |

### Queue Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/queue` | List queue items | Yes |
| POST | `/api/queue` | Add item to queue | Yes |
| GET | `/api/queue/[id]` | Get queue item | Yes |
| DELETE | `/api/queue/[id]` | Remove from queue | Yes |
| POST | `/api/queue/[id]/retry` | Retry failed item | Yes |
| GET | `/api/queue/stats` | Queue statistics | Yes |

### Admin Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/admin/users` | List all users | Admin |
| GET | `/api/admin/users/[id]` | Get user details | Admin |
| PUT | `/api/admin/users/[id]` | Update user | Admin |
| DELETE | `/api/admin/users/[id]` | Delete user | Admin |
| POST | `/api/admin/users/[id]/suspend` | Suspend user | Admin |
| POST | `/api/admin/users/[id]/unsuspend` | Unsuspend user | Admin |
| POST | `/api/admin/users/[id]/reset-password` | Force password reset | Admin |
| GET | `/api/admin/sitemaps` | List all sitemaps | Admin |
| PUT | `/api/admin/sitemaps/[id]` | Update sitemap | Admin |
| GET | `/api/admin/workers` | List service workers | Admin |
| POST | `/api/admin/workers` | Add service worker | Admin |
| PUT | `/api/admin/workers/[id]` | Update worker | Admin |
| DELETE | `/api/admin/workers/[id]` | Delete worker | Admin |
| POST | `/api/admin/jobs/run` | Manually run job | Admin |
| POST | `/api/admin/cache/clear` | Clear cache | Admin |
| GET | `/api/admin/stats` | System statistics | Admin |

### Email Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/admin/emails/send` | Send email to users | Admin |
| GET | `/api/admin/emails/templates` | List templates | Admin |
| POST | `/api/admin/emails/templates` | Create template | Admin |
| PUT | `/api/admin/emails/templates/[id]` | Update template | Admin |
| DELETE | `/api/admin/emails/templates/[id]` | Delete template | Admin |

---

## Background Jobs & Scheduling

### BullMQ Queue Configuration

**File**: `src/lib/queue/client.ts`

```typescript
import { Queue, QueueEvents } from "bullmq";
import { redis } from "@/lib/redis";

// Define queue names
export enum QueueName {
  URL_PROCESSING = "url-processing",
  SITEMAP_SCAN = "sitemap-scan",
  INDEXING_SUBMISSION = "indexing-submission",
  STATUS_CHECK = "status-check",
  CLEANUP = "cleanup",
  EMAIL = "email",
}

// Queue options
const defaultQueueOptions = {
  connection: redis,
  defaultJobOptions: {
    attempts: 3,
    backoff: {
      type: "exponential" as const,
      delay: 2000,
    },
    removeOnComplete: {
      count: 100,
      age: 24 * 3600, // 24 hours
    },
    removeOnFail: {
      count: 1000,
      age: 7 * 24 * 3600, // 7 days
    },
  },
};

// Create queues
export const urlProcessingQueue = new Queue(
  QueueName.URL_PROCESSING,
  defaultQueueOptions
);

export const sitemapScanQueue = new Queue(
  QueueName.SITEMAP_SCAN,
  defaultQueueOptions
);

export const indexingSubmissionQueue = new Queue(
  QueueName.INDEXING_SUBMISSION,
  defaultQueueOptions
);

export const statusCheckQueue = new Queue(
  QueueName.STATUS_CHECK,
  defaultQueueOptions
);

export const cleanupQueue = new Queue(
  QueueName.CLEANUP,
  defaultQueueOptions
);

export const emailQueue = new Queue(
  QueueName.EMAIL,
  defaultQueueOptions
);

// Queue events for monitoring
export const queueEvents = new QueueEvents(QueueName.URL_PROCESSING, {
  connection: redis,
});
```

### Job Definitions

#### 1. Process Queued URL Job

**File**: `src/lib/queue/jobs/process-url.ts`

```typescript
import { Job } from "bullmq";
import { prisma } from "@/lib/db";
import axios from "axios";
import * as cheerio from "cheerio";

export interface ProcessUrlData {
  queueId: string;
  url: string;
  userId: string;
}

export async function processUrl(job: Job<ProcessUrlData>) {
  const { queueId, url, userId } = job.data;

  try {
    // Update queue status
    await prisma.indexQueue.update({
      where: { id: queueId },
      data: { status: "PROCESSING", lastAttempt: new Date() },
    });

    // Scrape Google search results
    const searchUrl = `https://www.google.com/search?q=${encodeURIComponent(url)}`;
    const response = await axios.get(searchUrl, {
      headers: {
        "User-Agent": "Mozilla/5.0 (compatible; SERPSurfer/1.0)",
      },
    });

    const $ = cheerio.load(response.data);
    const isIndexed = $(`a[href*="${url}"]`).length > 0;

    // Update or create URL in tracking list
    await prisma.urlList.upsert({
      where: {
        userId_url: {
          userId,
          url,
        },
      },
      update: {
        indexed: isIndexed,
        lastSeen: new Date(),
      },
      create: {
        userId,
        url,
        indexed: isIndexed,
        lastSeen: new Date(),
      },
    });

    // Update queue item
    await prisma.indexQueue.update({
      where: { id: queueId },
      data: {
        status: "COMPLETED",
        processedAt: new Date(),
      },
    });

    return { success: true, indexed: isIndexed };
  } catch (error) {
    const attempts = (await prisma.indexQueue.findUnique({
      where: { id: queueId },
    }))!.attempts + 1;

    await prisma.indexQueue.update({
      where: { id: queueId },
      data: {
        status: attempts >= 3 ? "FAILED" : "PENDING",
        attempts,
        error: error instanceof Error ? error.message : "Unknown error",
      },
    });

    throw error;
  }
}
```

#### 2. Auto-Scan Sitemaps Job

**File**: `src/lib/queue/jobs/scan-sitemap.ts`

```typescript
import { Job } from "bullmq";
import { prisma } from "@/lib/db";
import axios from "axios";
import { XMLParser } from "fast-xml-parser";

export interface ScanSitemapData {
  sitemapId: string;
  userId: string;
  url: string;
}

export async function scanSitemap(job: Job<ScanSitemapData>) {
  const { sitemapId, userId, url } = job.data;

  try {
    // Fetch sitemap XML
    const response = await axios.get(url);
    const parser = new XMLParser();
    const parsed = parser.parse(response.data);

    const urlset = parsed.urlset || parsed.sitemapindex;
    if (!urlset) {
      throw new Error("Invalid sitemap format");
    }

    const urls = Array.isArray(urlset.url) ? urlset.url : [urlset.url];

    // Process each URL
    for (const urlData of urls) {
      const loc = urlData.loc;
      const lastmod = urlData.lastmod ? new Date(urlData.lastmod) : null;
      const changefreq = urlData.changefreq;
      const priority = urlData.priority ? parseFloat(urlData.priority) : null;

      // Check if URL already exists
      const existing = await prisma.sitemapUrl.findFirst({
        where: {
          sitemapId,
          url: loc,
        },
      });

      if (!existing) {
        // Create new URL
        await prisma.sitemapUrl.create({
          data: {
            sitemapId,
            url: loc,
            lastmod,
            changefreq,
            priority,
          },
        });

        // Add to URL tracking list
        await prisma.urlList.create({
          data: {
            userId,
            url: loc,
            indexed: false,
          },
        });

        // Queue for status check
        await prisma.indexQueue.create({
          data: {
            userId,
            url: loc,
            type: "URL",
            sitemapId,
          },
        });
      }
    }

    // Update sitemap
    await prisma.sitemap.update({
      where: { id: sitemapId },
      data: {
        lastDownloaded: new Date(),
      },
    });

    return { success: true, urlsProcessed: urls.length };
  } catch (error) {
    throw error;
  }
}
```

#### 3. Submit URLs for Indexing Job

**File**: `src/lib/queue/jobs/submit-indexing.ts`

```typescript
import { Job } from "bullmq";
import { prisma } from "@/lib/db";
import { getAvailableServiceWorker } from "@/services/worker.service";
import { google } from "googleapis";

export interface SubmitIndexingData {
  url: string;
  userId: string;
}

export async function submitForIndexing(job: Job<SubmitIndexingData>) {
  const { url, userId } = job.data;

  try {
    // Get available service worker
    const worker = await getAvailableServiceWorker();
    if (!worker) {
      throw new Error("No available service workers");
    }

    // Parse credentials
    const credentials = JSON.parse(worker.credentials);

    // Create auth client
    const auth = new google.auth.GoogleAuth({
      credentials,
      scopes: ["https://www.googleapis.com/auth/indexing"],
    });

    const authClient = await auth.getClient();
    const indexing = google.indexing({ version: "v3", auth: authClient });

    // Submit URL
    const response = await indexing.urlNotifications.publish({
      requestBody: {
        url,
        type: "URL_UPDATED",
      },
    });

    // Update worker usage
    await prisma.serviceWorker.update({
      where: { id: worker.id },
      data: {
        usedQuota: worker.usedQuota + 1,
        lastUsed: new Date(),
      },
    });

    // Record result
    await prisma.indexingResult.create({
      data: {
        url,
        status: "URL_UPDATED",
        metadata: JSON.stringify(response.data),
      },
    });

    return { success: true, response: response.data };
  } catch (error) {
    throw error;
  }
}
```

#### 4. Check Indexing Status Job

**File**: `src/lib/queue/jobs/check-status.ts`

```typescript
import { Job } from "bullmq";
import { prisma } from "@/lib/db";
import { getGoogleClient } from "@/lib/google/auth";
import { google } from "googleapis";
import { sendEmail } from "@/lib/email/client";

export interface CheckStatusData {
  userId: string;
}

export async function checkIndexingStatus(job: Job<CheckStatusData>) {
  const { userId } = job.data;

  try {
    // Get user's Google client
    const auth = await getGoogleClient(userId);
    const searchconsole = google.searchconsole({ version: "v1", auth });

    // Get all URLs that were submitted for indexing
    const submittedUrls = await prisma.urlList.findMany({
      where: {
        userId,
        indexed: false,
      },
    });

    const newlyIndexed = [];

    for (const urlRecord of submittedUrls) {
      try {
        // Inspect URL
        const response = await searchconsole.urlInspection.index.inspect({
          requestBody: {
            inspectionUrl: urlRecord.url,
            siteUrl: urlRecord.url, // Or use the site URL from sitemap
          },
        });

        const result = response.data.inspectionResult;
        const isIndexed = result?.indexStatusResult?.verdict === "PASS";

        if (isIndexed && !urlRecord.indexed) {
          newlyIndexed.push(urlRecord.url);
        }

        // Update URL record
        await prisma.urlList.update({
          where: { id: urlRecord.id },
          data: {
            indexed: isIndexed,
            verdict: result?.indexStatusResult?.verdict,
            coverageState: result?.indexStatusResult?.coverageState,
            robotsTxtState: result?.indexStatusResult?.robotsTxtState,
            indexingState: result?.indexStatusResult?.indexingState,
            pageFetchState: result?.indexStatusResult?.pageFetchState,
            crawledAs: result?.indexStatusResult?.crawledAs,
            googleCanonical: result?.indexStatusResult?.googleCanonical,
            userCanonical: result?.indexStatusResult?.userCanonical,
            lastSeen: new Date(),
          },
        });
      } catch (error) {
        console.error(`Error checking ${urlRecord.url}:`, error);
      }
    }

    // Send notification email if any URLs were newly indexed
    if (newlyIndexed.length > 0) {
      const user = await prisma.user.findUnique({ where: { id: userId } });
      if (user?.email) {
        await sendEmail({
          to: user.email,
          subject: `${newlyIndexed.length} URLs Successfully Indexed!`,
          template: "success-notification",
          data: {
            urls: newlyIndexed,
            count: newlyIndexed.length,
          },
        });
      }
    }

    return { success: true, newlyIndexed: newlyIndexed.length };
  } catch (error) {
    throw error;
  }
}
```

#### 5. Cleanup Old URLs Job

**File**: `src/lib/queue/jobs/cleanup.ts`

```typescript
import { Job } from "bullmq";
import { prisma } from "@/lib/db";

export async function cleanupOldUrls(job: Job) {
  try {
    const cutoffDate = new Date();
    cutoffDate.setHours(cutoffDate.getHours() - 72); // 72 hours ago

    // Delete URLs not seen in 72+ hours
    const result = await prisma.urlList.deleteMany({
      where: {
        lastSeen: {
          lt: cutoffDate,
        },
      },
    });

    // Clean up old queue items
    const queueCutoff = new Date();
    queueCutoff.setDate(queueCutoff.getDate() - 7); // 7 days ago

    await prisma.indexQueue.deleteMany({
      where: {
        status: "COMPLETED",
        processedAt: {
          lt: queueCutoff,
        },
      },
    });

    return { success: true, deletedUrls: result.count };
  } catch (error) {
    throw error;
  }
}
```

### Worker Process

**File**: `workers/index.ts`

```typescript
import { Worker } from "bullmq";
import { redis } from "@/lib/redis";
import { QueueName } from "@/lib/queue/client";
import { processUrl } from "@/lib/queue/jobs/process-url";
import { scanSitemap } from "@/lib/queue/jobs/scan-sitemap";
import { submitForIndexing } from "@/lib/queue/jobs/submit-indexing";
import { checkIndexingStatus } from "@/lib/queue/jobs/check-status";
import { cleanupOldUrls } from "@/lib/queue/jobs/cleanup";

// URL Processing Worker
const urlProcessingWorker = new Worker(
  QueueName.URL_PROCESSING,
  async (job) => processUrl(job),
  {
    connection: redis,
    concurrency: 5,
  }
);

// Sitemap Scan Worker
const sitemapScanWorker = new Worker(
  QueueName.SITEMAP_SCAN,
  async (job) => scanSitemap(job),
  {
    connection: redis,
    concurrency: 3,
  }
);

// Indexing Submission Worker
const indexingSubmissionWorker = new Worker(
  QueueName.INDEXING_SUBMISSION,
  async (job) => submitForIndexing(job),
  {
    connection: redis,
    concurrency: 10,
  }
);

// Status Check Worker
const statusCheckWorker = new Worker(
  QueueName.STATUS_CHECK,
  async (job) => checkIndexingStatus(job),
  {
    connection: redis,
    concurrency: 2,
  }
);

// Cleanup Worker
const cleanupWorker = new Worker(
  QueueName.CLEANUP,
  async (job) => cleanupOldUrls(job),
  {
    connection: redis,
    concurrency: 1,
  }
);

// Error handling
const workers = [
  urlProcessingWorker,
  sitemapScanWorker,
  indexingSubmissionWorker,
  statusCheckWorker,
  cleanupWorker,
];

workers.forEach((worker) => {
  worker.on("completed", (job) => {
    console.log(`Job ${job.id} completed in queue ${worker.name}`);
  });

  worker.on("failed", (job, err) => {
    console.error(`Job ${job?.id} failed in queue ${worker.name}:`, err);
  });
});

console.log("Workers started successfully");

// Graceful shutdown
process.on("SIGTERM", async () => {
  console.log("SIGTERM received, closing workers...");
  await Promise.all(workers.map((w) => w.close()));
  process.exit(0);
});
```

### Scheduled Jobs (Cron)

**File**: `workers/scheduler.ts`

```typescript
import cron from "node-cron";
import { prisma } from "@/lib/db";
import {
  sitemapScanQueue,
  indexingSubmissionQueue,
  statusCheckQueue,
  cleanupQueue,
  urlProcessingQueue,
} from "@/lib/queue/client";

// Process Queued URLs - Every minute
cron.schedule("* * * * *", async () => {
  console.log("Running: Process Queued URLs");

  const pendingUrls = await prisma.indexQueue.findMany({
    where: {
      type: "URL",
      status: "PENDING",
    },
    take: 50, // Process 50 at a time
  });

  for (const item of pendingUrls) {
    await urlProcessingQueue.add("process-url", {
      queueId: item.id,
      url: item.url,
      userId: item.userId,
    });
  }
});

// Auto-Scan Sitemaps - Daily at 8:00 AM EST
cron.schedule("0 8 * * *", async () => {
  console.log("Running: Auto-Scan Sitemaps");

  const sitemaps = await prisma.sitemap.findMany({
    where: {
      autoScan: true,
    },
  });

  for (const sitemap of sitemaps) {
    await sitemapScanQueue.add("scan-sitemap", {
      sitemapId: sitemap.id,
      userId: sitemap.userId,
      url: sitemap.url,
    });
  }
});

// Submit URLs for Indexing - Every 6 hours
cron.schedule("0 */6 * * *", async () => {
  console.log("Running: Submit URLs for Indexing");

  const urlsToSubmit = await prisma.urlList.findMany({
    where: {
      indexed: false,
    },
    take: 100,
  });

  for (const url of urlsToSubmit) {
    await indexingSubmissionQueue.add("submit-indexing", {
      url: url.url,
      userId: url.userId,
    });
  }
});

// Check Indexing Status - Every 2 hours
cron.schedule("0 */2 * * *", async () => {
  console.log("Running: Check Indexing Status");

  const users = await prisma.user.findMany({
    where: {
      googleRefreshToken: {
        not: null,
      },
    },
  });

  for (const user of users) {
    await statusCheckQueue.add("check-status", {
      userId: user.id,
    });
  }
});

// Cleanup Old URLs - Every 6 hours
cron.schedule("0 */6 * * *", async () => {
  console.log("Running: Cleanup Old URLs");

  await cleanupQueue.add("cleanup", {});
});

// Janitor (General cleanup) - Every minute
cron.schedule("* * * * *", async () => {
  // Reset service worker quotas daily
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  await prisma.serviceWorker.updateMany({
    where: {
      quotaResetAt: {
        lt: today,
      },
    },
    data: {
      usedQuota: 0,
      quotaResetAt: new Date(),
    },
  });
});

console.log("Scheduler started successfully");
```

---

## Frontend Architecture

### Component Structure

#### Dashboard Layout

**File**: `src/app/(dashboard)/layout.tsx`

```typescript
import { redirect } from "next/navigation";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { Header } from "@/components/shared/header";
import { Sidebar } from "@/components/shared/sidebar";

export default async function DashboardLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const session = await getServerSession(authOptions);

  if (!session) {
    redirect("/login");
  }

  return (
    <div className="flex h-screen bg-gray-50">
      <Sidebar />
      <div className="flex-1 flex flex-col overflow-hidden">
        <Header user={session.user} />
        <main className="flex-1 overflow-auto p-6">{children}</main>
      </div>
    </div>
  );
}
```

#### Sitemap List Component

**File**: `src/components/dashboard/sitemap-list.tsx`

```typescript
"use client";

import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Button } from "@/components/ui/button";
import { Switch } from "@/components/ui/switch";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import axios from "axios";

export function SitemapList() {
  const queryClient = useQueryClient();

  const { data: sitemaps, isLoading } = useQuery({
    queryKey: ["sitemaps"],
    queryFn: async () => {
      const response = await axios.get("/api/sitemaps");
      return response.data;
    },
  });

  const toggleAutoScan = useMutation({
    mutationFn: async ({ id, autoScan }: { id: string; autoScan: boolean }) => {
      await axios.put(`/api/sitemaps/${id}/auto-scan`, { autoScan });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["sitemaps"] });
    },
  });

  const queueSitemap = useMutation({
    mutationFn: async (id: string) => {
      await axios.post(`/api/sitemaps/${id}/scan`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["sitemaps"] });
    },
  });

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return (
    <div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>URL</TableHead>
            <TableHead>Type</TableHead>
            <TableHead>URLs</TableHead>
            <TableHead>Last Scanned</TableHead>
            <TableHead>Auto-Scan</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {sitemaps?.map((sitemap: any) => (
            <TableRow key={sitemap.id}>
              <TableCell className="font-medium">{sitemap.url}</TableCell>
              <TableCell>
                <Badge variant={sitemap.isSitemapsIndex ? "default" : "secondary"}>
                  {sitemap.isSitemapsIndex ? "Index" : "Sitemap"}
                </Badge>
              </TableCell>
              <TableCell>{sitemap._count.urls}</TableCell>
              <TableCell>
                {sitemap.lastDownloaded
                  ? new Date(sitemap.lastDownloaded).toLocaleDateString()
                  : "Never"}
              </TableCell>
              <TableCell>
                <Switch
                  checked={sitemap.autoScan}
                  onCheckedChange={(checked) =>
                    toggleAutoScan.mutate({ id: sitemap.id, autoScan: checked })
                  }
                />
              </TableCell>
              <TableCell>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => queueSitemap.mutate(sitemap.id)}
                >
                  Scan Now
                </Button>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  );
}
```

#### URL Table Component

**File**: `src/components/dashboard/url-table.tsx`

```typescript
"use client";

import { useQuery } from "@tanstack/react-query";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { CheckCircle, XCircle, Clock } from "lucide-react";
import axios from "axios";

export function UrlTable() {
  const { data: urls, isLoading } = useQuery({
    queryKey: ["urls"],
    queryFn: async () => {
      const response = await axios.get("/api/urls");
      return response.data;
    },
  });

  const getFreshnessColor = (lastSeen: string | null) => {
    if (!lastSeen) return "bg-red-500";

    const hoursSinceLastSeen =
      (Date.now() - new Date(lastSeen).getTime()) / (1000 * 60 * 60);

    if (hoursSinceLastSeen < 24) return "bg-green-500";
    if (hoursSinceLastSeen < 48) return "bg-yellow-500";
    return "bg-red-500";
  };

  if (isLoading) {
    return <div>Loading...</div>;
  }

  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>URL</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Verdict</TableHead>
          <TableHead>Last Seen</TableHead>
          <TableHead>Freshness</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {urls?.map((url: any) => (
          <TableRow key={url.id}>
            <TableCell className="font-medium max-w-md truncate">
              {url.url}
            </TableCell>
            <TableCell>
              {url.indexed ? (
                <Badge variant="success" className="flex items-center gap-1">
                  <CheckCircle className="w-3 h-3" />
                  Indexed
                </Badge>
              ) : (
                <Badge variant="destructive" className="flex items-center gap-1">
                  <XCircle className="w-3 h-3" />
                  Not Indexed
                </Badge>
              )}
            </TableCell>
            <TableCell>{url.verdict || "N/A"}</TableCell>
            <TableCell>
              {url.lastSeen
                ? new Date(url.lastSeen).toLocaleString()
                : "Never"}
            </TableCell>
            <TableCell>
              <div
                className={`w-3 h-3 rounded-full ${getFreshnessColor(
                  url.lastSeen
                )}`}
              />
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
}
```

### State Management

Use **TanStack Query** for server state and **Zustand** for client state.

**Example Zustand Store** (`src/store/ui.ts`):

```typescript
import { create } from "zustand";

interface UIState {
  sidebarOpen: boolean;
  setSidebarOpen: (open: boolean) => void;
  toggleSidebar: () => void;
}

export const useUIStore = create<UIState>((set) => ({
  sidebarOpen: true,
  setSidebarOpen: (open) => set({ sidebarOpen: open }),
  toggleSidebar: () => set((state) => ({ sidebarOpen: !state.sidebarOpen })),
}));
```

---

## Core Features Specification

### 1. Google Search Console Integration

**Requirements:**
- OAuth 2.0 authentication with Google
- Automatic token refresh
- Fetch sitemaps from GSC
- Sync sitemap data
- URL inspection API integration

**Implementation:**

**File**: `src/lib/google/search-console.ts`

```typescript
import { google } from "googleapis";
import { getGoogleClient } from "./auth";

export async function fetchSitemaps(userId: string) {
  const auth = await getGoogleClient(userId);
  const webmasters = google.webmasters({ version: "v3", auth });

  // Get all sites
  const sitesResponse = await webmasters.sites.list();
  const sites = sitesResponse.data.siteEntry || [];

  const allSitemaps = [];

  for (const site of sites) {
    const sitemapsResponse = await webmasters.sitemaps.list({
      siteUrl: site.siteUrl!,
    });

    const sitemaps = sitemapsResponse.data.sitemap || [];
    allSitemaps.push(...sitemaps.map((sm) => ({ ...sm, siteUrl: site.siteUrl })));
  }

  return allSitemaps;
}

export async function inspectUrl(userId: string, inspectionUrl: string, siteUrl: string) {
  const auth = await getGoogleClient(userId);
  const searchconsole = google.searchconsole({ version: "v1", auth });

  const response = await searchconsole.urlInspection.index.inspect({
    requestBody: {
      inspectionUrl,
      siteUrl,
    },
  });

  return response.data;
}
```

### 2. Sitemap Management

**Requirements:**
- List all user sitemaps
- Import sitemaps from GSC
- Enable/disable auto-scan
- Manual scan trigger
- View sitemap URLs
- Track errors and warnings

**API Route**: `src/app/api/sitemaps/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";
import { z } from "zod";

const createSitemapSchema = z.object({
  url: z.string().url(),
  gscSiteUrl: z.string().url().optional(),
});

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions);
  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const sitemaps = await prisma.sitemap.findMany({
    where: { userId: session.user.id },
    include: {
      _count: {
        select: { urls: true },
      },
    },
    orderBy: { createdAt: "desc" },
  });

  return NextResponse.json(sitemaps);
}

export async function POST(request: NextRequest) {
  const session = await getServerSession(authOptions);
  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  try {
    const body = await request.json();
    const data = createSitemapSchema.parse(body);

    const sitemap = await prisma.sitemap.create({
      data: {
        userId: session.user.id,
        url: data.url,
        gscSiteUrl: data.gscSiteUrl,
      },
    });

    return NextResponse.json(sitemap, { status: 201 });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return NextResponse.json({ error: error.errors }, { status: 400 });
    }
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}
```

### 3. URL Monitoring

**Requirements:**
- Track all URLs from sitemaps
- Store indexing status
- Visual freshness indicators (green/yellow/red)
- Last seen timestamp
- Index history
- Bulk operations

**Color Coding Logic:**
- **Green**: Last seen < 24 hours ago
- **Yellow**: Last seen 24-48 hours ago
- **Red**: Last seen > 48 hours ago or never

### 4. Queue System

**Requirements:**
- Add URLs/sitemaps to queue
- Process queue items asynchronously
- Retry failed items
- Track processing status
- Display queue statistics
- Admin queue monitoring

**Service**: `src/services/queue.service.ts`

```typescript
import { prisma } from "@/lib/db";
import { urlProcessingQueue, sitemapScanQueue } from "@/lib/queue/client";
import { QueueType } from "@prisma/client";

export async function addToQueue(
  userId: string,
  url: string,
  type: QueueType,
  sitemapId?: string
) {
  const queueItem = await prisma.indexQueue.create({
    data: {
      userId,
      url,
      type,
      sitemapId,
    },
  });

  if (type === "URL") {
    await urlProcessingQueue.add("process-url", {
      queueId: queueItem.id,
      url,
      userId,
    });
  } else if (type === "SITEMAP") {
    await sitemapScanQueue.add("scan-sitemap", {
      sitemapId: sitemapId!,
      userId,
      url,
    });
  }

  return queueItem;
}

export async function retryQueueItem(queueId: string) {
  const item = await prisma.indexQueue.findUnique({
    where: { id: queueId },
  });

  if (!item) {
    throw new Error("Queue item not found");
  }

  await prisma.indexQueue.update({
    where: { id: queueId },
    data: {
      status: "PENDING",
      attempts: 0,
      error: null,
    },
  });

  await addToQueue(item.userId, item.url, item.type, item.sitemapId || undefined);
}
```

### 5. Email Notifications

**Requirements:**
- Send success notifications when URLs are indexed
- Email builder (GrapesJS integration)
- Template management
- Bulk email sending (admin)

**Email Service**: `src/lib/email/client.ts`

```typescript
import { Resend } from "resend";
import { prisma } from "@/lib/db";

const resend = new Resend(process.env.RESEND_API_KEY);

interface SendEmailOptions {
  to: string | string[];
  subject: string;
  template: string;
  data: Record<string, any>;
}

export async function sendEmail(options: SendEmailOptions) {
  const template = await prisma.emailTemplate.findFirst({
    where: { name: options.template },
  });

  if (!template) {
    throw new Error(`Template ${options.template} not found`);
  }

  // Replace template variables
  let html = template.htmlContent;
  for (const [key, value] of Object.entries(options.data)) {
    html = html.replace(new RegExp(`{{${key}}}`, "g"), String(value));
  }

  const response = await resend.emails.send({
    from: process.env.EMAIL_FROM!,
    to: Array.isArray(options.to) ? options.to : [options.to],
    subject: options.subject,
    html,
  });

  return response;
}
```

**Success Notification Template**:

```html
<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: #10b981; color: white; padding: 20px; text-align: center; }
    .content { padding: 20px; background: #f9fafb; }
    .url-list { list-style: none; padding: 0; }
    .url-item { padding: 10px; margin: 5px 0; background: white; border-left: 3px solid #10b981; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🎉 URLs Successfully Indexed!</h1>
    </div>
    <div class="content">
      <p>Great news! {{count}} of your URLs have been successfully indexed by Google.</p>
      <h3>Newly Indexed URLs:</h3>
      <ul class="url-list">
        {{#each urls}}
        <li class="url-item">{{this}}</li>
        {{/each}}
      </ul>
      <p>You can view more details in your <a href="{{dashboardUrl}}">dashboard</a>.</p>
    </div>
  </div>
</body>
</html>
```

---

## Google API Integration

### Required Google APIs

1. **Google Search Console API**
   - Fetch sitemaps
   - Inspect URLs
   - Get indexing data

2. **Google Indexing API**
   - Submit URLs for indexing
   - Request URL removal

### Service Account Setup

For the Indexing API, you'll need service accounts (equivalent to Laravel's ServiceWorker model).

**Worker Service**: `src/services/worker.service.ts`

```typescript
import { prisma } from "@/lib/db";

export async function getAvailableServiceWorker() {
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  // Find worker with available quota
  const worker = await prisma.serviceWorker.findFirst({
    where: {
      isActive: true,
      usedQuota: {
        lt: prisma.serviceWorker.fields.dailyQuota,
      },
    },
    orderBy: {
      usedQuota: "asc",
    },
  });

  return worker;
}

export async function resetWorkerQuotas() {
  await prisma.serviceWorker.updateMany({
    data: {
      usedQuota: 0,
      quotaResetAt: new Date(),
    },
  });
}
```

---

## Admin Panel

### Admin Routes

All admin routes should be protected with role-based middleware.

#### User Management

**File**: `src/app/(admin)/admin/users/page.tsx`

```typescript
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { redirect } from "next/navigation";
import { UserTable } from "@/components/admin/user-table";

export default async function AdminUsersPage() {
  const session = await getServerSession(authOptions);

  if (session?.user.role !== "admin") {
    redirect("/dashboard");
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">User Management</h1>
      <UserTable />
    </div>
  );
}
```

#### Service Worker Management

**File**: `src/app/api/admin/workers/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";

export async function GET() {
  const session = await getServerSession(authOptions);
  if (session?.user.role !== "admin") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  const workers = await prisma.serviceWorker.findMany({
    orderBy: { createdAt: "desc" },
  });

  return NextResponse.json(workers);
}

export async function POST(request: NextRequest) {
  const session = await getServerSession(authOptions);
  if (session?.user.role !== "admin") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  const body = await request.json();

  const worker = await prisma.serviceWorker.create({
    data: {
      name: body.name,
      email: body.email,
      credentials: body.credentials, // Should be encrypted
      dailyQuota: body.dailyQuota || 200,
    },
  });

  return NextResponse.json(worker, { status: 201 });
}
```

#### Manual Job Execution

**File**: `src/app/api/admin/jobs/run/route.ts`

```typescript
import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { prisma } from "@/lib/db";
import {
  sitemapScanQueue,
  statusCheckQueue,
  cleanupQueue,
} from "@/lib/queue/client";

export async function POST(request: NextRequest) {
  const session = await getServerSession(authOptions);
  if (session?.user.role !== "admin") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 });
  }

  const { jobName } = await request.json();

  try {
    switch (jobName) {
      case "scan-sitemaps":
        const sitemaps = await prisma.sitemap.findMany({
          where: { autoScan: true },
        });
        for (const sitemap of sitemaps) {
          await sitemapScanQueue.add("scan-sitemap", {
            sitemapId: sitemap.id,
            userId: sitemap.userId,
            url: sitemap.url,
          });
        }
        break;

      case "check-status":
        const users = await prisma.user.findMany({
          where: { googleRefreshToken: { not: null } },
        });
        for (const user of users) {
          await statusCheckQueue.add("check-status", { userId: user.id });
        }
        break;

      case "cleanup":
        await cleanupQueue.add("cleanup", {});
        break;

      default:
        return NextResponse.json({ error: "Unknown job" }, { status: 400 });
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    return NextResponse.json(
      { error: "Failed to run job" },
      { status: 500 }
    );
  }
}
```

---

## Queue System

### Bull Board Setup (Horizon Equivalent)

**File**: `src/app/api/admin/queues/route.ts`

```typescript
import { createBullBoard } from "@bull-board/api";
import { BullMQAdapter } from "@bull-board/api/bullMQAdapter";
import { ExpressAdapter } from "@bull-board/express";
import {
  urlProcessingQueue,
  sitemapScanQueue,
  indexingSubmissionQueue,
  statusCheckQueue,
  cleanupQueue,
} from "@/lib/queue/client";

const serverAdapter = new ExpressAdapter();
serverAdapter.setBasePath("/admin/queues");

createBullBoard({
  queues: [
    new BullMQAdapter(urlProcessingQueue),
    new BullMQAdapter(sitemapScanQueue),
    new BullMQAdapter(indexingSubmissionQueue),
    new BullMQAdapter(statusCheckQueue),
    new BullMQAdapter(cleanupQueue),
  ],
  serverAdapter,
});

export { serverAdapter };
```

This provides a web UI similar to Laravel Horizon for monitoring queues.

---

## Deployment & Infrastructure

### Environment Variables

**File**: `.env.example`

```bash
# App
NODE_ENV=production
NEXT_PUBLIC_APP_URL=https://yourdomain.com

# Database
DATABASE_URL="postgresql://user:password@localhost:5432/serpsurfer"

# Redis
REDIS_URL="redis://localhost:6379"

# NextAuth
NEXTAUTH_SECRET="your-secret-key"
NEXTAUTH_URL="https://yourdomain.com"

# Google OAuth
GOOGLE_CLIENT_ID="your-client-id"
GOOGLE_CLIENT_SECRET="your-client-secret"
GOOGLE_REDIRECT_URI="https://yourdomain.com/api/auth/callback/google"

# Email
RESEND_API_KEY="re_..."
EMAIL_FROM="noreply@yourdomain.com"

# Optional: Sentry
SENTRY_DSN="https://..."
```

### Docker Setup

**File**: `Dockerfile`

```dockerfile
FROM node:20-alpine AS base

# Dependencies
FROM base AS deps
WORKDIR /app
COPY package.json pnpm-lock.yaml ./
RUN corepack enable pnpm && pnpm install --frozen-lockfile

# Builder
FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .
RUN corepack enable pnpm && \
    pnpm prisma generate && \
    pnpm build

# Runner
FROM base AS runner
WORKDIR /app
ENV NODE_ENV=production

RUN addgroup --system --gid 1001 nodejs && \
    adduser --system --uid 1001 nextjs

COPY --from=builder /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static
COPY --from=builder /app/prisma ./prisma
COPY --from=builder /app/node_modules/.prisma ./node_modules/.prisma

USER nextjs

EXPOSE 3000
ENV PORT=3000

CMD ["node", "server.js"]
```

**File**: `docker-compose.yml`

```yaml
version: "3.8"

services:
  app:
    build: .
    ports:
      - "3000:3000"
    environment:
      - DATABASE_URL=postgresql://postgres:password@db:5432/serpsurfer
      - REDIS_URL=redis://redis:6379
    depends_on:
      - db
      - redis

  worker:
    build: .
    command: node workers/index.js
    environment:
      - DATABASE_URL=postgresql://postgres:password@db:5432/serpsurfer
      - REDIS_URL=redis://redis:6379
    depends_on:
      - db
      - redis

  scheduler:
    build: .
    command: node workers/scheduler.js
    environment:
      - DATABASE_URL=postgresql://postgres:password@db:5432/serpsurfer
      - REDIS_URL=redis://redis:6379
    depends_on:
      - db
      - redis

  db:
    image: postgres:15-alpine
    environment:
      - POSTGRES_USER=postgres
      - POSTGRES_PASSWORD=password
      - POSTGRES_DB=serpsurfer
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data
    ports:
      - "6379:6379"

volumes:
  postgres_data:
  redis_data:
```

### Deployment Options

#### Option 1: Vercel (Recommended)

```bash
# Install Vercel CLI
npm i -g vercel

# Deploy
vercel

# Set environment variables in Vercel dashboard
# Note: You'll need separate hosting for workers (Railway, Render, etc.)
```

#### Option 2: Railway

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Deploy
railway up
```

#### Option 3: AWS (ECS/Fargate)

Use the Dockerfile and deploy to ECS with proper task definitions for:
- Web app
- Worker processes
- Scheduler

---

## Migration Strategy

### Phase 1: Setup & Core Infrastructure (Week 1-2)

1. **Initialize Next.js project**
   ```bash
   npx create-next-app@latest serp-surfer-nextjs --typescript --tailwind --app
   cd serp-surfer-nextjs
   pnpm install
   ```

2. **Setup database**
   - Install Prisma
   - Create schema
   - Run migrations

3. **Setup authentication**
   - Configure NextAuth.js
   - Implement Google OAuth
   - Create login/register pages

### Phase 2: Core Features (Week 3-4)

1. **Sitemap management**
   - API routes
   - Components
   - Google Search Console integration

2. **URL tracking**
   - Database models
   - API endpoints
   - Dashboard UI

3. **Queue system**
   - Setup BullMQ
   - Implement workers
   - Create job processors

### Phase 3: Background Jobs (Week 5)

1. **Implement all job processors**
   - URL processing
   - Sitemap scanning
   - Indexing submission
   - Status checking
   - Cleanup

2. **Setup scheduler**
   - Cron jobs
   - Job monitoring

### Phase 4: Admin Panel (Week 6)

1. **User management**
2. **Sitemap administration**
3. **Service worker management**
4. **Email builder**

### Phase 5: Testing & Deployment (Week 7-8)

1. **Write tests**
   - Unit tests
   - Integration tests
   - E2E tests

2. **Performance optimization**
   - Caching strategies
   - Database indexing
   - Query optimization

3. **Deploy to production**
   - Setup CI/CD
   - Configure monitoring
   - Launch

### Data Migration

**Script**: `scripts/migrate-from-laravel.ts`

```typescript
import { PrismaClient } from "@prisma/client";
import mysql from "mysql2/promise";

const prisma = new PrismaClient();

async function migrate() {
  // Connect to Laravel MySQL database
  const laravelDb = await mysql.createConnection({
    host: process.env.LARAVEL_DB_HOST,
    user: process.env.LARAVEL_DB_USER,
    password: process.env.LARAVEL_DB_PASSWORD,
    database: process.env.LARAVEL_DB_NAME,
  });

  // Migrate users
  const [users] = await laravelDb.execute("SELECT * FROM users");
  for (const user of users as any[]) {
    await prisma.user.create({
      data: {
        id: user.id,
        email: user.email,
        name: user.name,
        password: user.password,
        googleAccessToken: user.google_access_token,
        googleRefreshToken: user.google_refresh_token,
        googleTokenExpiry: user.google_token_expiry,
        createdAt: user.created_at,
        updatedAt: user.updated_at,
      },
    });
  }

  // Migrate sitemaps
  const [sitemaps] = await laravelDb.execute("SELECT * FROM sitemaps");
  for (const sitemap of sitemaps as any[]) {
    await prisma.sitemap.create({
      data: {
        id: sitemap.id,
        userId: sitemap.user_id,
        url: sitemap.url,
        autoScan: sitemap.auto_scan,
        // ... other fields
      },
    });
  }

  // Continue for other tables...

  await laravelDb.end();
  await prisma.$disconnect();

  console.log("Migration complete!");
}

migrate().catch(console.error);
```

---

## Additional Considerations

### Security

1. **Input validation**: Use Zod for all user inputs
2. **SQL injection**: Prisma ORM prevents this
3. **XSS**: React escapes by default
4. **CSRF**: NextAuth handles this
5. **Rate limiting**: Implement with `express-rate-limit` or Upstash
6. **Encryption**: Encrypt sensitive data (service worker credentials)

### Performance

1. **Database indexing**: Already defined in Prisma schema
2. **Caching**: Use Redis for frequently accessed data
3. **CDN**: Use Vercel's Edge Network or CloudFront
4. **Image optimization**: Next.js Image component
5. **Code splitting**: Automatic with Next.js App Router

### Monitoring

1. **Error tracking**: Sentry
2. **Logging**: Pino or Winston
3. **APM**: New Relic or Datadog
4. **Uptime monitoring**: UptimeRobot or Pingdom

### Testing

```typescript
// Example test with Vitest
import { describe, it, expect } from "vitest";
import { addToQueue } from "@/services/queue.service";

describe("Queue Service", () => {
  it("should add URL to queue", async () => {
    const result = await addToQueue("user-id", "https://example.com", "URL");
    expect(result).toHaveProperty("id");
    expect(result.url).toBe("https://example.com");
  });
});
```

---

## Conclusion

This specification provides a complete blueprint for rewriting SERP Surfer in Next.js with full feature parity to the Laravel version. The JavaScript/TypeScript ecosystem provides excellent tools (Prisma, BullMQ, NextAuth.js, TanStack Query) that match or exceed Laravel's capabilities.

**Key Advantages of Next.js Version:**
- ✅ Modern, type-safe codebase with TypeScript
- ✅ Superior developer experience
- ✅ Excellent performance with React Server Components
- ✅ Unified frontend/backend in one codebase
- ✅ Easy deployment options (Vercel, Railway, etc.)
- ✅ Strong ecosystem and community support

**Total Estimated Development Time**: 6-8 weeks with 1 developer

**Recommended Tech Stack Summary:**
- **Frontend**: Next.js 14+ (App Router) + TypeScript + TailwindCSS + shadcn/ui
- **Backend**: Next.js API Routes + Prisma ORM
- **Database**: PostgreSQL
- **Queue**: BullMQ + Redis
- **Auth**: NextAuth.js
- **Email**: Resend or SendGrid
- **Deployment**: Vercel (app) + Railway (workers)
