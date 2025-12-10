# Database Scripts, Type Definitions & Utilities

Complete database seeding, migration scripts, TypeScript types, and utility functions.

---

## Table of Contents
1. [Database Seeding](#database-seeding)
2. [Migration Scripts](#migration-scripts)
3. [Type Definitions](#type-definitions)
4. [Utility Functions](#utility-functions)
5. [Validation Schemas](#validation-schemas)
6. [Custom Hooks](#custom-hooks)

---

## Database Seeding

### Main Seed Script

**File**: `prisma/seed.ts`

```typescript
import { PrismaClient, UserStatus, QueueType, QueueStatus, EmailType } from "@prisma/client";
import { hash } from "bcryptjs";

const prisma = new PrismaClient();

async function main() {
  console.log("🌱 Starting database seed...");

  // Clear existing data
  await prisma.indexingResult.deleteMany();
  await prisma.indexQueue.deleteMany();
  await prisma.sitemapUrl.deleteMany();
  await prisma.sitemap.deleteMany();
  await prisma.urlList.deleteMany();
  await prisma.emailTemplate.deleteMany();
  await prisma.serviceWorker.deleteMany();
  await prisma.session.deleteMany();
  await prisma.account.deleteMany();
  await prisma.user.deleteMany();
  await prisma.role.deleteMany();

  console.log("✅ Cleared existing data");

  // Create roles
  const adminRole = await prisma.role.create({
    data: {
      name: "admin",
      description: "Administrator with full access",
    },
  });

  const userRole = await prisma.role.create({
    data: {
      name: "user",
      description: "Regular user",
    },
  });

  console.log("✅ Created roles");

  // Create admin user
  const hashedAdminPassword = await hash("Admin123!", 12);
  const adminUser = await prisma.user.create({
    data: {
      name: "Admin User",
      email: "admin@serpsurfer.com",
      password: hashedAdminPassword,
      roleId: adminRole.id,
      status: UserStatus.ACTIVE,
      emailVerified: new Date(),
    },
  });

  console.log("✅ Created admin user");

  // Create test users
  const hashedUserPassword = await hash("User123!", 12);
  const testUsers = [];

  for (let i = 1; i <= 5; i++) {
    const user = await prisma.user.create({
      data: {
        name: `Test User ${i}`,
        email: `user${i}@test.com`,
        password: hashedUserPassword,
        roleId: userRole.id,
        status: UserStatus.ACTIVE,
        emailVerified: new Date(),
      },
    });
    testUsers.push(user);
  }

  console.log("✅ Created test users");

  // Create sitemaps for test users
  const sitemaps = [];
  for (const user of testUsers) {
    for (let i = 1; i <= 3; i++) {
      const sitemap = await prisma.sitemap.create({
        data: {
          userId: user.id,
          url: `https://example${user.id.slice(-1)}.com/sitemap-${i}.xml`,
          gscSiteUrl: `https://example${user.id.slice(-1)}.com`,
          autoScan: i === 1, // Enable auto-scan for first sitemap
          lastDownloaded: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000),
          errors: BigInt(Math.floor(Math.random() * 5)),
          warnings: BigInt(Math.floor(Math.random() * 10)),
        },
      });
      sitemaps.push(sitemap);
    }
  }

  console.log("✅ Created sitemaps");

  // Create sitemap URLs
  for (const sitemap of sitemaps) {
    const urlCount = 50 + Math.floor(Math.random() * 50);

    for (let i = 1; i <= urlCount; i++) {
      await prisma.sitemapUrl.create({
        data: {
          sitemapId: sitemap.id,
          url: `${sitemap.gscSiteUrl}/page-${i}`,
          priority: Math.random(),
          changefreq: ["daily", "weekly", "monthly"][Math.floor(Math.random() * 3)],
          lastmod: new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000),
          indexed: Math.random() > 0.3, // 70% indexed
        },
      });
    }
  }

  console.log("✅ Created sitemap URLs");

  // Create URL tracking list
  for (const user of testUsers) {
    const urlCount = 100 + Math.floor(Math.random() * 100);

    for (let i = 1; i <= urlCount; i++) {
      const lastSeen = Math.random() > 0.1
        ? new Date(Date.now() - Math.random() * 72 * 60 * 60 * 1000)
        : null;

      await prisma.urlList.create({
        data: {
          userId: user.id,
          url: `https://example.com/page-${i}`,
          indexed: Math.random() > 0.4,
          lastSeen,
          verdict: ["PASS", "FAIL", null][Math.floor(Math.random() * 3)],
          coverageState: ["Submitted and indexed", "Discovered - currently not indexed"][Math.floor(Math.random() * 2)],
          indexingState: ["Indexing allowed", "Indexing not allowed"][Math.floor(Math.random() * 2)],
        },
      });
    }
  }

  console.log("✅ Created URL tracking list");

  // Create queue items
  for (const user of testUsers) {
    // Pending items
    for (let i = 1; i <= 10; i++) {
      await prisma.indexQueue.create({
        data: {
          userId: user.id,
          url: `https://example.com/pending-${i}`,
          type: [QueueType.URL, QueueType.SITEMAP, QueueType.INDEX_SUBMISSION][Math.floor(Math.random() * 3)],
          status: QueueStatus.PENDING,
        },
      });
    }

    // Completed items
    for (let i = 1; i <= 20; i++) {
      await prisma.indexQueue.create({
        data: {
          userId: user.id,
          url: `https://example.com/completed-${i}`,
          type: QueueType.URL,
          status: QueueStatus.COMPLETED,
          processedAt: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000),
        },
      });
    }

    // Failed items
    for (let i = 1; i <= 5; i++) {
      await prisma.indexQueue.create({
        data: {
          userId: user.id,
          url: `https://example.com/failed-${i}`,
          type: QueueType.URL,
          status: QueueStatus.FAILED,
          attempts: 3,
          error: "Network timeout",
          lastAttempt: new Date(Date.now() - Math.random() * 24 * 60 * 60 * 1000),
        },
      });
    }
  }

  console.log("✅ Created queue items");

  // Create service workers
  const workers = [];
  for (let i = 1; i <= 3; i++) {
    const worker = await prisma.serviceWorker.create({
      data: {
        name: `Service Worker ${i}`,
        email: `worker${i}@serpsurfer.com`,
        credentials: JSON.stringify({
          type: "service_account",
          project_id: `project-${i}`,
          private_key_id: `key-${i}`,
          private_key: "-----BEGIN PRIVATE KEY-----\nMOCK_KEY\n-----END PRIVATE KEY-----",
          client_email: `worker${i}@project.iam.gserviceaccount.com`,
        }),
        dailyQuota: 200,
        usedQuota: Math.floor(Math.random() * 100),
        lastUsed: new Date(Date.now() - Math.random() * 24 * 60 * 60 * 1000),
        isActive: true,
      },
    });
    workers.push(worker);
  }

  console.log("✅ Created service workers");

  // Create indexing results
  for (let i = 1; i <= 50; i++) {
    await prisma.indexingResult.create({
      data: {
        url: `https://example.com/indexed-${i}`,
        status: "URL_UPDATED",
        metadata: JSON.stringify({
          urlNotificationMetadata: {
            url: `https://example.com/indexed-${i}`,
            latestUpdate: {
              type: "URL_UPDATED",
              notifyTime: new Date().toISOString(),
            },
          },
        }),
      },
    });
  }

  console.log("✅ Created indexing results");

  // Create email templates
  await prisma.emailTemplate.create({
    data: {
      name: "success-notification",
      subject: "URLs Successfully Indexed!",
      type: EmailType.SUCCESS_NOTIFICATION,
      htmlContent: `
        <!DOCTYPE html>
        <html>
        <body>
          <h1>🎉 Great News!</h1>
          <p>{{count}} of your URLs have been successfully indexed by Google.</p>
          <ul>
            {{#each urls}}
            <li>{{this}}</li>
            {{/each}}
          </ul>
          <p><a href="{{dashboardUrl}}">View Dashboard</a></p>
        </body>
        </html>
      `,
    },
  });

  await prisma.emailTemplate.create({
    data: {
      name: "bug-report",
      subject: "Bug Report",
      type: EmailType.BUG_REPORT,
      htmlContent: `
        <!DOCTYPE html>
        <html>
        <body>
          <h1>🐛 Bug Report</h1>
          <p><strong>From:</strong> {{userEmail}}</p>
          <p><strong>Subject:</strong> {{subject}}</p>
          <p><strong>Message:</strong></p>
          <p>{{message}}</p>
          <p><strong>Reported At:</strong> {{reportedAt}}</p>
        </body>
        </html>
      `,
    },
  });

  console.log("✅ Created email templates");

  console.log("\n🎉 Database seeding completed successfully!");
  console.log("\n📊 Summary:");
  console.log(`- Roles: 2`);
  console.log(`- Users: ${testUsers.length + 1}`);
  console.log(`- Sitemaps: ${sitemaps.length}`);
  console.log(`- Service Workers: ${workers.length}`);
  console.log(`- Email Templates: 2`);
  console.log(`\n🔐 Admin Login:`);
  console.log(`Email: admin@serpsurfer.com`);
  console.log(`Password: Admin123!`);
  console.log(`\n🔐 Test User Login:`);
  console.log(`Email: user1@test.com`);
  console.log(`Password: User123!`);
}

main()
  .catch((e) => {
    console.error("❌ Seeding failed:", e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
```

---

## Migration Scripts

### Laravel to Next.js Migration

**File**: `scripts/migrate-from-laravel.ts`

```typescript
import { PrismaClient } from "@prisma/client";
import mysql from "mysql2/promise";
import { config } from "dotenv";

config({ path: ".env.local" });

const prisma = new PrismaClient();

async function migrate() {
  console.log("🚀 Starting migration from Laravel to Next.js...\n");

  try {
    // Connect to Laravel database
    const laravelDb = await mysql.createConnection({
      host: process.env.LARAVEL_DB_HOST || "localhost",
      user: process.env.LARAVEL_DB_USER || "root",
      password: process.env.LARAVEL_DB_PASSWORD || "",
      database: process.env.LARAVEL_DB_NAME || "serpsurfer_laravel",
    });

    console.log("✅ Connected to Laravel database\n");

    // Migrate roles
    console.log("Migrating roles...");
    const [roles] = await laravelDb.execute("SELECT * FROM roles");
    const roleMap = new Map<number, string>();

    for (const role of roles as any[]) {
      const newRole = await prisma.role.create({
        data: {
          name: role.name,
          description: role.description,
        },
      });
      roleMap.set(role.id, newRole.id);
    }
    console.log(`✅ Migrated ${roles.length} roles\n`);

    // Migrate users
    console.log("Migrating users...");
    const [users] = await laravelDb.execute("SELECT * FROM users");
    const userMap = new Map<number, string>();

    for (const user of users as any[]) {
      const roleId = roleMap.get(user.role_id);

      const newUser = await prisma.user.create({
        data: {
          email: user.email,
          name: user.name,
          password: user.password,
          emailVerified: user.email_verified_at,
          image: user.image,
          googleAccessToken: user.google_access_token,
          googleRefreshToken: user.google_refresh_token,
          googleTokenExpiry: user.google_token_expiry,
          status: user.is_suspended ? "SUSPENDED" : "ACTIVE",
          isSuspended: user.is_suspended || false,
          roleId: roleId!,
          createdAt: user.created_at,
          updatedAt: user.updated_at,
        },
      });
      userMap.set(user.id, newUser.id);
    }
    console.log(`✅ Migrated ${users.length} users\n`);

    // Migrate sitemaps
    console.log("Migrating sitemaps...");
    const [sitemaps] = await laravelDb.execute("SELECT * FROM sitemaps");
    const sitemapMap = new Map<number, string>();

    for (const sitemap of sitemaps as any[]) {
      const userId = userMap.get(sitemap.user_id);

      const newSitemap = await prisma.sitemap.create({
        data: {
          userId: userId!,
          url: sitemap.url,
          type: sitemap.type,
          gscSiteUrl: sitemap.gsc_site_url,
          path: sitemap.path,
          lastSubmitted: sitemap.last_submitted,
          lastDownloaded: sitemap.last_downloaded,
          warnings: sitemap.warnings ? BigInt(sitemap.warnings) : BigInt(0),
          errors: sitemap.errors ? BigInt(sitemap.errors) : BigInt(0),
          isPending: sitemap.is_pending || false,
          isSitemapsIndex: sitemap.is_sitemaps_index || false,
          autoScan: sitemap.auto_scan || false,
          createdAt: sitemap.created_at,
          updatedAt: sitemap.updated_at,
        },
      });
      sitemapMap.set(sitemap.id, newSitemap.id);
    }
    console.log(`✅ Migrated ${sitemaps.length} sitemaps\n`);

    // Migrate sitemap URLs
    console.log("Migrating sitemap URLs...");
    const [sitemapUrls] = await laravelDb.execute("SELECT * FROM sitemap_urls");

    for (const url of sitemapUrls as any[]) {
      const sitemapId = sitemapMap.get(url.sitemap_id);

      await prisma.sitemapUrl.create({
        data: {
          sitemapId: sitemapId!,
          url: url.url,
          priority: url.priority,
          changefreq: url.changefreq,
          lastmod: url.lastmod,
          indexed: url.indexed || false,
          inspectionUrl: url.inspection_url,
          createdAt: url.created_at,
          updatedAt: url.updated_at,
        },
      });
    }
    console.log(`✅ Migrated ${sitemapUrls.length} sitemap URLs\n`);

    // Migrate URL list
    console.log("Migrating URL list...");
    const [urlList] = await laravelDb.execute("SELECT * FROM url_list");

    for (const url of urlList as any[]) {
      const userId = userMap.get(url.user_id);

      await prisma.urlList.create({
        data: {
          userId: userId!,
          url: url.url,
          indexed: url.indexed || false,
          lastSeen: url.last_seen,
          verdict: url.verdict,
          coverageState: url.coverage_state,
          robotsTxtState: url.robots_txt_state,
          indexingState: url.indexing_state,
          pageFetchState: url.page_fetch_state,
          crawledAs: url.crawled_as,
          googleCanonical: url.google_canonical,
          userCanonical: url.user_canonical,
          referringUrls: url.referring_urls,
          createdAt: url.created_at,
          updatedAt: url.updated_at,
        },
      });
    }
    console.log(`✅ Migrated ${urlList.length} URLs\n`);

    // Migrate queue
    console.log("Migrating queue...");
    const [queue] = await laravelDb.execute("SELECT * FROM index_queue");

    for (const item of queue as any[]) {
      const userId = userMap.get(item.user_id);
      const sitemapId = item.sitemap_id ? sitemapMap.get(item.sitemap_id) : null;

      await prisma.indexQueue.create({
        data: {
          userId: userId!,
          url: item.url,
          type: item.type,
          status: item.status,
          sitemapId: sitemapId || undefined,
          priority: item.priority || 0,
          attempts: item.attempts || 0,
          maxAttempts: item.max_attempts || 3,
          error: item.error,
          lastAttempt: item.last_attempt,
          processedAt: item.processed_at,
          createdAt: item.created_at,
          updatedAt: item.updated_at,
        },
      });
    }
    console.log(`✅ Migrated ${queue.length} queue items\n`);

    // Migrate indexing results
    console.log("Migrating indexing results...");
    const [results] = await laravelDb.execute("SELECT * FROM indexing_results");

    for (const result of results as any[]) {
      await prisma.indexingResult.create({
        data: {
          url: result.url,
          status: result.status,
          metadata: result.metadata,
          createdAt: result.created_at,
        },
      });
    }
    console.log(`✅ Migrated ${results.length} indexing results\n`);

    // Migrate service workers
    console.log("Migrating service workers...");
    const [workers] = await laravelDb.execute("SELECT * FROM service_workers");

    for (const worker of workers as any[]) {
      await prisma.serviceWorker.create({
        data: {
          name: worker.name,
          email: worker.email,
          credentials: worker.credentials,
          dailyQuota: worker.daily_quota || 200,
          usedQuota: worker.used_quota || 0,
          lastUsed: worker.last_used,
          quotaResetAt: worker.quota_reset_at,
          isActive: worker.is_active !== false,
          createdAt: worker.created_at,
          updatedAt: worker.updated_at,
        },
      });
    }
    console.log(`✅ Migrated ${workers.length} service workers\n`);

    await laravelDb.end();
    console.log("\n🎉 Migration completed successfully!");
    console.log("\n📊 Migration Summary:");
    console.log(`- Roles: ${roles.length}`);
    console.log(`- Users: ${users.length}`);
    console.log(`- Sitemaps: ${sitemaps.length}`);
    console.log(`- Sitemap URLs: ${sitemapUrls.length}`);
    console.log(`- URL List: ${urlList.length}`);
    console.log(`- Queue Items: ${queue.length}`);
    console.log(`- Indexing Results: ${results.length}`);
    console.log(`- Service Workers: ${workers.length}`);
  } catch (error) {
    console.error("\n❌ Migration failed:", error);
    throw error;
  } finally {
    await prisma.$disconnect();
  }
}

migrate()
  .then(() => {
    console.log("\n✅ Disconnected from database");
    process.exit(0);
  })
  .catch((error) => {
    console.error("\n❌ Fatal error:", error);
    process.exit(1);
  });
```

---

## Type Definitions

### Global Types

**File**: `src/types/index.ts`

```typescript
import { User, Sitemap, UrlList, IndexQueue, ServiceWorker } from "@prisma/client";

// ============================================================================
// USER TYPES
// ============================================================================

export type UserWithRole = User & {
  role: {
    name: string;
  };
};

export type SafeUser = Omit<User, "password" | "googleAccessToken" | "googleRefreshToken">;

export type UserStats = {
  totalSitemaps: number;
  totalUrls: number;
  indexedUrls: number;
  pendingQueue: number;
};

// ============================================================================
// SITEMAP TYPES
// ============================================================================

export type SitemapWithCount = Sitemap & {
  _count: {
    urls: number;
  };
};

export type SitemapStats = {
  totalUrls: number;
  indexedUrls: number;
  notIndexedUrls: number;
  indexingRate: number;
};

export type SitemapImportResult = {
  success: boolean;
  sitemap?: Sitemap;
  error?: string;
};

// ============================================================================
// URL TYPES
// ============================================================================

export type UrlFreshness = {
  color: "green" | "yellow" | "red";
  status: "fresh" | "stale" | "very_stale" | "never_seen";
};

export type UrlStats = {
  total: number;
  indexed: number;
  notIndexed: number;
  indexingRate: number;
  freshness: {
    fresh: number;
    stale: number;
    veryStale: number;
  };
};

export type BulkImportResult = {
  success: string[];
  failed: Array<{ url: string; error: string }>;
  duplicate: string[];
};

// ============================================================================
// QUEUE TYPES
// ============================================================================

export type QueueStats = {
  pending: number;
  processing: number;
  completed: number;
  failed: number;
  total: number;
};

export type QueueItemWithSitemap = IndexQueue & {
  sitemap?: Sitemap;
};

// ============================================================================
// GOOGLE API TYPES
// ============================================================================

export type GoogleSitemapInfo = {
  url: string;
  siteUrl: string;
  lastSubmitted: Date | null;
  lastDownloaded: Date | null;
  errors: number;
  warnings: number;
  isPending: boolean;
};

export type GoogleInspectionResult = {
  verdict?: string;
  coverageState?: string;
  robotsTxtState?: string;
  indexingState?: string;
  pageFetchState?: string;
  crawledAs?: string;
  googleCanonical?: string;
  userCanonical?: string;
};

export type GoogleIndexingResponse = {
  urlNotificationMetadata?: {
    url: string;
    latestUpdate?: {
      type: string;
      notifyTime: string;
    };
  };
};

// ============================================================================
// WORKER TYPES
// ============================================================================

export type WorkerWithStats = ServiceWorker & {
  usagePercentage: number;
  remainingQuota: number;
};

export type WorkerStats = {
  total: number;
  active: number;
  totalQuota: number;
  usedQuota: number;
  remainingQuota: number;
  usagePercentage: number;
};

// ============================================================================
// ANALYTICS TYPES
// ============================================================================

export type DashboardStats = {
  sitemaps: number;
  urls: {
    total: number;
    indexed: number;
    notIndexed: number;
    indexingRate: number;
  };
  queue: {
    pending: number;
  };
  recentActivity: {
    recentlyIndexed: number;
  };
};

export type AdminAnalytics = {
  users: {
    total: number;
    active: number;
    inactive: number;
  };
  sitemaps: number;
  urls: {
    total: number;
    indexed: number;
    notIndexed: number;
    indexingRate: number;
  };
  queue: QueueStats;
  workers: WorkerStats;
};

export type IndexingTrend = {
  date: Date;
  count: number;
};

// ============================================================================
// API RESPONSE TYPES
// ============================================================================

export type ApiResponse<T = any> = {
  data?: T;
  error?: string;
  message?: string;
};

export type PaginatedResponse<T> = {
  data: T[];
  pagination: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
  };
};

export type ApiError = {
  error: string;
  details?: any;
  statusCode: number;
};

// ============================================================================
// FORM TYPES
// ============================================================================

export type LoginFormData = {
  email: string;
  password: string;
};

export type RegisterFormData = {
  name: string;
  email: string;
  password: string;
  confirmPassword: string;
};

export type SitemapFormData = {
  url: string;
  gscSiteUrl?: string;
  autoScan?: boolean;
};

export type UrlFormData = {
  url: string;
};

export type ProfileUpdateData = {
  name?: string;
  image?: string;
};

export type PasswordChangeData = {
  currentPassword: string;
  newPassword: string;
  confirmPassword: string;
};

// ============================================================================
// NEXT-AUTH TYPES
// ============================================================================

declare module "next-auth" {
  interface Session {
    user: {
      id: string;
      email: string;
      name?: string;
      image?: string;
      role: string;
    };
  }

  interface User {
    id: string;
    email: string;
    name?: string;
    image?: string;
    role: string;
  }
}

declare module "next-auth/jwt" {
  interface JWT {
    id: string;
    role: string;
  }
}
```

---

## Utility Functions

### Common Utilities

**File**: `src/lib/utils.ts`

```typescript
import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";
import { formatDistanceToNow, format } from "date-fns";

/**
 * Merge Tailwind CSS classes
 */
export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

/**
 * Format date to readable string
 */
export function formatDate(date: Date | string | null, formatStr: string = "PPP"): string {
  if (!date) return "Never";

  const dateObj = typeof date === "string" ? new Date(date) : date;

  if (isNaN(dateObj.getTime())) return "Invalid date";

  return format(dateObj, formatStr);
}

/**
 * Format date to relative time (e.g., "2 hours ago")
 */
export function formatRelativeTime(date: Date | string | null): string {
  if (!date) return "Never";

  const dateObj = typeof date === "string" ? new Date(date) : date;

  if (isNaN(dateObj.getTime())) return "Invalid date";

  return formatDistanceToNow(dateObj, { addSuffix: true });
}

/**
 * Truncate string with ellipsis
 */
export function truncate(str: string, length: number = 50): string {
  if (str.length <= length) return str;
  return str.slice(0, length) + "...";
}

/**
 * Truncate URL for display
 */
export function truncateUrl(url: string, maxLength: number = 50): string {
  try {
    const urlObj = new URL(url);
    const domain = urlObj.hostname;
    const path = urlObj.pathname + urlObj.search;

    if (url.length <= maxLength) return url;

    const truncatedPath = truncate(path, maxLength - domain.length - 10);
    return `${domain}${truncatedPath}`;
  } catch {
    return truncate(url, maxLength);
  }
}

/**
 * Format number with commas
 */
export function formatNumber(num: number): string {
  return new Intl.NumberFormat("en-US").format(num);
}

/**
 * Format percentage
 */
export function formatPercentage(value: number, decimals: number = 1): string {
  return `${value.toFixed(decimals)}%`;
}

/**
 * Get URL freshness color
 */
export function getUrlFreshnessColor(lastSeen: Date | null): {
  color: "green" | "yellow" | "red";
  label: string;
} {
  if (!lastSeen) {
    return { color: "red", label: "Never" };
  }

  const hoursSinceLastSeen = (Date.now() - new Date(lastSeen).getTime()) / (1000 * 60 * 60);

  if (hoursSinceLastSeen < 24) {
    return { color: "green", label: "Fresh" };
  } else if (hoursSinceLastSeen < 48) {
    return { color: "yellow", label: "Stale" };
  } else {
    return { color: "red", label: "Very Stale" };
  }
}

/**
 * Sleep/delay function
 */
export function sleep(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * Debounce function
 */
export function debounce<T extends (...args: any[]) => any>(
  func: T,
  wait: number
): (...args: Parameters<T>) => void {
  let timeout: NodeJS.Timeout | null = null;

  return function executedFunction(...args: Parameters<T>) {
    const later = () => {
      timeout = null;
      func(...args);
    };

    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

/**
 * Throttle function
 */
export function throttle<T extends (...args: any[]) => any>(
  func: T,
  limit: number
): (...args: Parameters<T>) => void {
  let inThrottle: boolean;

  return function executedFunction(...args: Parameters<T>) {
    if (!inThrottle) {
      func(...args);
      inThrottle = true;
      setTimeout(() => (inThrottle = false), limit);
    }
  };
}

/**
 * Generate random string
 */
export function generateRandomString(length: number = 32): string {
  const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  let result = "";
  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return result;
}

/**
 * Check if string is valid URL
 */
export function isValidUrl(str: string): boolean {
  try {
    new URL(str);
    return true;
  } catch {
    return false;
  }
}

/**
 * Extract domain from URL
 */
export function extractDomain(url: string): string {
  try {
    const urlObj = new URL(url);
    return urlObj.hostname;
  } catch {
    return url;
  }
}

/**
 * Capitalize first letter
 */
export function capitalize(str: string): string {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Convert camelCase to Title Case
 */
export function camelToTitle(str: string): string {
  const result = str.replace(/([A-Z])/g, " $1");
  return result.charAt(0).toUpperCase() + result.slice(1);
}

/**
 * Parse JSON safely
 */
export function parseJSON<T>(str: string, fallback: T): T {
  try {
    return JSON.parse(str) as T;
  } catch {
    return fallback;
  }
}

/**
 * Check if running in browser
 */
export const isBrowser = typeof window !== "undefined";

/**
 * Check if running in development
 */
export const isDevelopment = process.env.NODE_ENV === "development";

/**
 * Check if running in production
 */
export const isProduction = process.env.NODE_ENV === "production";

/**
 * Get error message from unknown error
 */
export function getErrorMessage(error: unknown): string {
  if (error instanceof Error) return error.message;
  if (typeof error === "string") return error;
  return "An unknown error occurred";
}

/**
 * Copy text to clipboard
 */
export async function copyToClipboard(text: string): Promise<boolean> {
  if (!isBrowser) return false;

  try {
    await navigator.clipboard.writeText(text);
    return true;
  } catch {
    return false;
  }
}

/**
 * Download data as file
 */
export function downloadAsFile(data: string, filename: string, type: string = "text/plain") {
  if (!isBrowser) return;

  const blob = new Blob([data], { type });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

/**
 * Group array by key
 */
export function groupBy<T>(array: T[], key: keyof T): Record<string, T[]> {
  return array.reduce((result, item) => {
    const group = String(item[key]);
    if (!result[group]) {
      result[group] = [];
    }
    result[group].push(item);
    return result;
  }, {} as Record<string, T[]>);
}

/**
 * Remove duplicates from array
 */
export function unique<T>(array: T[]): T[] {
  return Array.from(new Set(array));
}

/**
 * Chunk array into smaller arrays
 */
export function chunk<T>(array: T[], size: number): T[][] {
  const chunks: T[][] = [];
  for (let i = 0; i < array.length; i += size) {
    chunks.push(array.slice(i, i + size));
  }
  return chunks;
}
```

This comprehensive document provides all the database scripts, type definitions, and utility functions needed for the Next.js rewrite!
