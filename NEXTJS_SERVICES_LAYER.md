# Complete Service Layer Implementations

This document contains all service layer implementations for business logic.

---

## Table of Contents
1. [Sitemap Service](#sitemap-service)
2. [URL Service](#url-service)
3. [Queue Service](#queue-service)
4. [User Service](#user-service)
5. [Google Service](#google-service)
6. [Worker Service](#worker-service)
7. [Email Service](#email-service)
8. [Analytics Service](#analytics-service)

---

## Sitemap Service

**File**: `src/services/sitemap.service.ts`

```typescript
import { prisma } from "@/lib/db";
import { sitemapScanQueue } from "@/lib/queue/client";
import axios from "axios";
import { XMLParser } from "fast-xml-parser";

export class SitemapService {
  /**
   * Fetch and parse sitemap XML
   */
  static async fetchSitemap(url: string) {
    try {
      const response = await axios.get(url, {
        timeout: 30000,
        headers: {
          "User-Agent": process.env.SCRAPER_USER_AGENT || "SERPSurfer/1.0",
        },
      });

      const parser = new XMLParser({
        ignoreAttributes: false,
        attributeNamePrefix: "@_",
      });

      const parsed = parser.parse(response.data);

      return {
        success: true,
        data: parsed,
      };
    } catch (error) {
      console.error("Sitemap fetch error:", error);
      throw new Error(
        `Failed to fetch sitemap: ${
          error instanceof Error ? error.message : "Unknown error"
        }`
      );
    }
  }

  /**
   * Parse sitemap and extract URLs
   */
  static parseSitemapUrls(sitemapData: any) {
    const urls: Array<{
      url: string;
      lastmod?: Date;
      changefreq?: string;
      priority?: number;
    }> = [];

    // Handle sitemap index
    if (sitemapData.sitemapindex) {
      const sitemaps = Array.isArray(sitemapData.sitemapindex.sitemap)
        ? sitemapData.sitemapindex.sitemap
        : [sitemapData.sitemapindex.sitemap];

      return {
        isSitemapIndex: true,
        sitemaps: sitemaps.map((sm: any) => ({
          url: sm.loc,
          lastmod: sm.lastmod ? new Date(sm.lastmod) : undefined,
        })),
      };
    }

    // Handle regular sitemap
    if (sitemapData.urlset) {
      const urlEntries = Array.isArray(sitemapData.urlset.url)
        ? sitemapData.urlset.url
        : [sitemapData.urlset.url];

      for (const entry of urlEntries) {
        urls.push({
          url: entry.loc,
          lastmod: entry.lastmod ? new Date(entry.lastmod) : undefined,
          changefreq: entry.changefreq,
          priority: entry.priority ? parseFloat(entry.priority) : undefined,
        });
      }

      return {
        isSitemapIndex: false,
        urls,
      };
    }

    throw new Error("Invalid sitemap format");
  }

  /**
   * Import sitemap and queue for processing
   */
  static async importSitemap(
    userId: string,
    url: string,
    gscSiteUrl?: string
  ) {
    // Check if sitemap exists
    const existing = await prisma.sitemap.findFirst({
      where: { userId, url },
    });

    if (existing) {
      throw new Error("Sitemap already exists");
    }

    // Fetch and parse to validate
    const sitemapData = await this.fetchSitemap(url);
    const parsed = this.parseSitemapUrls(sitemapData.data);

    // Create sitemap record
    const sitemap = await prisma.sitemap.create({
      data: {
        userId,
        url,
        gscSiteUrl,
        isSitemapsIndex: parsed.isSitemapIndex,
        lastDownloaded: new Date(),
      },
    });

    // Queue for processing
    await sitemapScanQueue.add("scan-sitemap", {
      sitemapId: sitemap.id,
      userId,
      url,
    });

    return sitemap;
  }

  /**
   * Process sitemap and save URLs
   */
  static async processSitemap(sitemapId: string, userId: string) {
    const sitemap = await prisma.sitemap.findUnique({
      where: { id: sitemapId },
    });

    if (!sitemap) {
      throw new Error("Sitemap not found");
    }

    const sitemapData = await this.fetchSitemap(sitemap.url);
    const parsed = this.parseSitemapUrls(sitemapData.data);

    if (parsed.isSitemapIndex) {
      // Handle sitemap index - process child sitemaps
      for (const childSitemap of parsed.sitemaps || []) {
        await this.importSitemap(userId, childSitemap.url, sitemap.gscSiteUrl);
      }
    } else {
      // Handle regular sitemap - save URLs
      const urls = parsed.urls || [];

      for (const urlData of urls) {
        // Check if URL exists
        const existing = await prisma.sitemapUrl.findFirst({
          where: {
            sitemapId,
            url: urlData.url,
          },
        });

        if (!existing) {
          // Create sitemap URL
          await prisma.sitemapUrl.create({
            data: {
              sitemapId,
              url: urlData.url,
              lastmod: urlData.lastmod,
              changefreq: urlData.changefreq,
              priority: urlData.priority,
            },
          });

          // Add to tracking
          await prisma.urlList.upsert({
            where: {
              userId_url: {
                userId,
                url: urlData.url,
              },
            },
            create: {
              userId,
              url: urlData.url,
            },
            update: {},
          });

          // Queue for status check
          await prisma.indexQueue.create({
            data: {
              userId,
              url: urlData.url,
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
          isPending: false,
        },
      });
    }

    return {
      success: true,
      urlCount: parsed.urls?.length || 0,
    };
  }

  /**
   * Get sitemap statistics
   */
  static async getSitemapStats(sitemapId: string) {
    const [totalUrls, indexedUrls, notIndexedUrls] = await Promise.all([
      prisma.sitemapUrl.count({
        where: { sitemapId },
      }),
      prisma.sitemapUrl.count({
        where: { sitemapId, indexed: true },
      }),
      prisma.sitemapUrl.count({
        where: { sitemapId, indexed: false },
      }),
    ]);

    return {
      totalUrls,
      indexedUrls,
      notIndexedUrls,
      indexingRate: totalUrls > 0 ? (indexedUrls / totalUrls) * 100 : 0,
    };
  }

  /**
   * Enable/disable auto-scan
   */
  static async toggleAutoScan(sitemapId: string, autoScan: boolean) {
    return await prisma.sitemap.update({
      where: { id: sitemapId },
      data: { autoScan },
    });
  }

  /**
   * Delete sitemap and associated data
   */
  static async deleteSitemap(sitemapId: string) {
    // Delete sitemap URLs first (cascade should handle this, but being explicit)
    await prisma.sitemapUrl.deleteMany({
      where: { sitemapId },
    });

    // Delete sitemap
    await prisma.sitemap.delete({
      where: { id: sitemapId },
    });

    return { success: true };
  }
}
```

---

## URL Service

**File**: `src/services/url.service.ts`

```typescript
import { prisma } from "@/lib/db";
import { urlProcessingQueue } from "@/lib/queue/client";
import axios from "axios";
import * as cheerio from "cheerio";

export class UrlService {
  /**
   * Check if URL is indexed on Google
   */
  static async checkIndexingStatus(url: string) {
    try {
      const searchUrl = `https://www.google.com/search?q=${encodeURIComponent(
        url
      )}`;

      const response = await axios.get(searchUrl, {
        headers: {
          "User-Agent":
            process.env.SCRAPER_USER_AGENT ||
            "Mozilla/5.0 (compatible; SERPSurfer/1.0)",
        },
        timeout: 10000,
      });

      const $ = cheerio.load(response.data);

      // Check if URL appears in search results
      const foundLinks = $(`a[href*="${url}"]`);
      const isIndexed = foundLinks.length > 0;

      return {
        indexed: isIndexed,
        checkedAt: new Date(),
      };
    } catch (error) {
      console.error("URL indexing check error:", error);
      throw error;
    }
  }

  /**
   * Add URL to tracking
   */
  static async trackUrl(userId: string, url: string) {
    // Check if already tracked
    const existing = await prisma.urlList.findFirst({
      where: { userId, url },
    });

    if (existing) {
      return existing;
    }

    // Create URL record
    const urlRecord = await prisma.urlList.create({
      data: {
        userId,
        url,
      },
    });

    // Queue for status check
    await prisma.indexQueue.create({
      data: {
        userId,
        url,
        type: "URL",
      },
    });

    // Add to processing queue
    await urlProcessingQueue.add("process-url", {
      queueId: urlRecord.id,
      url,
      userId,
    });

    return urlRecord;
  }

  /**
   * Update URL indexing status
   */
  static async updateUrlStatus(
    urlId: string,
    data: {
      indexed?: boolean;
      verdict?: string;
      coverageState?: string;
      indexingState?: string;
      [key: string]: any;
    }
  ) {
    return await prisma.urlList.update({
      where: { id: urlId },
      data: {
        ...data,
        lastSeen: new Date(),
      },
    });
  }

  /**
   * Get URL freshness color
   */
  static getUrlFreshness(lastSeen: Date | null) {
    if (!lastSeen) {
      return { color: "red", status: "never_seen" };
    }

    const hoursSinceLastSeen =
      (Date.now() - lastSeen.getTime()) / (1000 * 60 * 60);

    if (hoursSinceLastSeen < 24) {
      return { color: "green", status: "fresh" };
    } else if (hoursSinceLastSeen < 48) {
      return { color: "yellow", status: "stale" };
    } else {
      return { color: "red", status: "very_stale" };
    }
  }

  /**
   * Get URL statistics for a user
   */
  static async getUserUrlStats(userId: string) {
    const [total, indexed, notIndexed, fresh, stale, veryStale] =
      await Promise.all([
        prisma.urlList.count({ where: { userId } }),
        prisma.urlList.count({ where: { userId, indexed: true } }),
        prisma.urlList.count({ where: { userId, indexed: false } }),
        prisma.urlList.count({
          where: {
            userId,
            lastSeen: {
              gte: new Date(Date.now() - 24 * 60 * 60 * 1000),
            },
          },
        }),
        prisma.urlList.count({
          where: {
            userId,
            lastSeen: {
              gte: new Date(Date.now() - 48 * 60 * 60 * 1000),
              lt: new Date(Date.now() - 24 * 60 * 60 * 1000),
            },
          },
        }),
        prisma.urlList.count({
          where: {
            userId,
            OR: [
              {
                lastSeen: {
                  lt: new Date(Date.now() - 48 * 60 * 60 * 1000),
                },
              },
              { lastSeen: null },
            ],
          },
        }),
      ]);

    return {
      total,
      indexed,
      notIndexed,
      indexingRate: total > 0 ? Math.round((indexed / total) * 100) : 0,
      freshness: {
        fresh,
        stale,
        veryStale,
      },
    };
  }

  /**
   * Bulk import URLs
   */
  static async bulkImportUrls(userId: string, urls: string[]) {
    const results = {
      success: [] as string[],
      failed: [] as { url: string; error: string }[],
      duplicate: [] as string[],
    };

    for (const url of urls) {
      try {
        // Validate URL
        new URL(url);

        // Check if exists
        const existing = await prisma.urlList.findFirst({
          where: { userId, url },
        });

        if (existing) {
          results.duplicate.push(url);
          continue;
        }

        // Create URL
        await this.trackUrl(userId, url);
        results.success.push(url);
      } catch (error) {
        results.failed.push({
          url,
          error: error instanceof Error ? error.message : "Unknown error",
        });
      }
    }

    return results;
  }

  /**
   * Remove stale URLs
   */
  static async removeStaleUrls(hoursThreshold: number = 72) {
    const cutoffDate = new Date(Date.now() - hoursThreshold * 60 * 60 * 1000);

    const deleted = await prisma.urlList.deleteMany({
      where: {
        lastSeen: {
          lt: cutoffDate,
        },
      },
    });

    return {
      deleted: deleted.count,
      cutoffDate,
    };
  }
}
```

---

## Queue Service

**File**: `src/services/queue.service.ts`

```typescript
import { prisma } from "@/lib/db";
import {
  urlProcessingQueue,
  sitemapScanQueue,
  indexingSubmissionQueue,
} from "@/lib/queue/client";
import { QueueType } from "@prisma/client";

export class QueueService {
  /**
   * Add item to queue
   */
  static async addToQueue(
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

    // Add to appropriate BullMQ queue
    switch (type) {
      case "URL":
        await urlProcessingQueue.add("process-url", {
          queueId: queueItem.id,
          url,
          userId,
        });
        break;

      case "SITEMAP":
        await sitemapScanQueue.add("scan-sitemap", {
          sitemapId: sitemapId!,
          userId,
          url,
        });
        break;

      case "INDEX_SUBMISSION":
        await indexingSubmissionQueue.add("submit-indexing", {
          url,
          userId,
        });
        break;
    }

    return queueItem;
  }

  /**
   * Retry failed queue item
   */
  static async retryQueueItem(queueId: string) {
    const item = await prisma.indexQueue.findUnique({
      where: { id: queueId },
    });

    if (!item) {
      throw new Error("Queue item not found");
    }

    if (item.status === "PROCESSING") {
      throw new Error("Cannot retry item that is currently processing");
    }

    // Reset queue item
    await prisma.indexQueue.update({
      where: { id: queueId },
      data: {
        status: "PENDING",
        attempts: 0,
        error: null,
        lastAttempt: null,
      },
    });

    // Re-add to queue
    await this.addToQueue(item.userId, item.url, item.type, item.sitemapId || undefined);

    return { success: true };
  }

  /**
   * Get queue statistics
   */
  static async getQueueStats(userId?: string) {
    const where = userId ? { userId } : {};

    const [pending, processing, completed, failed] = await Promise.all([
      prisma.indexQueue.count({
        where: { ...where, status: "PENDING" },
      }),
      prisma.indexQueue.count({
        where: { ...where, status: "PROCESSING" },
      }),
      prisma.indexQueue.count({
        where: { ...where, status: "COMPLETED" },
      }),
      prisma.indexQueue.count({
        where: { ...where, status: "FAILED" },
      }),
    ]);

    return {
      pending,
      processing,
      completed,
      failed,
      total: pending + processing + completed + failed,
    };
  }

  /**
   * Clear completed queue items
   */
  static async clearCompleted(userId?: string, daysOld: number = 7) {
    const cutoffDate = new Date(Date.now() - daysOld * 24 * 60 * 60 * 1000);

    const where: any = {
      status: "COMPLETED",
      processedAt: {
        lt: cutoffDate,
      },
    };

    if (userId) {
      where.userId = userId;
    }

    const deleted = await prisma.indexQueue.deleteMany({ where });

    return {
      deleted: deleted.count,
      cutoffDate,
    };
  }

  /**
   * Get queue processing rate
   */
  static async getProcessingRate(hours: number = 24) {
    const startTime = new Date(Date.now() - hours * 60 * 60 * 1000);

    const processed = await prisma.indexQueue.count({
      where: {
        status: "COMPLETED",
        processedAt: {
          gte: startTime,
        },
      },
    });

    return {
      processed,
      hours,
      rate: processed / hours,
    };
  }
}
```

---

## Google Service

**File**: `src/services/google.service.ts`

```typescript
import { google } from "googleapis";
import { getGoogleClient } from "@/lib/google/auth";
import { prisma } from "@/lib/db";

export class GoogleService {
  /**
   * Fetch sitemaps from Google Search Console
   */
  static async fetchUserSitemaps(userId: string) {
    const auth = await getGoogleClient(userId);
    const webmasters = google.webmasters({ version: "v3", auth });

    // Get all sites
    const sitesResponse = await webmasters.sites.list();
    const sites = sitesResponse.data.siteEntry || [];

    const allSitemaps = [];

    for (const site of sites) {
      try {
        const sitemapsResponse = await webmasters.sitemaps.list({
          siteUrl: site.siteUrl!,
        });

        const sitemaps = sitemapsResponse.data.sitemap || [];

        for (const sitemap of sitemaps) {
          allSitemaps.push({
            url: sitemap.path!,
            siteUrl: site.siteUrl!,
            lastSubmitted: sitemap.lastSubmitted
              ? new Date(sitemap.lastSubmitted)
              : null,
            lastDownloaded: sitemap.lastDownloaded
              ? new Date(sitemap.lastDownloaded)
              : null,
            errors: sitemap.errors ? Number(sitemap.errors) : 0,
            warnings: sitemap.warnings ? Number(sitemap.warnings) : 0,
            isPending: sitemap.isPending || false,
          });
        }
      } catch (error) {
        console.error(`Error fetching sitemaps for ${site.siteUrl}:`, error);
      }
    }

    return allSitemaps;
  }

  /**
   * Inspect URL using Search Console API
   */
  static async inspectUrl(userId: string, inspectionUrl: string, siteUrl: string) {
    const auth = await getGoogleClient(userId);
    const searchconsole = google.searchconsole({ version: "v1", auth });

    const response = await searchconsole.urlInspection.index.inspect({
      requestBody: {
        inspectionUrl,
        siteUrl,
      },
    });

    return response.data.inspectionResult;
  }

  /**
   * Submit URL to Google Indexing API
   */
  static async submitUrlForIndexing(
    serviceAccountCredentials: string,
    url: string
  ) {
    const credentials = JSON.parse(serviceAccountCredentials);

    const auth = new google.auth.GoogleAuth({
      credentials,
      scopes: ["https://www.googleapis.com/auth/indexing"],
    });

    const authClient = await auth.getClient();
    const indexing = google.indexing({ version: "v3", auth: authClient });

    const response = await indexing.urlNotifications.publish({
      requestBody: {
        url,
        type: "URL_UPDATED",
      },
    });

    return response.data;
  }

  /**
   * Get indexing status metadata
   */
  static async getIndexingMetadata(
    serviceAccountCredentials: string,
    url: string
  ) {
    const credentials = JSON.parse(serviceAccountCredentials);

    const auth = new google.auth.GoogleAuth({
      credentials,
      scopes: ["https://www.googleapis.com/auth/indexing"],
    });

    const authClient = await auth.getClient();
    const indexing = google.indexing({ version: "v3", auth: authClient });

    const response = await indexing.urlNotifications.getMetadata({
      url,
    });

    return response.data;
  }

  /**
   * Batch import sitemaps from GSC
   */
  static async importSitemapsFromGSC(userId: string) {
    const gscSitemaps = await this.fetchUserSitemaps(userId);

    const imported = [];
    const skipped = [];

    for (const gscSitemap of gscSitemaps) {
      // Check if already exists
      const existing = await prisma.sitemap.findFirst({
        where: {
          userId,
          url: gscSitemap.url,
        },
      });

      if (existing) {
        skipped.push(gscSitemap.url);
        continue;
      }

      // Create sitemap
      const sitemap = await prisma.sitemap.create({
        data: {
          userId,
          url: gscSitemap.url,
          gscSiteUrl: gscSitemap.siteUrl,
          lastSubmitted: gscSitemap.lastSubmitted,
          lastDownloaded: gscSitemap.lastDownloaded,
          errors: gscSitemap.errors,
          warnings: gscSitemap.warnings,
          isPending: gscSitemap.isPending,
        },
      });

      imported.push(sitemap);
    }

    return {
      imported: imported.length,
      skipped: skipped.length,
      sitemaps: imported,
    };
  }
}
```

---

## Worker Service

**File**: `src/services/worker.service.ts`

```typescript
import { prisma } from "@/lib/db";

export class WorkerService {
  /**
   * Get available service worker
   */
  static async getAvailableWorker() {
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

  /**
   * Increment worker usage
   */
  static async incrementWorkerUsage(workerId: string) {
    return await prisma.serviceWorker.update({
      where: { id: workerId },
      data: {
        usedQuota: {
          increment: 1,
        },
        lastUsed: new Date(),
      },
    });
  }

  /**
   * Reset all worker quotas
   */
  static async resetAllQuotas() {
    return await prisma.serviceWorker.updateMany({
      data: {
        usedQuota: 0,
        quotaResetAt: new Date(),
      },
    });
  }

  /**
   * Create new service worker
   */
  static async createWorker(data: {
    name: string;
    email: string;
    credentials: string;
    dailyQuota?: number;
  }) {
    return await prisma.serviceWorker.create({
      data: {
        name: data.name,
        email: data.email,
        credentials: data.credentials, // Should be encrypted
        dailyQuota: data.dailyQuota || 200,
      },
    });
  }

  /**
   * Get worker statistics
   */
  static async getWorkerStats(workerId: string) {
    const worker = await prisma.serviceWorker.findUnique({
      where: { id: workerId },
    });

    if (!worker) {
      throw new Error("Worker not found");
    }

    const usagePercentage = (worker.usedQuota / worker.dailyQuota) * 100;
    const remainingQuota = worker.dailyQuota - worker.usedQuota;

    return {
      ...worker,
      usagePercentage,
      remainingQuota,
    };
  }

  /**
   * Get all workers with stats
   */
  static async getAllWorkersWithStats() {
    const workers = await prisma.serviceWorker.findMany({
      orderBy: { createdAt: "desc" },
    });

    return workers.map((worker) => ({
      ...worker,
      usagePercentage: (worker.usedQuota / worker.dailyQuota) * 100,
      remainingQuota: worker.dailyQuota - worker.usedQuota,
    }));
  }
}
```

---

## Email Service

**File**: `src/services/email.service.ts`

```typescript
import { prisma } from "@/lib/db";
import { sendEmail } from "@/lib/email/client";
import { EmailType } from "@prisma/client";

export class EmailService {
  /**
   * Send success notification email
   */
  static async sendSuccessNotification(
    userId: string,
    urls: string[]
  ) {
    const user = await prisma.user.findUnique({
      where: { id: userId },
    });

    if (!user?.email) {
      throw new Error("User email not found");
    }

    await sendEmail({
      to: user.email,
      subject: `${urls.length} URL${urls.length > 1 ? "s" : ""} Successfully Indexed!`,
      template: "success-notification",
      data: {
        urls,
        count: urls.length,
        userName: user.name || user.email,
        dashboardUrl: `${process.env.NEXT_PUBLIC_APP_URL}/dashboard`,
      },
    });
  }

  /**
   * Send bug report email
   */
  static async sendBugReport(
    userEmail: string,
    subject: string,
    message: string
  ) {
    await sendEmail({
      to: process.env.ADMIN_EMAIL || "admin@example.com",
      subject: `Bug Report: ${subject}`,
      template: "bug-report",
      data: {
        userEmail,
        subject,
        message,
        reportedAt: new Date().toLocaleString(),
      },
    });
  }

  /**
   * Create email template
   */
  static async createTemplate(data: {
    name: string;
    subject: string;
    htmlContent: string;
    jsonContent?: string;
    type: EmailType;
  }) {
    return await prisma.emailTemplate.create({
      data,
    });
  }

  /**
   * Update email template
   */
  static async updateTemplate(
    templateId: string,
    data: {
      name?: string;
      subject?: string;
      htmlContent?: string;
      jsonContent?: string;
    }
  ) {
    return await prisma.emailTemplate.update({
      where: { id: templateId },
      data,
    });
  }

  /**
   * Send bulk email (admin)
   */
  static async sendBulkEmail(
    userIds: string[],
    subject: string,
    htmlContent: string
  ) {
    const users = await prisma.user.findMany({
      where: {
        id: {
          in: userIds,
        },
      },
      select: {
        email: true,
      },
    });

    const emails = users.map((u) => u.email);

    // Send in batches to avoid rate limits
    const batchSize = 50;
    for (let i = 0; i < emails.length; i += batchSize) {
      const batch = emails.slice(i, i + batchSize);

      await sendEmail({
        to: batch,
        subject,
        template: "custom",
        data: {
          content: htmlContent,
        },
      });
    }

    return {
      sent: emails.length,
    };
  }
}
```

---

## Analytics Service

**File**: `src/services/analytics.service.ts`

```typescript
import { prisma } from "@/lib/db";

export class AnalyticsService {
  /**
   * Get user dashboard stats
   */
  static async getUserDashboardStats(userId: string) {
    const [
      totalSitemaps,
      totalUrls,
      indexedUrls,
      pendingQueue,
      recentlyIndexed,
    ] = await Promise.all([
      prisma.sitemap.count({
        where: { userId },
      }),
      prisma.urlList.count({
        where: { userId },
      }),
      prisma.urlList.count({
        where: { userId, indexed: true },
      }),
      prisma.indexQueue.count({
        where: { userId, status: "PENDING" },
      }),
      prisma.indexingResult.count({
        where: {
          createdAt: {
            gte: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000), // Last 7 days
          },
        },
      }),
    ]);

    return {
      sitemaps: totalSitemaps,
      urls: {
        total: totalUrls,
        indexed: indexedUrls,
        notIndexed: totalUrls - indexedUrls,
        indexingRate: totalUrls > 0 ? (indexedUrls / totalUrls) * 100 : 0,
      },
      queue: {
        pending: pendingQueue,
      },
      recentActivity: {
        recentlyIndexed,
      },
    };
  }

  /**
   * Get indexing trends
   */
  static async getIndexingTrends(userId: string, days: number = 30) {
    const startDate = new Date(Date.now() - days * 24 * 60 * 60 * 1000);

    const results = await prisma.indexingResult.groupBy({
      by: ["createdAt"],
      _count: true,
      where: {
        createdAt: {
          gte: startDate,
        },
      },
      orderBy: {
        createdAt: "asc",
      },
    });

    return results.map((result) => ({
      date: result.createdAt,
      count: result._count,
    }));
  }

  /**
   * Get admin analytics
   */
  static async getAdminAnalytics() {
    const [
      totalUsers,
      activeUsers,
      totalSitemaps,
      totalUrls,
      indexedUrls,
      queueStats,
      workerStats,
    ] = await Promise.all([
      prisma.user.count(),
      prisma.user.count({
        where: {
          sitemaps: {
            some: {},
          },
        },
      }),
      prisma.sitemap.count(),
      prisma.urlList.count(),
      prisma.urlList.count({ where: { indexed: true } }),
      this.getQueueAnalytics(),
      this.getWorkerAnalytics(),
    ]);

    return {
      users: {
        total: totalUsers,
        active: activeUsers,
        inactive: totalUsers - activeUsers,
      },
      sitemaps: totalSitemaps,
      urls: {
        total: totalUrls,
        indexed: indexedUrls,
        notIndexed: totalUrls - indexedUrls,
        indexingRate: totalUrls > 0 ? (indexedUrls / totalUrls) * 100 : 0,
      },
      queue: queueStats,
      workers: workerStats,
    };
  }

  /**
   * Get queue analytics
   */
  private static async getQueueAnalytics() {
    const [pending, processing, completed, failed] = await Promise.all([
      prisma.indexQueue.count({ where: { status: "PENDING" } }),
      prisma.indexQueue.count({ where: { status: "PROCESSING" } }),
      prisma.indexQueue.count({ where: { status: "COMPLETED" } }),
      prisma.indexQueue.count({ where: { status: "FAILED" } }),
    ]);

    return {
      pending,
      processing,
      completed,
      failed,
      total: pending + processing + completed + failed,
    };
  }

  /**
   * Get worker analytics
   */
  private static async getWorkerAnalytics() {
    const workers = await prisma.serviceWorker.findMany();

    const totalQuota = workers.reduce((sum, w) => sum + w.dailyQuota, 0);
    const usedQuota = workers.reduce((sum, w) => sum + w.usedQuota, 0);

    return {
      total: workers.length,
      active: workers.filter((w) => w.isActive).length,
      totalQuota,
      usedQuota,
      remainingQuota: totalQuota - usedQuota,
      usagePercentage: totalQuota > 0 ? (usedQuota / totalQuota) * 100 : 0,
    };
  }
}
```

This completes the comprehensive service layer implementation. All business logic is properly encapsulated in service classes that can be easily tested and maintained.
